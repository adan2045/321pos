<?php
namespace app\controllers;

use \Controller;
use \Response;
use \DataBase;
use app\controllers\SesionController;
use app\models\MesaModel;

class CajeroController extends Controller {
    public $nombre = '';
    public $apellido;

    public function __construct() {}

    public function actionIndex($var = null)
    {
        $footer = SiteController::footer();
        $head = SiteController::head();
        $nav = SiteController::nav();
        $path = static::path();

        Response::render($this->viewDir(__NAMESPACE__), "inicio", [
            "title" => $this->title . "Inicio",
            "head" => $head,
            "nav" => $nav,
            "footer" => $footer,
        ]);
    }

    public function actionVistaCajero()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $footer = SiteController::footer();
        $head = SiteController::head();
        $nav = SiteController::nav();
        $path = static::path();

        $mesaModel = new MesaModel();
        $mesas = $mesaModel->obtenerConTotales();

        $pedidoModel = new \app\models\PedidoModel();
        $pedidos = $pedidoModel->obtenerPedidosDelDiaConDetalle();

        Response::render($this->viewDir(__NAMESPACE__), "vistaCajero", [
            "title" => $this->title . "Mesas",
            'ruta' => self::$ruta,
            "head" => $head,
            "nav" => $nav,
            "footer" => $footer,
            "mesas" => $mesas,
            "cajero" => $_SESSION['user_email'] ?? 'Sin sesión',
            "pedidos" => $pedidos
        ]);
    }
    
    public function actionPagarMesa()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mesa_id'])) {
            $mesaId = intval($_POST['mesa_id']);
            $db = \DataBase::getInstance()->getConnection();

            try {
                $db->beginTransaction();

                $stmt = $db->prepare("UPDATE pedidos SET cerrado = 1 WHERE mesa_id = ? AND DATE(fecha) = CURDATE()");
                $stmt->execute([$mesaId]);

                $stmt2 = $db->prepare("UPDATE mesas SET estado = 'libre' WHERE id = ?");
                $stmt2->execute([$mesaId]);

                $db->commit();
                echo 'ok';
            } catch (\Exception $e) {
                $db->rollBack();
                http_response_code(500);
                echo 'error';
            }
        } else {
            http_response_code(400);
            echo 'error';
        }
    }
    public function actionCancelarTicket()
{
    header('Content-Type: application/json');

    $db = \DataBase::getInstance();
    $conn = $db->getConnection();

    try {

        $data = json_decode(file_get_contents("php://input"), true);

        $ticket = $data['ticket'];
        $password = $data['password'];

        // 🔐 validar contraseña (ejemplo simple)
        if ($password !== "1234") {
            throw new \Exception("Contraseña incorrecta");
        }

        $conn->beginTransaction();

        // Buscar pedido
        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$ticket]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$pedido) {
            throw new \Exception("Ticket no encontrado");
        }

        if ($pedido['estado'] === 'cancelado') {
            throw new \Exception("El ticket ya está cancelado");
        }

        // Marcar pedido cancelado
        $upd = $conn->prepare("UPDATE pedidos SET estado='cancelado' WHERE id=?");
        $upd->execute([$pedido['id']]);

        // Insertar movimiento negativo
        $stmtMov = $conn->prepare("
        INSERT INTO movimientos_caja
        (caja_id, tipo, referencia_id, numero_ticket, descripcion, metodo_pago, ingreso, egreso, fecha)
        VALUES (?, 'nota_credito', ?, ?, ?, ?, 0, ?, NOW())
    ");

$stmtMov->execute([
    $_SESSION['caja_id'] ?? null,
    $pedido['id'],
    $pedido['id'],
    "Nota crédito Ticket #" . $pedido['id'],
    $pedido['metodo_pago'],
    $pedido['total']
]);

        $conn->commit();

        echo json_encode(["status"=>"ok"]);

    } catch (\Exception $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        echo json_encode([
            "status"=>"error",
            "message"=>$e->getMessage()
        ]);
    }
}

    public function actionRegistrarGasto()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motivo = trim($_POST['motivo'] ?? '');
            $monto = floatval($_POST['monto'] ?? 0);
            $autorizado_por = trim($_POST['autorizado_por'] ?? '');
            $usuario_id = $_SESSION['user_id'] ?? null;

            // ✅ Mejor: caja_id desde POST o desde sesión
            $caja_id = $_POST['caja_id'] ?? ($_SESSION['caja_id'] ?? null);

            if ($motivo !== '' && $monto > 0 && $autorizado_por !== '' && !empty($caja_id)) {
                $db = \DataBase::getInstance()->getConnection();
                $stmt = $db->prepare("INSERT INTO gastos (fecha, monto, motivo, autorizado_por, usuario_id, caja_id) VALUES (NOW(), ?, ?, ?, ?, ?)");
                $stmt->execute([$monto, $motivo, $autorizado_por, $usuario_id, $caja_id]);

                header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
                exit;
            } else {
                echo "<script>alert('Faltan datos obligatorios para registrar el gasto'); window.history.back();</script>";
                exit;
            }
        }
    }

    public function actionCuentaMesa()
    {
        if (!isset($_GET['id'])) {
            echo "Mesa no especificada";
            return;
        }

        $mesaId = intval($_GET['id']);

        $pedidoModel = new \app\models\PedidoModel();
        $pedidos = $pedidoModel->obtenerPedidosActivosPorMesa($mesaId);

        $productos = [];
        $total = 0;

        foreach ($pedidos as $pedido) {
            foreach ($pedido['detalles'] as $detalle) {
                $subtotal = $detalle['precio'] * $detalle['cantidad'];
                $productos[] = [
                    'nombre' => $detalle['nombre'],
                    'descripcion' => $detalle['descripcion'],
                    'precio' => $detalle['precio'],
                    'cantidad' => $detalle['cantidad'],
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }

        $mesaModel = new \app\models\MesaModel();
        $mesa = $mesaModel->obtenerPorId($mesaId);

        $head = SiteController::head();
        $nav = SiteController::nav();
        $footer = SiteController::footer();

        Response::render('cajero', 'cuenta', [
            'productos' => $productos,
            'total' => $total,
            'mesa' => $mesa,
            'ruta' => \App::baseUrl(),
            'head' => $head,
            'nav' => $nav,
            'footer' => $footer
        ]);
    }

    public function actionCuenta()
    {
        $pedidoId = isset($_GET['id']) ? intval($_GET['id']) : null;
        $mesaNumero = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;

        $footer = SiteController::footer();
        $head = SiteController::head();
        $nav = SiteController::nav();
        $pedidoModel = new \app\models\PedidoModel();

        if ($pedidoId) {
            $datos = $pedidoModel->obtenerDetalleCuentaPorPedido($pedidoId);
            $esCuentaCerrada = true;
        } elseif ($mesaNumero) {
            $mesaModel = new \app\models\MesaModel();
            $mesa = $mesaModel->obtenerPorNumero($mesaNumero);
            $mesaId = $mesa['id'] ?? null;

            if ($mesaId) {
                $datos = $pedidoModel->obtenerDetalleCuentaPorMesa($mesaId);
            } else {
                $datos = ['mesa' => [], 'productos' => [], 'total' => 0];
            }
            $esCuentaCerrada = false;
        } else {
            $datos = ['mesa' => [], 'productos' => [], 'total' => 0];
            $esCuentaCerrada = true;
        }

        Response::render('cajero', 'cuenta', [
            'head' => $head,
            'title' => 'Cuenta',
            'nav' => $nav,
            'footer' => $footer,
            'mesa' => $datos['mesa'],
            'productos' => $datos['productos'],
            'total' => $datos['total'],
            'ruta' => \App::baseUrl(),
            'esCuentaCerrada' => $esCuentaCerrada,
        ]);
    }

    public function actionCerrarMesa()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mesaId = $_POST['mesa_id'] ?? null;

            if ($mesaId) {
                $mesaModel = new \app\models\MesaModel();
                $mesaModel->cerrarMesaYSolicitarCuenta($mesaId);
                echo 'ok';
            } else {
                http_response_code(400);
                echo 'Faltan datos';
            }
        }
    }

    public function actionMarcarPagado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mesa_id = $_POST['mesa_id'] ?? null;
            $medio_pago = $_POST['medio_pago'] ?? null;

            if ($mesa_id && $medio_pago) {
                $pedidoModel = new \app\models\PedidoModel();
                $pedidoModel->cerrarPedidosDeHoyPorMesa($mesa_id, $medio_pago);

                $mesaModel = new \app\models\MesaModel();
                $mesaModel->actualizarEstado($mesa_id, 'disponible');

                echo 'ok';
            } else {
                http_response_code(400);
                echo 'Faltan datos';
            }
        }
    }

    public function actionPlanillaCaja()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $footer = \app\controllers\SiteController::footer();
        $head = \app\controllers\SiteController::head();
        $nav = \app\controllers\SiteController::nav();
        $path = static::path();

        $cajaId = $_SESSION['caja_id'] ?? null;

        if ($cajaId) {
            $cajaModel = new \app\models\CajaModel();
            $datos = $cajaModel->obtenerTotalesDelDia();
            $productos = $cajaModel->resumenPorProducto();
            $gastos = $cajaModel->obtenerGastosDelDia();
            $datos['total_gastos'] = $gastos['total'];
            $datos['cantidad_gastos'] = $gastos['cantidad'];
        } else {
            $datos = [
                'venta_bruta' => 0, 'cantidad_pedidos' => 0,
                'efectivo_total' => 0, 'efectivo_cantidad' => 0,
                'qr' => 0, 'qr_cantidad' => 0,
                'mercadopago' => 0, 'mercadopago_cantidad' => 0,
                'tarjetas' => 0, 'tarjetas_cantidad' => 0,
                'inicio_caja' => 0, 'efectivo_ventas' => 0,
                'caja_fuerte' => 0, 'cantidad_caja_fuerte' => 0,
                'total_gastos' => 0, 'cantidad_gastos' => 0,
                'saldo' => 0
            ];
            $productos = [];
        }

        \Response::render($this->viewDir(__NAMESPACE__), "planillaCaja", [
            "title" => "Planilla de Caja",
            "head" => $head,
            "nav" => $nav,
            "footer" => $footer,
            "datos" => $datos,
            "productos" => $productos,
            "ruta" => $path,
        ]);
    }

    public function actionCambiarMedioPago()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pedidoId = $_POST['pedido_id'] ?? null;
            $medioPago = $_POST['medio_pago'] ?? null;

            if ($pedidoId && $medioPago) {
                $db = \DataBase::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE pedidos SET metodo_pago = ? WHERE id = ?");
                $ok = $stmt->execute([$medioPago, $pedidoId]);
                echo $ok ? 'ok' : 'error';
                return;
            }
        }
        http_response_code(400);
        echo 'error';
    }

    public function actionRegistrarCajaFuerte()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monto = floatval($_POST['monto'] ?? 0);
            $responsable = trim($_POST['responsable'] ?? '');

            // ✅ Mejor: caja_id desde POST o desde sesión
            $caja_id = $_POST['caja_id'] ?? ($_SESSION['caja_id'] ?? null);

            if ($monto > 0 && !empty($responsable) && !empty($caja_id)) {
                $db = \DataBase::getInstance()->getConnection();
                $stmt = $db->prepare("INSERT INTO caja_fuerte (fecha, monto, responsable, caja_id) VALUES (NOW(), ?, ?, ?)");
                $stmt->execute([$monto, $responsable, $caja_id]);

                header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
                exit;
            } else {
                $_SESSION['mensaje_error'] = "Faltan datos obligatorios para registrar el movimiento de Caja Fuerte.";
                header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
                exit;
            }
        }
    }

    public function actionAbrirCaja()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // ✅ Aceptar POST o GET (así tu modal puede ser POST)
        $monto = 0.0;
        if (isset($_POST['monto'])) $monto = floatval($_POST['monto']);
        elseif (isset($_GET['monto'])) $monto = floatval($_GET['monto']);

        if ($monto < 0 || $monto > 10000000) {
            $_SESSION['mensaje_error'] = "El monto debe ser entre 0 y 10.000.000.";
            header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
            exit;
        }

        $hoy = date('Y-m-d');
        $db = \DataBase::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM cajas WHERE DATE(fecha_apertura) = ? AND fecha_cierre IS NULL");
        $stmt->execute([$hoy]);
        $cajaAbierta = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($cajaAbierta) {
            $_SESSION['caja_abierta'] = true;
            $_SESSION['caja_id'] = $cajaAbierta['id'];
            $_SESSION['caja_apertura'] = $cajaAbierta['fecha_apertura'];
            $_SESSION['saldo_inicial'] = $cajaAbierta['saldo_inicial'];

            header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
            exit;
        }

        $stmt = $db->prepare("INSERT INTO cajas (fecha_apertura, saldo_inicial) VALUES (NOW(), ?)");
        $stmt->execute([$monto]);
        $cajaId = $db->lastInsertId();

        $_SESSION['caja_abierta'] = true;
        $_SESSION['caja_id'] = $cajaId;
        $_SESSION['caja_apertura'] = date('Y-m-d H:i:s');
        $_SESSION['saldo_inicial'] = $monto;

        header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
        exit;
    }

    public function actionCerrarCaja() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $cajaModel = new \app\models\CajaModel();
            $usuarioId = $_SESSION['user_id'] ?? null;
            $datos = $cajaModel->obtenerTotalesDelDia();
            $saldoCierre = $datos['saldo'];

            $exito = $cajaModel->cerrarCajaDelDia($usuarioId, null, $saldoCierre);

            if ($exito) {
                $_SESSION['mensaje_exito'] = "¡Caja cerrada con éxito!";
                unset($_SESSION['caja_abierta'], $_SESSION['caja_id'], $_SESSION['caja_apertura'], $_SESSION['saldo_inicial']);
            } else {
                $_SESSION['mensaje_error'] = "No se encontró una caja abierta para cerrar.";
            }
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al cerrar la caja: " . $e->getMessage();
        }

        header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
        exit;
    }

    public function actionLibroDiario()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Base path para construir URLs sin hardcodear "/321POS/public"
        $ruta = static::path();
        $db = \DataBase::getInstance()->getConnection();

        $cajaId = $_SESSION['caja_id'] ?? null;
        $fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d');

        if (!$cajaId) {
            $stmt = $db->prepare("SELECT id FROM cajas WHERE DATE(fecha_apertura) = ? ORDER BY fecha_apertura DESC LIMIT 1");
            $stmt->execute([$fechaSeleccionada]);
            $cajaId = $stmt->fetchColumn();
        }

        $inicioCaja = 0;
        $fechaApertura = '';
        if ($cajaId) {
            $stmt = $db->prepare("SELECT saldo_inicial, fecha_apertura FROM cajas WHERE id = ?");
            $stmt->execute([$cajaId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $inicioCaja = $row['saldo_inicial'] ?? 0;
            $fechaApertura = $row['fecha_apertura'] ?? '';
        }

        $stmt = $db->prepare("
        SELECT p.id, p.total, p.metodo_pago, p.mesa_id, p.fecha_cierre
        FROM pedidos p
        WHERE DATE(p.fecha_cierre) = ?
        ");
        $stmt->execute([$fechaSeleccionada]);
        $ventas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 🔴 Leer notas de crédito y otros movimientos manuales
        $stmt = $db->prepare("
         SELECT *
        FROM movimientos_caja
        WHERE DATE(fecha) = ?
        AND tipo = 'nota_credito'
    ");
        $stmt->execute([$fechaSeleccionada]);
        $notasCredito = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT id, monto, motivo, autorizado_por, fecha FROM gastos WHERE caja_id = ?");
        $stmt->execute([$cajaId]);
        $gastos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT id, monto, responsable, fecha FROM caja_fuerte WHERE caja_id = ?");
        $stmt->execute([$cajaId]);
        $cajaFuerte = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $cacheMesas = [];
        $getNumeroMesa = function($mesaId) use (&$cacheMesas, $db) {
            if (!$mesaId) return '';
            if (isset($cacheMesas[$mesaId])) return $cacheMesas[$mesaId];
            $stmtMesa = $db->prepare("SELECT numero FROM mesas WHERE id = ?");
            $stmtMesa->execute([$mesaId]);
            $num = $stmtMesa->fetchColumn();
            $cacheMesas[$mesaId] = $num ?: '';
            return $cacheMesas[$mesaId];
        };

        $movimientos = [];
        $movimientos[] = [
            'tipo' => 'inicio',
            'detalle' => "INICIO DE CAJA",
            'efectivo' => $inicioCaja,
            'tarjeta' => '',
            'qr' => '',
            'mp' => '',
            'total' => $inicioCaja,
            'mesa' => '',
            'fecha_hora' => $fechaApertura ? date('Y-m-d H:i', strtotime($fechaApertura)) : $fechaSeleccionada . ' 08:00',
            'clase_efectivo' => 'entrada',
            'clase_tarjeta' => '',
            'clase_qr' => '',
            'clase_mp' => '',
            'clase_total' => 'entrada',
            'ticket_url' => ''
        ];

        foreach ($ventas as $v) {

    $numeroTicket = str_pad($v['id'], 4, '0', STR_PAD_LEFT);
    $detalle = "TICKET #" . $numeroTicket;
    $metodo = strtolower($v['metodo_pago']);

    $movimientos[] = [
        'tipo' => 'venta',
        'detalle' => $detalle,
        'numero_ticket' => $v['id'],   
        'efectivo' => $metodo=='efectivo' ? $v['total'] : '',
        'tarjeta' => $metodo=='tarjeta' ? $v['total'] : '',
        'qr' => $metodo=='qr' ? $v['total'] : '',
        'mp' => $metodo=='mercadopago' ? $v['total'] : '',
        'total' => $v['total'],
        'mesa' => $getNumeroMesa($v['mesa_id']),
        'fecha_hora' => date('Y-m-d H:i', strtotime($v['fecha_cierre'])),
        'ticket_url' => '/321POS/public/cajero/cuenta?id=' . $v['id']
    ];
}
    foreach ($notasCredito as $nc) {

    $movimientos[] = [
        'tipo' => 'nota_credito',
        'detalle' => $nc['descripcion'],
        'numero_ticket' => $nc['numero_ticket'],
        'efectivo' => $nc['metodo_pago'] == 'efectivo' ? -$nc['egreso'] : '',
        'tarjeta' => $nc['metodo_pago'] == 'tarjeta' ? -$nc['egreso'] : '',
        'qr' => $nc['metodo_pago'] == 'qr' ? -$nc['egreso'] : '',
        'mp' => $nc['metodo_pago'] == 'mercadopago' ? -$nc['egreso'] : '',
        'total' => -$nc['egreso'],
        'mesa' => '',
        'fecha_hora' => date('Y-m-d H:i', strtotime($nc['fecha'])),
        'ticket_url' => ''
    ];
}

        foreach ($cajaFuerte as $cf) {
            $movimientos[] = [
                'tipo' => 'caja_fuerte',
                'detalle' => "Depósito Caja Fuerte (".$cf['responsable'].")",
                'efectivo' => $cf['monto'],
                'tarjeta' => '',
                'qr' => '',
                'mp' => '',
                'total' => $cf['monto'],
                'mesa' => '',
                'fecha_hora' => date('Y-m-d H:i', strtotime($cf['fecha'])),
                'clase_efectivo' => 'egreso',
                'clase_tarjeta' => '',
                'clase_qr' => '',
                'clase_mp' => '',
                'clase_total' => 'egreso',
                'ticket_url' => ''
            ];
        }

        foreach ($gastos as $g) {
            $movimientos[] = [
                'tipo' => 'gasto',
                'detalle' => "GASTO: ".$g['motivo'].($g['autorizado_por'] ? " (aut: ".$g['autorizado_por'].")" : ""),
                'efectivo' => -abs($g['monto']),
                'tarjeta' => '',
                'qr' => '',
                'mp' => '',
                'total' => -abs($g['monto']),
                'mesa' => '',
                'fecha_hora' => date('Y-m-d H:i', strtotime($g['fecha'])),
                'clase_efectivo' => 'egreso',
                'clase_tarjeta' => '',
                'clase_qr' => '',
                'clase_mp' => '',
                'clase_total' => 'egreso',
                'ticket_url' => ''
            ];
        }

        usort($movimientos, function($a, $b) {
            return strcmp($a['fecha_hora'], $b['fecha_hora']);
        });

        $totales = ['efectivo' => 0, 'tarjeta' => 0, 'qr' => 0, 'mp' => 0, 'total' => 0];
        foreach($movimientos as $m) {
            $totales['efectivo'] += floatval($m['efectivo'] ?: 0);
            $totales['tarjeta'] += floatval($m['tarjeta'] ?: 0);
            $totales['qr']      += floatval($m['qr'] ?: 0);
            $totales['mp']      += floatval($m['mp'] ?: 0);
            $totales['total']   += floatval($m['total'] ?: 0);
        }

        require __DIR__ . '/../views/cajero/libroDiario.php';
    }
}
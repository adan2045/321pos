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
        if (session_status() === PHP_SESSION_NONE) session_start();

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

    public function actionRegistrarGasto()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $redirect   = $_POST['redirect'] ?? null;
        $aceptaJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
        $esAjax     = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($redirect) { header("Location: " . $redirect); exit; }
            header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
            exit;
        }

        $motivo         = trim($_POST['motivo'] ?? '');
        $monto          = floatval($_POST['monto'] ?? 0);
        $autorizado_por = trim($_POST['autorizado_por'] ?? '');

        $db = \DataBase::getInstance()->getConnection();

        $usuario_id = null;
        if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            $usuario_id = (int)$_SESSION['user_id'];
            $chk = $db->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1");
            $chk->execute([$usuario_id]);
            if (!$chk->fetchColumn()) $usuario_id = null;
        }

        $caja_id = $_POST['caja_id'] ?? ($_SESSION['caja_id'] ?? null);

        if ($motivo === '' || $monto <= 0 || $autorizado_por === '' || empty($caja_id)) {
            if ($aceptaJson || $esAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios para registrar el gasto']);
                exit;
            }
            echo "<script>alert('Faltan datos obligatorios para registrar el gasto'); window.history.back();</script>";
            exit;
        }

        $stmt = $db->prepare("
            INSERT INTO gastos (fecha, monto, motivo, autorizado_por, usuario_id, caja_id)
            VALUES (NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$monto, $motivo, $autorizado_por, $usuario_id, $caja_id]);

        if ($aceptaJson || $esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'ok']);
            exit;
        }

        if ($redirect) { header("Location: " . $redirect); exit; }
        header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
        exit;
    }

    public function actionCuentaMesa()
    {
        if (!isset($_GET['id'])) { echo "Mesa no especificada"; return; }

        $mesaId = intval($_GET['id']);
        $pedidoModel = new \app\models\PedidoModel();
        $pedidos = $pedidoModel->obtenerPedidosActivosPorMesa($mesaId);

        $productos = [];
        $total = 0;
        foreach ($pedidos as $pedido) {
            foreach ($pedido['detalles'] as $detalle) {
                $subtotal = $detalle['precio'] * $detalle['cantidad'];
                $productos[] = [
                    'nombre'      => $detalle['nombre'],
                    'descripcion' => $detalle['descripcion'],
                    'precio'      => $detalle['precio'],
                    'cantidad'    => $detalle['cantidad'],
                    'subtotal'    => $subtotal
                ];
                $total += $subtotal;
            }
        }

        $mesaModel = new \app\models\MesaModel();
        $mesa = $mesaModel->obtenerPorId($mesaId);

        $head   = SiteController::head();
        $nav    = SiteController::nav();
        $footer = SiteController::footer();

        Response::render('cajero', 'cuenta', [
            'productos' => $productos,
            'total'     => $total,
            'mesa'      => $mesa,
            'ruta'      => \App::baseUrl(),
            'head'      => $head,
            'nav'       => $nav,
            'footer'    => $footer
        ]);
    }

    public function actionCuenta()
    {
        $pedidoId   = isset($_GET['id'])   ? intval($_GET['id'])   : null;
        $mesaNumero = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;

        $footer      = SiteController::footer();
        $head        = SiteController::head();
        $nav         = SiteController::nav();
        $pedidoModel = new \app\models\PedidoModel();

        if ($pedidoId) {
            $datos           = $pedidoModel->obtenerDetalleCuentaPorPedido($pedidoId);
            $esCuentaCerrada = true;
        } elseif ($mesaNumero) {
            $mesaModel = new \app\models\MesaModel();
            $mesa      = $mesaModel->obtenerPorNumero($mesaNumero);
            $mesaId    = $mesa['id'] ?? null;
            $datos     = $mesaId
                ? $pedidoModel->obtenerDetalleCuentaPorMesa($mesaId)
                : ['mesa' => [], 'productos' => [], 'total' => 0];
            $esCuentaCerrada = false;
        } else {
            $datos           = ['mesa' => [], 'productos' => [], 'total' => 0];
            $esCuentaCerrada = true;
        }

        Response::render('cajero', 'cuenta', [
            'head'           => $head,
            'title'          => 'Cuenta',
            'nav'            => $nav,
            'footer'         => $footer,
            'mesa'           => $datos['mesa'],
            'productos'      => $datos['productos'],
            'total'          => $datos['total'],
            'ruta'           => \App::baseUrl(),
            'esCuentaCerrada'=> $esCuentaCerrada,
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
            $mesa_id    = $_POST['mesa_id']    ?? null;
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
        $head   = \app\controllers\SiteController::head();
        $nav    = \app\controllers\SiteController::nav();
        $path   = static::path();

        $cajaId = $_SESSION['caja_id'] ?? null;

        if ($cajaId) {
            $cajaModel = new \app\models\CajaModel();
            $datos     = $cajaModel->obtenerTotalesDelDia();
            $productos = $cajaModel->resumenPorProducto();
            $gastos    = $cajaModel->obtenerGastosDelDia();
            $datos['total_gastos']    = $gastos['total'];
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
            "title"     => "Planilla de Caja",
            "head"      => $head,
            "nav"       => $nav,
            "footer"    => $footer,
            "datos"     => $datos,
            "productos" => $productos,
            "ruta"      => $path,
        ]);
    }

    public function actionCambiarMedioPago()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pedidoId  = $_POST['pedido_id']  ?? null;
            $medioPago = $_POST['medio_pago'] ?? null;

            if ($pedidoId && $medioPago) {
                $db   = \DataBase::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE pedidos SET metodo_pago = ? WHERE id = ?");
                $ok   = $stmt->execute([$medioPago, $pedidoId]);
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
            $monto       = floatval($_POST['monto'] ?? 0);
            $responsable = trim($_POST['responsable'] ?? '');
            $caja_id     = $_POST['caja_id'] ?? ($_SESSION['caja_id'] ?? null);

            if ($monto > 0 && !empty($responsable) && !empty($caja_id)) {
                $db   = \DataBase::getInstance()->getConnection();
                $stmt = $db->prepare("INSERT INTO caja_fuerte (fecha, monto, responsable, caja_id) VALUES (NOW(), ?, ?, ?)");
                $stmt->execute([$monto, $responsable, $caja_id]);

                $redirect = $_POST['redirect'] ?? (\App::baseUrl() . "/pos");
                header("Location: " . $redirect);
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

        $monto = 0.0;
        if (isset($_POST['monto']))     $monto = floatval($_POST['monto']);
        elseif (isset($_GET['monto']))  $monto = floatval($_GET['monto']);

        if ($monto < 0 || $monto > 10000000) {
            $_SESSION['mensaje_error'] = "El monto debe ser entre 0 y 10.000.000.";
            header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
            exit;
        }

        $hoy = date('Y-m-d');
        $db  = \DataBase::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM cajas WHERE DATE(fecha_apertura) = ? AND fecha_cierre IS NULL");
        $stmt->execute([$hoy]);
        $cajaAbierta = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($cajaAbierta) {
            $_SESSION['caja_abierta'] = true;
            $_SESSION['caja_id']      = $cajaAbierta['id'];
            $_SESSION['caja_apertura']= $cajaAbierta['fecha_apertura'];
            $_SESSION['saldo_inicial']= $cajaAbierta['saldo_inicial'];
            header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
            exit;
        }

        $stmt = $db->prepare("INSERT INTO cajas (fecha_apertura, saldo_inicial) VALUES (NOW(), ?)");
        $stmt->execute([$monto]);
        $cajaId = $db->lastInsertId();

        $_SESSION['caja_abierta'] = true;
        $_SESSION['caja_id']      = $cajaId;
        $_SESSION['caja_apertura']= date('Y-m-d H:i:s');
        $_SESSION['saldo_inicial']= $monto;

        header("Location: " . \App::baseUrl() . "/cajero/planillaCaja");
        exit;
    }

    // ══════════════════════════════════════════════
    //  FICHAR — Estado
    // ══════════════════════════════════════════════
    public function actionFicharEstado()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status'=>'error','message'=>'Método no permitido']);
            return;
        }

        $db      = \DataBase::getInstance()->getConnection();
        $payload = json_decode((string)file_get_contents('php://input'), true) ?: [];

        $empleadoId     = isset($payload['empleado_id'])     ? (int)$payload['empleado_id']          : 0;
        $empleadoNumero = isset($payload['empleado_numero']) ? trim((string)$payload['empleado_numero']) : '';

        // Buscar empleado
        if ($empleadoId <= 0) {
            if ($empleadoNumero === '') {
                http_response_code(400);
                echo json_encode(['status'=>'error','message'=>'Falta empleado_numero']);
                return;
            }
            $st = $db->prepare("SELECT id, nombre FROM empleados WHERE numero_empleado = ? LIMIT 1");
            $st->execute([$empleadoNumero]);
            $emp = $st->fetch(\PDO::FETCH_ASSOC);
        } else {
            $st = $db->prepare("SELECT id, nombre FROM empleados WHERE id = ? LIMIT 1");
            $st->execute([$empleadoId]);
            $emp = $st->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$emp) {
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Empleado no encontrado']);
            return;
        }

        $empleadoId = (int)$emp['id'];
        $nombre     = (string)$emp['nombre'];
        $hoy        = date('Y-m-d');

        // Última fichada del día (cualquier tipo)
        $stLast = $db->prepare("
            SELECT tipo, fecha_hora
            FROM fichadas
            WHERE empleado_id = ? AND fecha = ?
            ORDER BY fecha_hora DESC
            LIMIT 1
        ");
        $stLast->execute([$empleadoId, $hoy]);
        $ultima = $stLast->fetch(\PDO::FETCH_ASSOC);

        // Última entrada del día
        $stEnt = $db->prepare("
            SELECT fecha_hora FROM fichadas
            WHERE empleado_id = ? AND fecha = ? AND tipo = 'entrada'
            ORDER BY fecha_hora DESC LIMIT 1
        ");
        $stEnt->execute([$empleadoId, $hoy]);
        $entrada = $stEnt->fetchColumn();

        // Última salida del día
        $stSal = $db->prepare("
            SELECT fecha_hora FROM fichadas
            WHERE empleado_id = ? AND fecha = ? AND tipo = 'salida'
            ORDER BY fecha_hora DESC LIMIT 1
        ");
        $stSal->execute([$empleadoId, $hoy]);
        $salida = $stSal->fetchColumn();

        $enTurno     = false;
        $segundos    = 0;
        $entradaHora = null;
        $salidaHora  = null;

        if ($entrada) {
            $entradaHora = date('H:i', strtotime($entrada));

            if (!$salida || strtotime($salida) < strtotime($entrada)) {
                // En turno: la última fichada es entrada
                $enTurno  = true;
                $segundos = max(0, time() - strtotime($entrada));
            } else {
                // La última fichada es salida
                $salidaHora = date('H:i', strtotime($salida));
            }
        }

        echo json_encode([
            'status'              => 'ok',
            'empleado_id'         => $empleadoId,
            'empleado_nombre'     => $nombre,
            'en_turno'            => $enTurno,
            'entrada_hora'        => $entradaHora,
            'salida_hora'         => $salidaHora,
            'segundos_trabajados' => $segundos,
        ]);
    }

    // ══════════════════════════════════════════════
    //  FICHAR — Registrar entrada o salida
    // ══════════════════════════════════════════════
    public function actionFicharRegistrar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status'=>'error','message'=>'Método no permitido']);
            return;
        }

        $payload    = json_decode((string)file_get_contents('php://input'), true) ?: [];
        $empleadoId = (int)($payload['empleado_id'] ?? 0);
        $tipo       = strtolower(trim((string)($payload['tipo'] ?? '')));

        if ($empleadoId <= 0 || !in_array($tipo, ['entrada','salida'], true)) {
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'Datos inválidos']);
            return;
        }

        $db  = \DataBase::getInstance()->getConnection();
        $hoy = date('Y-m-d');

        // ── Validar que no repita el mismo tipo consecutivo ──
        $stCheck = $db->prepare("
            SELECT tipo FROM fichadas
            WHERE empleado_id = ? AND fecha = ?
            ORDER BY fecha_hora DESC
            LIMIT 1
        ");
        $stCheck->execute([$empleadoId, $hoy]);
        $ultimoTipo = $stCheck->fetchColumn();

        if ($ultimoTipo === $tipo) {
            http_response_code(409);
            echo json_encode([
                'status'  => 'error',
                'message' => $tipo === 'salida'
                    ? 'Ya fichaste salida. Debés fichar entrada primero.'
                    : 'Ya estás en turno. Debés fichar salida primero.'
            ]);
            return;
        }

        // registrado_por
        $registradoPor = null;
        if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            $registradoPor = (int)$_SESSION['user_id'];
            $chk = $db->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1");
            $chk->execute([$registradoPor]);
            if (!$chk->fetchColumn()) $registradoPor = null;
        }

        $negocioId = isset($_SESSION['negocio_id']) ? (int)$_SESSION['negocio_id'] : null;
        $ahora     = date('Y-m-d H:i:s');

        // Calcular tiempo trabajado si es salida
        $tiempoMin = null;
        if ($tipo === 'salida') {
            $stEnt = $db->prepare("
                SELECT fecha_hora FROM fichadas
                WHERE empleado_id = ? AND fecha = ? AND tipo = 'entrada'
                ORDER BY fecha_hora DESC LIMIT 1
            ");
            $stEnt->execute([$empleadoId, $hoy]);
            $ultimaEntrada = $stEnt->fetchColumn();

            if ($ultimaEntrada) {
                $tiempoMin = (int) floor((strtotime($ahora) - strtotime($ultimaEntrada)) / 60);
            }
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO fichadas
                  (negocio_id, empleado_id, tipo, fecha_hora, fecha, tardanza_min, es_tardanza, tiempo_trabajado_min, turno, registrado_por, notas)
                VALUES
                  (?, ?, ?, ?, ?, NULL, 0, ?, 1, ?, NULL)
            ");
            $stmt->execute([
                $negocioId,
                $empleadoId,
                $tipo,
                $ahora,
                $hoy,
                $tiempoMin,
                $registradoPor
            ]);

            $db->commit();
            echo json_encode(['status' => 'ok']);
            return;

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
            return;
        }
    }

    public function actionFichar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . \App::baseUrl() . "/pos");
            exit;
        }

        $accion   = strtolower(trim($_POST['accion'] ?? ''));
        $obs      = trim($_POST['obs'] ?? '');
        $redirect = $_POST['redirect'] ?? (\App::baseUrl() . "/pos");

        if (!in_array($accion, ['entrada', 'salida'], true)) {
            $_SESSION['mensaje_error'] = "Acción inválida para fichar.";
            header("Location: " . $redirect);
            exit;
        }

        $db         = \DataBase::getInstance()->getConnection();
        $usuario_id = null;
        if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            $usuario_id = (int)$_SESSION['user_id'];
            $chk = $db->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1");
            $chk->execute([$usuario_id]);
            if (!$chk->fetchColumn()) $usuario_id = null;
        }

        $negocio_id = (int)($_SESSION['negocio_id'] ?? 1);

        try {
            $stmt = $db->prepare("
                INSERT INTO fichadas (fecha, accion, observacion, usuario_id, negocio_id)
                VALUES (NOW(), ?, ?, ?, ?)
            ");
            $stmt->execute([$accion, ($obs !== '' ? $obs : null), $usuario_id, $negocio_id]);

            $_SESSION['mensaje_exito'] = "Fichada registrada.";
            header("Location: " . $redirect);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['mensaje_error'] = "Error al fichar: " . $e->getMessage();
            header("Location: " . $redirect);
            exit;
        }
    }

    public function actionCerrarCaja()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $cajaModel  = new \app\models\CajaModel();
            $usuarioId  = $_SESSION['user_id'] ?? null;
            $datos      = $cajaModel->obtenerTotalesDelDia();
            $saldoCierre= $datos['saldo'];

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
        $db = \DataBase::getInstance()->getConnection();

        $cajaId           = $_SESSION['caja_id'] ?? null;
        $fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d');

        if (!$cajaId) {
            $stmt = $db->prepare("SELECT id FROM cajas WHERE DATE(fecha_apertura) = ? ORDER BY fecha_apertura DESC LIMIT 1");
            $stmt->execute([$fechaSeleccionada]);
            $cajaId = $stmt->fetchColumn();
        }

        $inicioCaja   = 0;
        $fechaApertura = '';
        if ($cajaId) {
            $stmt = $db->prepare("SELECT saldo_inicial, fecha_apertura FROM cajas WHERE id = ?");
            $stmt->execute([$cajaId]);
            $row           = $stmt->fetch(\PDO::FETCH_ASSOC);
            $inicioCaja    = $row['saldo_inicial'] ?? 0;
            $fechaApertura = $row['fecha_apertura'] ?? '';
        }

        $stmt = $db->prepare("
            SELECT p.id, p.total, p.metodo_pago, p.mesa_id, p.fecha, p.fecha_cierre
            FROM pedidos p
            WHERE p.caja_id = ? AND p.cerrado = 1
              AND DATE(COALESCE(p.fecha_cierre, p.fecha)) = ?
        ");
        $stmt->execute([$cajaId, $fechaSeleccionada]);
        $ventas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT id, monto, motivo, autorizado_por, fecha FROM gastos WHERE caja_id = ? AND DATE(fecha) = ?");
        $stmt->execute([$cajaId, $fechaSeleccionada]);
        $gastos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT id, monto, responsable, fecha FROM caja_fuerte WHERE caja_id = ? AND DATE(fecha) = ?");
        $stmt->execute([$cajaId, $fechaSeleccionada]);
        $cajaFuerte = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $cacheMesas   = [];
        $getNumeroMesa = function($mesaId) use (&$cacheMesas, $db) {
            if (!$mesaId) return '';
            if (isset($cacheMesas[$mesaId])) return $cacheMesas[$mesaId];
            $stmtMesa = $db->prepare("SELECT numero FROM mesas WHERE id = ?");
            $stmtMesa->execute([$mesaId]);
            $num = $stmtMesa->fetchColumn();
            $cacheMesas[$mesaId] = $num ?: '';
            return $cacheMesas[$mesaId];
        };

        $movimientos   = [];
        $movimientos[] = [
            'tipo'          => 'inicio',
            'detalle'       => "INICIO DE CAJA",
            'efectivo'      => $inicioCaja,
            'tarjeta'       => '',
            'qr'            => '',
            'mp'            => '',
            'total'         => $inicioCaja,
            'mesa'          => '',
            'fecha_hora'    => $fechaApertura ? date('Y-m-d H:i', strtotime($fechaApertura)) : $fechaSeleccionada . ' 08:00',
            'clase_efectivo'=> 'entrada',
            'clase_tarjeta' => '',
            'clase_qr'      => '',
            'clase_mp'      => '',
            'clase_total'   => 'entrada',
            'ticket_url'    => ''
        ];

        $stmtPagos = $db->prepare("
            SELECT mp.clave, SUM(pp.monto) as monto
            FROM pedido_pagos pp
            JOIN metodos_pago mp ON mp.id = pp.metodo_pago_id
            WHERE pp.pedido_id = ?
            GROUP BY mp.clave
        ");

        foreach ($ventas as $v) {
            $detalle = "TICKET #" . str_pad($v['id'], 4, '0', STR_PAD_LEFT);
            $metodo  = strtolower((string)$v['metodo_pago']);
            $ef = $tj = $qr = $mp = '';

            if ($metodo === 'dividido') {
                $stmtPagos->execute([$v['id']]);
                foreach ($stmtPagos->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $clave = strtolower((string)($r['clave'] ?? ''));
                    $monto = $r['monto'] ?? 0;
                    if ($clave === 'efectivo')    $ef = $monto;
                    if ($clave === 'tarjeta')     $tj = $monto;
                    if ($clave === 'qr')          $qr = $monto;
                    if ($clave === 'mercadopago') $mp = $monto;
                }
            } else {
                if ($metodo === 'efectivo')    $ef = $v['total'];
                if ($metodo === 'tarjeta')     $tj = $v['total'];
                if ($metodo === 'qr')          $qr = $v['total'];
                if ($metodo === 'mercadopago') $mp = $v['total'];
            }

            $fechaMov      = $v['fecha_cierre'] ?: $v['fecha'];
            $movimientos[] = [
                'tipo'          => 'venta',
                'detalle'       => $detalle,
                'efectivo'      => $ef,
                'tarjeta'       => $tj,
                'qr'            => $qr,
                'mp'            => $mp,
                'total'         => $v['total'],
                'mesa'          => $getNumeroMesa($v['mesa_id']),
                'fecha_hora'    => date('Y-m-d H:i', strtotime($fechaMov)),
                'clase_efectivo'=> $ef !== '' ? 'venta' : '',
                'clase_tarjeta' => $tj !== '' ? 'venta' : '',
                'clase_qr'      => $qr !== '' ? 'venta' : '',
                'clase_mp'      => $mp !== '' ? 'venta' : '',
                'clase_total'   => 'venta',
                'ticket_url'    => \App::baseUrl() . '/cajero/cuenta?id=' . $v['id']
            ];
        }

        foreach ($cajaFuerte as $cf) {
            $montoCf       = -abs($cf['monto']);
            $movimientos[] = [
                'tipo'          => 'caja_fuerte',
                'detalle'       => "Depósito Caja Fuerte (" . $cf['responsable'] . ")",
                'efectivo'      => $montoCf,
                'tarjeta'       => '', 'qr' => '', 'mp' => '',
                'total'         => $montoCf,
                'mesa'          => '',
                'fecha_hora'    => date('Y-m-d H:i', strtotime($cf['fecha'])),
                'clase_efectivo'=> 'egreso',
                'clase_tarjeta' => '', 'clase_qr' => '', 'clase_mp' => '',
                'clase_total'   => 'egreso',
                'ticket_url'    => ''
            ];
        }

        foreach ($gastos as $g) {
            $movimientos[] = [
                'tipo'          => 'gasto',
                'detalle'       => "GASTO: " . $g['motivo'] . ($g['autorizado_por'] ? " (aut: " . $g['autorizado_por'] . ")" : ""),
                'efectivo'      => -abs($g['monto']),
                'tarjeta'       => '', 'qr' => '', 'mp' => '',
                'total'         => -abs($g['monto']),
                'mesa'          => '',
                'fecha_hora'    => date('Y-m-d H:i', strtotime($g['fecha'])),
                'clase_efectivo'=> 'egreso',
                'clase_tarjeta' => '', 'clase_qr' => '', 'clase_mp' => '',
                'clase_total'   => 'egreso',
                'ticket_url'    => ''
            ];
        }

        usort($movimientos, fn($a, $b) => strcmp($a['fecha_hora'], $b['fecha_hora']));

        $totales = ['efectivo' => 0, 'tarjeta' => 0, 'qr' => 0, 'mp' => 0, 'total' => 0];
        foreach ($movimientos as $m) {
            $totales['efectivo'] += floatval($m['efectivo'] ?: 0);
            $totales['tarjeta']  += floatval($m['tarjeta']  ?: 0);
            $totales['qr']       += floatval($m['qr']       ?: 0);
            $totales['mp']       += floatval($m['mp']       ?: 0);
            $totales['total']    += floatval($m['total']    ?: 0);
        }

        require __DIR__ . '/../views/cajero/libroDiario.php';
    }
}
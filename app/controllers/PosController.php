<?php
namespace app\controllers;

use \Controller;
use \Response;
use app\models\CajaModel;
use \DataBase;

class PosController extends Controller
{
    public function __construct() {}

    public function actionIndex($var = null)
    {
        // Base path del proyecto (ej: /321POS/public/)
        $ruta = static::path();

        // Si querés chequear sesión acá, lo enchufás:
        // SesionController::verificarSesion();

        // Datos para los modales de gestión (monto sugerido = último cierre)
        $cajaModel = new CajaModel();
        $ultimoCierre = $cajaModel->obtenerUltimoCierre();

        // Layout
        $footer = SiteController::footer();
        $head   = SiteController::head();
        $nav    = SiteController::nav();

        Response::render($this->viewDir(__NAMESPACE__), "index", [
            "title"        => "321POS - Mostrador",
            "ruta"         => $ruta,
            "ultimoCierre" => $ultimoCierre,
            "head"         => $head,
            "nav"          => $nav,
            "footer"       => $footer,
        ]);
    }

    /**
     * Cobra un ticket de mostrador desde el POS (AJAX).
     * Espera JSON:
     * {
     *   "items": [{"codigo":1001,"precio":1500,"cantidad":2}],
     *   "notas": "...",
     *   "pagos": [{"metodo":"efectivo","monto":3000.00}]
     * }
     */
    public function actionCobrar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
            return;
        }

        $payload = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'JSON inválido']);
            return;
        }

        $items = $payload['items'] ?? [];
        $pagos = $payload['pagos'] ?? [];
        $notas = trim((string)($payload['notas'] ?? ''));

        if (!is_array($items) || count($items) === 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ticket vacío']);
            return;
        }
        if (!is_array($pagos) || count($pagos) === 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se informaron pagos']);
            return;
        }

        $negocioId = (int)($_SESSION['negocio_id'] ?? 1);
        $cajaId    = $_SESSION['caja_id'] ?? null;

        // ✅ FIX: sin login -> usuarioId NULL (y validar si viene algo)
        $usuarioId = null;
        if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            $usuarioId = (int)$_SESSION['user_id'];
        }

        if (!$cajaId) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'No hay caja abierta']);
            return;
        }

        // Normalizar items + total
        $subtotal = 0.0;
        $itemsNorm = [];
        foreach ($items as $it) {
            $codigo   = (int)($it['codigo'] ?? 0);
            $cantidad = (float)($it['cantidad'] ?? 0);
            $precio   = (float)($it['precio'] ?? 0);
            if ($codigo <= 0 || $cantidad <= 0 || $precio < 0) continue;
            $linea = round($cantidad * $precio, 2);
            $subtotal += $linea;
            $itemsNorm[] = [
                'producto_id' => $codigo,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal' => $linea,
            ];
        }
        $subtotal = round($subtotal, 2);
        if (count($itemsNorm) === 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ítems inválidos']);
            return;
        }

        // Normalizar pagos
        $pagosNorm = [];
        $sumaPagos = 0.0;
        foreach ($pagos as $p) {
            $metodo = strtolower(trim((string)($p['metodo'] ?? '')));
            $monto  = (float)($p['monto'] ?? 0);
            if ($monto <= 0) continue;
            if (!in_array($metodo, ['efectivo','tarjeta','mercadopago','qr'], true)) continue;
            $monto = round($monto, 2);
            $sumaPagos += $monto;
            $pagosNorm[] = ['metodo' => $metodo, 'monto' => $monto];
        }
        $sumaPagos = round($sumaPagos, 2);
        if (count($pagosNorm) === 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Pagos inválidos']);
            return;
        }

        if (abs($sumaPagos - $subtotal) > 0.01) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'La suma de pagos no coincide con el total']);
            return;
        }

        $metodoPedido = (count($pagosNorm) > 1) ? 'dividido' : $pagosNorm[0]['metodo'];

        $db = DataBase::getInstance()->getConnection();
        try {
            $db->beginTransaction();

            // ✅ FIX: si viene usuarioId, validar que exista para no romper FK
            if ($usuarioId !== null) {
                $chk = $db->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1");
                $chk->execute([$usuarioId]);
                if (!$chk->fetchColumn()) $usuarioId = null;
            }

            $stmt = $db->prepare("
                INSERT INTO pedidos
                  (negocio_id, caja_id, usuario_id, usuario_cierre_id, mesa_id, fecha, fecha_cierre, tipo, estado, cerrado, subtotal, descuento_monto, total, metodo_pago, fuente, notas, minutos_atencion)
                VALUES
                  (?, ?, ?, ?, NULL, NOW(), NOW(), 'mostrador', 'cerrado', 1, ?, 0, ?, ?, 'local', ?, 0)
            ");
            $stmt->execute([
                $negocioId,
                (int)$cajaId,
                $usuarioId,
                $usuarioId, // usuario_cierre_id también NULL si no hay login
                $subtotal,
                $subtotal,
                $metodoPedido,
                $notas !== '' ? $notas : null,
            ]);
            $pedidoId = (int)$db->lastInsertId();

            $stmtDet = $db->prepare("
                INSERT INTO pedido_detalle
                  (pedido_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal, estado, notas)
                VALUES
                  (?, ?, ?, ?, 0, ?, 'completado', NULL)
            ");
            foreach ($itemsNorm as $itn) {
                $stmtDet->execute([
                    $pedidoId,
                    $itn['producto_id'],
                    $itn['cantidad'],
                    $itn['precio'],
                    $itn['subtotal'],
                ]);
            }

            $stmtMpId = $db->prepare("SELECT id FROM metodos_pago WHERE clave = ? LIMIT 1");
            $stmtPago = $db->prepare("
                INSERT INTO pedido_pagos (pedido_id, metodo_pago_id, monto, referencia, fecha)
                VALUES (?, ?, ?, NULL, NOW())
            ");
            foreach ($pagosNorm as $pn) {
                $stmtMpId->execute([$pn['metodo']]);
                $metodoId = (int)$stmtMpId->fetchColumn();
                if (!$metodoId) throw new \Exception('Método de pago no configurado: ' . $pn['metodo']);
                $stmtPago->execute([$pedidoId, $metodoId, $pn['monto']]);
            }

            $db->commit();
            echo json_encode(['status' => 'ok', 'pedido_id' => $pedidoId]);
            return;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            return;
        }
    }
}
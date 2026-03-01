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
        // 🔥 Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Base path del proyecto
        $ruta = static::path();

        // Detectar si hay caja abierta
        $cajaAbierta = !empty($_SESSION['caja_id']);

        // Datos para modales
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
            "cajaAbierta"  => $cajaAbierta, // 👈 ESTA ES LA CLAVE
            "head"         => $head,
            "nav"          => $nav,
            "footer"       => $footer,
        ]);
    }

    /**
     * Cobra un ticket de mostrador desde el POS (AJAX)
     */
    public function actionCobrar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        if (!$items) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ticket vacío']);
            return;
        }

        if (!$pagos) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se informaron pagos']);
            return;
        }

        $negocioId = (int)($_SESSION['negocio_id'] ?? 1);
        $cajaId    = $_SESSION['caja_id'] ?? null;
        $usuarioId = $_SESSION['user_id'] ?? null;

        if (!$cajaId) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'No hay caja abierta']);
            return;
        }

        // ---- Normalizar items ----
        $subtotal = 0;
        $itemsNorm = [];

        foreach ($items as $it) {
            $codigo   = (int)($it['codigo'] ?? 0);
            $cantidad = (float)($it['cantidad'] ?? 0);
            $precio   = (float)($it['precio'] ?? 0);

            if ($codigo <= 0 || $cantidad <= 0) continue;

            $linea = round($cantidad * $precio, 2);
            $subtotal += $linea;

            $itemsNorm[] = [
                'producto_id' => $codigo,
                'cantidad'    => $cantidad,
                'precio'      => $precio,
                'subtotal'    => $linea
            ];
        }

        $subtotal = round($subtotal, 2);

        if (!$itemsNorm) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ítems inválidos']);
            return;
        }

        // ---- Normalizar pagos ----
        $pagosNorm = [];
        $sumaPagos = 0;

        foreach ($pagos as $p) {
            $metodo = strtolower($p['metodo'] ?? '');
            $monto  = round((float)($p['monto'] ?? 0), 2);

            if ($monto <= 0) continue;

            $sumaPagos += $monto;
            $pagosNorm[] = ['metodo' => $metodo, 'monto' => $monto];
        }

        if (abs($sumaPagos - $subtotal) > 0.01) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'La suma de pagos no coincide']);
            return;
        }

        $metodoPedido = count($pagosNorm) > 1 ? 'dividido' : $pagosNorm[0]['metodo'];

        $db = DataBase::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO pedidos
                (negocio_id, caja_id, usuario_id, usuario_cierre_id, mesa_id, fecha, fecha_cierre,
                 tipo, estado, cerrado, subtotal, descuento_monto, total, metodo_pago, fuente, notas, minutos_atencion)
                VALUES (?, ?, ?, ?, NULL, NOW(), NOW(),
                        'mostrador', 'cerrado', 1, ?, 0, ?, ?, 'local', ?, 0)
            ");

            $stmt->execute([
                $negocioId,
                $cajaId,
                $usuarioId,
                $usuarioId,
                $subtotal,
                $subtotal,
                $metodoPedido,
                $notas ?: null
            ]);

            $pedidoId = (int)$db->lastInsertId();

            $stmtDet = $db->prepare("
                INSERT INTO pedido_detalle
                (pedido_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal, estado, notas)
                VALUES (?, ?, ?, ?, 0, ?, 'completado', NULL)
            ");

            foreach ($itemsNorm as $it) {
                $stmtDet->execute([
                    $pedidoId,
                    $it['producto_id'],
                    $it['cantidad'],
                    $it['precio'],
                    $it['subtotal']
                ]);
            }

            $stmtMpId = $db->prepare("SELECT id FROM metodos_pago WHERE clave = ? LIMIT 1");
            $stmtPago = $db->prepare("
                INSERT INTO pedido_pagos (pedido_id, metodo_pago_id, monto, referencia, fecha)
                VALUES (?, ?, ?, NULL, NOW())
            ");

            foreach ($pagosNorm as $pn) {
                $stmtMpId->execute([$pn['metodo']]);
                $metodoId = $stmtMpId->fetchColumn();

                if (!$metodoId) {
                    throw new \Exception('Método de pago no configurado');
                }

                $stmtPago->execute([$pedidoId, $metodoId, $pn['monto']]);
            }

            $db->commit();

            echo json_encode(['status' => 'ok', 'pedido_id' => $pedidoId]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
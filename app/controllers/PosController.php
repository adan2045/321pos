<?php
namespace app\controllers;

use \Controller;
use \Response;
use app\models\CajaModel;

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
    public function actionGuardarPedido()
{
    header('Content-Type: application/json');

    $db = \DataBase::getInstance();
    $conn = $db->getConnection();

    try {
        $conn->beginTransaction();

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || empty($data['items'])) {
            throw new \Exception("Ticket vacío");
        }

        $negocio_id = 1;
        $caja_id = 1;
        $usuario_id = 1;

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }

        $total = $subtotal;

        // 1️⃣ Insertar pedido
        $stmt = $conn->prepare("
            INSERT INTO pedidos 
            (negocio_id, caja_id, usuario_id, tipo, estado, cerrado, subtotal, total, metodo_pago, fecha_cierre, notas)
            VALUES (?, ?, ?, 'mostrador', 'cerrado', 1, ?, ?, ?, NOW(), ?)
        ");

        $stmt->execute([
            $negocio_id,
            $caja_id,
            $usuario_id,
            $subtotal,
            $total,
            $data['metodo'],
            $data['notas'] ?? null
        ]);

        $pedido_id = $conn->lastInsertId();

        // 2️⃣ Insertar detalle
        $stmtDetalle = $conn->prepare("
            INSERT INTO pedido_detalle
            (pedido_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($data['items'] as $item) {

            // obtener costo actual del producto
            $stmtCosto = $conn->prepare("SELECT costo FROM productos WHERE id = ?");
            $stmtCosto->execute([$item['codigo']]);
            $prod = $stmtCosto->fetch(\PDO::FETCH_ASSOC);

            $costo = $prod ? $prod['costo'] : 0;
            $sub = $item['precio'] * $item['cantidad'];

            $stmtDetalle->execute([
                $pedido_id,
                $item['codigo'],
                $item['cantidad'],
                $item['precio'],
                $costo,
                $sub
            ]);
        }

        // 3️⃣ Insertar pago
        $stmtMetodo = $conn->prepare("SELECT id FROM metodos_pago WHERE clave = ?");
        $stmtMetodo->execute([$data['metodo']]);
        $metodo = $stmtMetodo->fetch(\PDO::FETCH_ASSOC);

        $metodo_id = $metodo ? $metodo['id'] : 1;

        $stmtPago = $conn->prepare("
            INSERT INTO pedido_pagos
            (pedido_id, metodo_pago_id, monto)
            VALUES (?, ?, ?)
        ");

        $stmtPago->execute([
            $pedido_id,
            $metodo_id,
            $total
        ]);

        // 4️⃣ Registrar movimiento de caja (VENTA)
        $stmtMov = $conn->prepare("
           INSERT INTO movimientos_caja
            (caja_id, tipo, referencia_id, numero_ticket, descripcion, metodo_pago, ingreso, egreso)
          VALUES (?, 'venta', ?, ?, ?, ?, ?, 0)
        ");

        $stmtMov->execute([
        $caja_id,
        $pedido_id,
        $pedido_id, // usamos id como número de ticket por ahora
        "Venta Ticket #".$pedido_id,
        $data['metodo'],
        $total
        ]);
        $conn->commit();

        echo json_encode(["status" => "ok"]);

    } catch (\Exception $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
}
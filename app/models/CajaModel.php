<?php
namespace app\models;

use \DataBase;

class CajaModel
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getInstance()->getConnection();
    }
    public function obtenerSaldoInicialPorCajaId($cajaId)
{
    $sql = "SELECT saldo_inicial FROM cajas WHERE id = :cajaId LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['cajaId' => $cajaId]);
    $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $fila ? floatval($fila['saldo_inicial']) : 0.0;
}
    public function obtenerSaldoInicial($fecha = null)
{
    // Tomar la fecha del día consultado
    $fechaHoy = $fecha ?: date('Y-m-d');
    // Buscar el saldo_inicial de la última caja abierta ese día (por si se abrió varias veces)
    $sql = "SELECT saldo_inicial FROM cajas WHERE DATE(fecha_apertura) = :fechaHoy ORDER BY fecha_apertura DESC LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['fechaHoy' => $fechaHoy]);
    $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $fila ? floatval($fila['saldo_inicial']) : 0.0;
}
    // Totales del día agrupados por método de pago, usando SOLO pedidos cerrados
    public function obtenerTotalesDelDia($fecha = null)
{
    $fechaHoy = $fecha ?: date('Y-m-d');

    // 1. Buscar caja actual (id) por sesión o por fecha
    if (session_status() === PHP_SESSION_NONE) session_start();
    $cajaId = isset($_SESSION['caja_id']) ? $_SESSION['caja_id'] : null;
    if (!$cajaId) {
        // Si no está en sesión, buscar el último id de caja abierta en la fecha
        $stmt = $this->db->prepare("SELECT id FROM cajas WHERE DATE(fecha_apertura) = :fechaHoy ORDER BY fecha_apertura DESC LIMIT 1");
        $stmt->execute(['fechaHoy' => $fechaHoy]);
        $cajaId = $stmt->fetchColumn();
    }

    // 2. ¿Todos los pedidos de hoy ya tienen caja_id?
    $allPedidosTienenCajaId = false;
    if ($cajaId) {
        $sqlCheck = "SELECT COUNT(*) as sinCaja FROM pedidos WHERE DATE(fecha) = :fechaHoy AND caja_id IS NULL";
        $stmt = $this->db->prepare($sqlCheck);
        $stmt->execute(['fechaHoy' => $fechaHoy]);
        $sinCaja = $stmt->fetchColumn();
        $allPedidosTienenCajaId = ($sinCaja == 0);
    }

    // 3. SUMAR SEGÚN CAJA O FECHA SEGÚN DISPONIBILIDAD
    $wherePedidos = $allPedidosTienenCajaId && $cajaId
        ? "caja_id = :cajaId"
        : "DATE(fecha) = :fechaHoy";

    $param = $allPedidosTienenCajaId && $cajaId
        ? ['cajaId' => $cajaId]
        : ['fechaHoy' => $fechaHoy];

    // SUMA pedidos cerrados por método de pago
    $sql = "SELECT metodo_pago, SUM(total) as total, COUNT(*) as cantidad
            FROM pedidos
            WHERE cerrado = 1 AND $wherePedidos
            GROUP BY metodo_pago";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($param);
    $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $pagos = [
        'efectivo'     => ['total' => 0, 'cantidad' => 0],
        'tarjeta'      => ['total' => 0, 'cantidad' => 0],
        'mercadopago'  => ['total' => 0, 'cantidad' => 0],
        'qr'           => ['total' => 0, 'cantidad' => 0]
    ];

    foreach ($resultados as $row) {
        $metodo = strtolower(trim($row['metodo_pago'] ?? ''));
        if (isset($pagos[$metodo])) {
            $pagos[$metodo]['total'] = $row['total'];
            $pagos[$metodo]['cantidad'] = $row['cantidad'];
        }
    }

    // Total general del día
    $sqlTotal = "SELECT COALESCE(SUM(total), 0) as venta_bruta, COUNT(*) as cantidad_pedidos
                 FROM pedidos
                 WHERE cerrado = 1 AND $wherePedidos";
    $stmt = $this->db->prepare($sqlTotal);
    $stmt->execute($param);
    $datos = $stmt->fetch(\PDO::FETCH_ASSOC);

    // Obtener inicio de caja
    $inicioCaja = $cajaId
        ? $this->obtenerSaldoInicialPorCajaId($cajaId)
        : $this->obtenerSaldoInicial($fechaHoy);

    // SUMA gastos y caja fuerte SOLO DE ESTA CAJA
    $paramCaja = $cajaId ? ['cajaId' => $cajaId] : ['fechaHoy' => $fechaHoy];
    $whereCaja = $cajaId ? "caja_id = :cajaId" : "DATE(fecha) = :fechaHoy";

    // Gastos
    $sqlGastos = "SELECT COALESCE(SUM(monto),0) as total_gastos, COUNT(*) as cantidad_gastos FROM gastos WHERE $whereCaja";
    $stmt = $this->db->prepare($sqlGastos);
    $stmt->execute($paramCaja);
    $gastosRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    $totalGastos = $gastosRow ? floatval($gastosRow['total_gastos']) : 0;
    $cantidadGastos = $gastosRow ? intval($gastosRow['cantidad_gastos']) : 0;

    // Caja fuerte
    $sqlCF = "SELECT COALESCE(SUM(monto),0) as total_cf, COUNT(*) as cantidad_cf FROM caja_fuerte WHERE $whereCaja";
    $stmt = $this->db->prepare($sqlCF);
    $stmt->execute($paramCaja);
    $cfRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    $cajaFuerte = $cfRow ? floatval($cfRow['total_cf']) : 0;
    $cantidadCajaFuerte = $cfRow ? intval($cfRow['cantidad_cf']) : 0;

    // Componer array de resultados
    $datos['efectivo_total']    = $pagos['efectivo']['total'];
    $datos['efectivo_cantidad'] = $pagos['efectivo']['cantidad'];
    $datos['mercadopago']       = $pagos['mercadopago']['total'];
    $datos['mercadopago_cantidad'] = $pagos['mercadopago']['cantidad'];
    $datos['tarjetas']          = $pagos['tarjeta']['total'];
    $datos['tarjetas_cantidad'] = $pagos['tarjeta']['cantidad'];
    $datos['qr']                = $pagos['qr']['total'];
    $datos['qr_cantidad']       = $pagos['qr']['cantidad'];
    $datos['inicio_caja']       = $inicioCaja;
    $datos['efectivo_ventas']   = $pagos['efectivo']['total'];
    $datos['caja_fuerte']       = $cajaFuerte;
    $datos['cantidad_caja_fuerte'] = $cantidadCajaFuerte;
    $datos['total_gastos']      = $totalGastos;
    $datos['cantidad_gastos']   = $cantidadGastos;

    // SALDO: inicio de caja + ventas en efectivo - caja fuerte - gastos
    $datos['saldo'] = floatval($inicioCaja)
                    + floatval($pagos['efectivo']['total'])
                    - floatval($cajaFuerte)
                    - floatval($totalGastos);

    return $datos;
}
public function obtenerCantidadCajaFuerte($fecha = null)
{
    $fechaHoy = $fecha ?: date('Y-m-d');
    $sql = "SELECT COUNT(*) as cantidad FROM caja_fuerte WHERE DATE(fecha) = :fechaHoy";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['fechaHoy' => $fechaHoy]);
    $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $fila ? intval($fila['cantidad']) : 0;
}
   public function obtenerCajaFuerteDelDia($fecha = null)
{
    $fechaHoy = $fecha ?: date('Y-m-d');
    $sql = "SELECT COALESCE(SUM(monto), 0) as total FROM caja_fuerte WHERE DATE(fecha) = :fechaHoy";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['fechaHoy' => $fechaHoy]);
    $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $fila ? floatval($fila['total']) : 0.0;
}
public function obtenerUltimoCierre()
{
    // Busca el último saldo_cierre de una caja cerrada
    $sql = "SELECT saldo_cierre FROM cajas WHERE saldo_cierre IS NOT NULL AND fecha_cierre IS NOT NULL ORDER BY fecha_cierre DESC LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row && $row['saldo_cierre'] !== null ? floatval($row['saldo_cierre']) : 0.0;
}
public function obtenerGastosDelDia($cajaId = null)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!$cajaId) {
        $cajaId = $_SESSION['caja_id'] ?? null;
    }
    if ($cajaId) {
        $sql = "SELECT COALESCE(SUM(monto),0) as total, COUNT(*) as cantidad FROM gastos WHERE caja_id = :cajaId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cajaId' => $cajaId]);
        $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'total' => isset($fila['total']) ? floatval($fila['total']) : 0.0,
            'cantidad' => isset($fila['cantidad']) ? intval($fila['cantidad']) : 0
        ];
    } else {
        // Compatibilidad: Suma por fecha si no hay cajaId
        $fechaHoy = date('Y-m-d');
        $sql = "SELECT COALESCE(SUM(monto),0) as total, COUNT(*) as cantidad FROM gastos WHERE DATE(fecha) = :fechaHoy";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fechaHoy' => $fechaHoy]);
        $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'total' => isset($fila['total']) ? floatval($fila['total']) : 0.0,
            'cantidad' => isset($fila['cantidad']) ? intval($fila['cantidad']) : 0
        ];
    }
}

    
    // Resumen por producto
   public function resumenPorProducto($fecha = null)
{
    // Buscar caja_id de la sesión o del día
    if (session_status() === PHP_SESSION_NONE) session_start();
    $cajaId = isset($_SESSION['caja_id']) ? $_SESSION['caja_id'] : null;
    if (!$cajaId) {
        $fechaHoy = $fecha ?: date('Y-m-d');
        $stmt = $this->db->prepare("SELECT id FROM cajas WHERE DATE(fecha_apertura) = :fechaHoy ORDER BY fecha_apertura DESC LIMIT 1");
        $stmt->execute(['fechaHoy' => $fechaHoy]);
        $cajaId = $stmt->fetchColumn();
    }

    $where = $cajaId ? "p.caja_id = :cajaId AND p.cerrado = 1" : "DATE(p.fecha) = :fechaHoy AND p.cerrado = 1";
    $param = $cajaId ? ['cajaId' => $cajaId] : ['fechaHoy' => ($fecha ?: date('Y-m-d'))];

    $sql = "SELECT pr.nombre, SUM(pd.cantidad) as cantidad, SUM(pd.cantidad * pr.precio) as total
            FROM pedido_detalle pd
            JOIN pedidos p ON pd.pedido_id = p.id
            JOIN productos pr ON pd.producto_id = pr.id
            WHERE $where
            GROUP BY pr.nombre
            ORDER BY total DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($param);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function cerrarCajaDelDia($usuarioId = null, $fecha = null, $saldoCierre = 0.0) {
    $fechaHoy = $fecha ?: date('Y-m-d');
    $sql = "UPDATE cajas 
            SET fecha_cierre = NOW(), saldo_cierre = :saldoCierre, usuario_cierre = :usuario
            WHERE DATE(fecha_apertura) = :fechaHoy AND fecha_cierre IS NULL
            ORDER BY fecha_apertura DESC LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'saldoCierre' => $saldoCierre,
        'usuario' => $usuarioId ?? null,
        'fechaHoy' => $fechaHoy
    ]);
    return $stmt->rowCount() > 0;
}
public function obtenerCajaIdActual($fecha = null)
{
    $fechaHoy = $fecha ?: date('Y-m-d');
    $sql = "SELECT id FROM cajas WHERE DATE(fecha_apertura) = :fechaHoy AND fecha_cierre IS NULL ORDER BY fecha_apertura DESC LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['fechaHoy' => $fechaHoy]);
    return $stmt->fetchColumn() ?: null;
}
public function obtenerCajaIdDelDia($fecha = null)
{
    $fechaHoy = $fecha ?: date('Y-m-d');
    $sql = "SELECT id FROM cajas WHERE DATE(fecha_apertura) = :fechaHoy ORDER BY fecha_apertura DESC LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['fechaHoy' => $fechaHoy]);
    $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $fila ? intval($fila['id']) : null;
}

}
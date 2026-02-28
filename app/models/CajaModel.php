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
    // Totales del día agrupados por método de pago, con soporte correcto para pagos divididos
    public function obtenerTotalesDelDia($fecha = null)
{
    $fechaHoy = $fecha ?: date('Y-m-d');

    // 1. Buscar caja actual (id) por sesión o por fecha
    if (session_status() === PHP_SESSION_NONE) session_start();
    $cajaId = isset($_SESSION['caja_id']) ? $_SESSION['caja_id'] : null;
    if (!$cajaId) {
        $stmt = $this->db->prepare("SELECT id FROM cajas WHERE fecha_cierre IS NULL ORDER BY fecha_apertura DESC LIMIT 1");
        $stmt->execute();
        $cajaId = $stmt->fetchColumn();
        if (!$cajaId) {
            $stmt = $this->db->prepare("SELECT id FROM cajas WHERE DATE(fecha_apertura) = :fechaHoy ORDER BY fecha_apertura DESC LIMIT 1");
            $stmt->execute(['fechaHoy' => $fechaHoy]);
            $cajaId = $stmt->fetchColumn();
        }
    }

    // 2. Filtro base: siempre por caja si tenemos cajaId
    $wherePedidos = $cajaId ? "p.caja_id = :cajaId AND p.cerrado = 1" : "DATE(p.fecha) = :fechaHoy AND p.cerrado = 1";
    $param        = $cajaId ? ['cajaId' => $cajaId] : ['fechaHoy' => $fechaHoy];

    // 3. Total general (todos los pedidos cerrados de esta caja)
    $sqlTotal = "SELECT COALESCE(SUM(total), 0) as venta_bruta, COUNT(*) as cantidad_pedidos
                 FROM pedidos p
                 WHERE $wherePedidos";
    $stmt = $this->db->prepare($sqlTotal);
    $stmt->execute($param);
    $datos = $stmt->fetch(\PDO::FETCH_ASSOC);

    // 4. Sumar por método de pago usando pedido_pagos (cubre divididos + simples)
    //    Si el pedido tiene registros en pedido_pagos, usamos esos montos.
    //    Si no (pedidos viejos), usamos el total del pedido con su metodo_pago.
    $pagos = [
        'efectivo'    => 0.0,
        'tarjeta'     => 0.0,
        'mercadopago' => 0.0,
        'qr'          => 0.0,
    ];
    $cantidades = [
        'efectivo'    => 0,
        'tarjeta'     => 0,
        'mercadopago' => 0,
        'qr'          => 0,
    ];

    // Pedidos con pagos detallados en pedido_pagos
    $sqlPP = "SELECT mp.clave, COALESCE(SUM(pp.monto), 0) as total, COUNT(DISTINCT p.id) as cantidad
              FROM pedidos p
              JOIN pedido_pagos pp ON pp.pedido_id = p.id
              JOIN metodos_pago mp ON mp.id = pp.metodo_pago_id
              WHERE $wherePedidos
              GROUP BY mp.clave";
    $stmtPP = $this->db->prepare($sqlPP);
    $stmtPP->execute($param);
    $conDetalle = $stmtPP->fetchAll(\PDO::FETCH_ASSOC);

    // IDs de pedidos que ya tienen pedido_pagos
    $sqlIds = "SELECT DISTINCT p.id FROM pedidos p JOIN pedido_pagos pp ON pp.pedido_id = p.id WHERE $wherePedidos";
    $stmtIds = $this->db->prepare($sqlIds);
    $stmtIds->execute($param);
    $idsConPagos = $stmtIds->fetchAll(\PDO::FETCH_COLUMN, 0);

    foreach ($conDetalle as $row) {
        $clave = strtolower(trim($row['clave'] ?? ''));
        if (isset($pagos[$clave])) {
            $pagos[$clave]    += floatval($row['total']);
            $cantidades[$clave] += intval($row['cantidad']);
        }
    }

    // Pedidos SIN pedido_pagos (viejos, pago simple)
    if (!empty($idsConPagos)) {
        $placeholders = implode(',', array_fill(0, count($idsConPagos), '?'));
        $whereViejo = $cajaId
            ? "caja_id = ? AND cerrado = 1 AND id NOT IN ($placeholders)"
            : "DATE(fecha) = ? AND cerrado = 1 AND id NOT IN ($placeholders)";
        $paramsViejo = $cajaId
            ? array_merge([$cajaId], $idsConPagos)
            : array_merge([$fechaHoy], $idsConPagos);
    } else {
        $whereViejo  = $cajaId ? "caja_id = ? AND cerrado = 1" : "DATE(fecha) = ? AND cerrado = 1";
        $paramsViejo = $cajaId ? [$cajaId] : [$fechaHoy];
    }

    $sqlViejo = "SELECT metodo_pago, SUM(total) as total, COUNT(*) as cantidad
                 FROM pedidos
                 WHERE $whereViejo
                 GROUP BY metodo_pago";
    $stmtV = $this->db->prepare($sqlViejo);
    $stmtV->execute($paramsViejo);
    foreach ($stmtV->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $metodo = strtolower(trim($row['metodo_pago'] ?? ''));
        if (isset($pagos[$metodo])) {
            $pagos[$metodo]     += floatval($row['total']);
            $cantidades[$metodo] += intval($row['cantidad']);
        }
    }

    // Obtener inicio de caja
    $inicioCaja = $cajaId
        ? $this->obtenerSaldoInicialPorCajaId($cajaId)
        : $this->obtenerSaldoInicial($fechaHoy);

    // SUMA gastos y caja fuerte SOLO DE ESTA CAJA
    $paramCaja = $cajaId ? ['cajaId' => $cajaId] : ['fechaHoy' => $fechaHoy];
    $whereCaja = $cajaId ? "caja_id = :cajaId" : "DATE(fecha) = :fechaHoy";

    // Gastos (detalle individual)
    $sqlGastos = "SELECT id, monto, motivo, autorizado_por, fecha,
                         COALESCE((SELECT nombre FROM categorias_gasto cg WHERE cg.id = gastos.categoria_id), '') as categoria
                  FROM gastos WHERE $whereCaja ORDER BY fecha ASC";
    $stmt = $this->db->prepare($sqlGastos);
    $stmt->execute($paramCaja);
    $gastosDetalle   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $totalGastos     = array_sum(array_column($gastosDetalle, 'monto'));
    $cantidadGastos  = count($gastosDetalle);

    // Ingresos de caja (detalle individual)
    $sqlIngresos = "SELECT id, monto, motivo, responsable, fecha
                    FROM ingresos_caja WHERE $whereCaja ORDER BY fecha ASC";
    $stmt = $this->db->prepare($sqlIngresos);
    $stmt->execute($paramCaja);
    $ingresosDetalle   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $totalIngresos     = array_sum(array_column($ingresosDetalle, 'monto'));

    // Caja fuerte (detalle individual)
    $sqlCF = "SELECT id, monto, responsable, fecha FROM caja_fuerte WHERE $whereCaja ORDER BY fecha ASC";
    $stmt = $this->db->prepare($sqlCF);
    $stmt->execute($paramCaja);
    $cajaFuerteDetalle  = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $cajaFuerte         = array_sum(array_column($cajaFuerteDetalle, 'monto'));
    $cantidadCajaFuerte = count($cajaFuerteDetalle);

    // Componer array de resultados
    $datos['efectivo_total']       = $pagos['efectivo'];
    $datos['efectivo_cantidad']    = $cantidades['efectivo'];
    $datos['mercadopago']          = $pagos['mercadopago'];
    $datos['mercadopago_cantidad'] = $cantidades['mercadopago'];
    $datos['tarjetas']             = $pagos['tarjeta'];
    $datos['tarjetas_cantidad']    = $cantidades['tarjeta'];
    $datos['qr']                   = $pagos['qr'];
    $datos['qr_cantidad']          = $cantidades['qr'];
    $datos['inicio_caja']          = $inicioCaja;
    $datos['efectivo_ventas']      = $pagos['efectivo'];
    $datos['caja_fuerte']          = $cajaFuerte;
    $datos['cantidad_caja_fuerte'] = $cantidadCajaFuerte;
    $datos['caja_fuerte_detalle']  = $cajaFuerteDetalle;
    $datos['total_gastos']         = $totalGastos;
    $datos['cantidad_gastos']      = $cantidadGastos;
    $datos['gastos_detalle']       = $gastosDetalle;
    $datos['ingresos_detalle']     = $ingresosDetalle;
    $datos['total_ingresos']       = $totalIngresos;
    $datos['caja_id']              = $cajaId;

    // SALDO: inicio + ingresos + efectivo ventas - caja fuerte - gastos
    $datos['saldo'] = floatval($inicioCaja)
                    + floatval($totalIngresos)
                    + floatval($pagos['efectivo'])
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
<?php
namespace app\models;

class PedidoModel
{
    private \PDO $db;
    private array $colsCache = [];

    public function __construct()
    {
        $this->db = \DataBase::getInstance()->getConnection();
    }

    // ==========================
    // Vista cajero (lista del día)
    // ==========================
    public function obtenerPedidosDelDiaConDetalle(): array
    {
        $tPedidos = 'pedidos';
        $tMesas   = $this->tableExists('mesas') ? 'mesas' : null;

        if (!$this->tableExists($tPedidos)) return [];

        $colsPedidos = $this->cols($tPedidos);

        $colId        = $this->pickCol($colsPedidos, ['id']);
        $colMesaId    = $this->pickCol($colsPedidos, ['mesa_id', 'id_mesa']);
        $colTotal     = $this->pickCol($colsPedidos, ['total']);
        $colMetodo    = $this->pickCol($colsPedidos, ['metodo_pago', 'medio_pago', 'metodo']);
        $colCerrado   = $this->pickCol($colsPedidos, ['cerrado']);
        $colFecha     = $this->pickCol($colsPedidos, ['fecha', 'created_at']);
        $colFechaCier = $this->pickCol($colsPedidos, ['fecha_cierre', 'cerrado_en']);

        if (!$colId || !$colFecha) return [];

        $selectMesaNum = "'' AS mesa_numero";
        $joinMesa = "";
        if ($tMesas && $colMesaId) {
            $colsMesas = $this->cols($tMesas);
            $mId     = $this->pickCol($colsMesas, ['id']);
            $mNumero = $this->pickCol($colsMesas, ['numero', 'nro', 'num']);
            if ($mId && $mNumero) {
                $joinMesa = "LEFT JOIN `mesas` m ON m.`$mId` = p.`$colMesaId`";
                $selectMesaNum = "COALESCE(m.`$mNumero`, '') AS mesa_numero";
            }
        }

        $sql = "
            SELECT
                p.`$colId` AS id,
                " . ($colMesaId ? "p.`$colMesaId` AS mesa_id," : "NULL AS mesa_id,") . "
                $selectMesaNum,
                " . ($colTotal ? "p.`$colTotal` AS total," : "0 AS total,") . "
                " . ($colMetodo ? "p.`$colMetodo` AS metodo_pago," : "'' AS metodo_pago,") . "
                " . ($colCerrado ? "p.`$colCerrado` AS cerrado," : "0 AS cerrado,") . "
                p.`$colFecha` AS fecha,
                " . ($colFechaCier ? "p.`$colFechaCier` AS fecha_cierre" : "NULL AS fecha_cierre") . "
            FROM `pedidos` p
            $joinMesa
            WHERE DATE(p.`$colFecha`) = CURDATE()
            ORDER BY p.`$colId` DESC
        ";

        $pedidos = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        if (!$pedidos) return [];

        // Adjuntar detalle
        foreach ($pedidos as &$p) {
            $p['detalles'] = $this->obtenerDetalleItemsPorPedido((int)$p['id']);
        }
        unset($p);

        return $pedidos;
    }

    // ==========================
    // Pedidos activos por mesa
    // ==========================
    public function obtenerPedidosActivosPorMesa(int $mesaId): array
    {
        if ($mesaId <= 0) return [];
        if (!$this->tableExists('pedidos')) return [];

        $cols = $this->cols('pedidos');
        $colId      = $this->pickCol($cols, ['id']);
        $colMesaId  = $this->pickCol($cols, ['mesa_id', 'id_mesa']);
        $colCerrado = $this->pickCol($cols, ['cerrado']);
        $colFecha   = $this->pickCol($cols, ['fecha', 'created_at']);

        if (!$colId || !$colMesaId) return [];

        $where = "p.`$colMesaId` = :mesa_id";
        if ($colCerrado) $where .= " AND p.`$colCerrado` = 0";
        if ($colFecha)   $where .= " AND DATE(p.`$colFecha`) = CURDATE()";

        $sql = "SELECT p.`$colId` AS id FROM `pedidos` p WHERE $where ORDER BY p.`$colId` ASC";
        $st = $this->db->prepare($sql);
        $st->execute([':mesa_id' => $mesaId]);
        $ids = $st->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        $out = [];
        foreach ($ids as $id) {
            $out[] = [
                'id' => (int)$id,
                'detalles' => $this->obtenerDetalleItemsPorPedido((int)$id),
            ];
        }
        return $out;
    }

    // =========================================
    // Cuenta por pedido (lo que usa actionCuenta)
    // =========================================
    public function obtenerDetalleCuentaPorPedido(int $pedidoId): array
    {
        $base = ['mesa' => [], 'productos' => [], 'total' => 0];
        if ($pedidoId <= 0) return $base;
        if (!$this->tableExists('pedidos')) return $base;

        $colsP = $this->cols('pedidos');
        $colId      = $this->pickCol($colsP, ['id']);
        $colMesaId  = $this->pickCol($colsP, ['mesa_id', 'id_mesa']);
        $colTotal   = $this->pickCol($colsP, ['total']);
        $colFecha   = $this->pickCol($colsP, ['fecha', 'created_at']);

        if (!$colId) return $base;

        $sql = "
            SELECT
                p.`$colId` AS id,
                " . ($colMesaId ? "p.`$colMesaId` AS mesa_id," : "NULL AS mesa_id,") . "
                " . ($colTotal ? "p.`$colTotal` AS total," : "0 AS total,") . "
                " . ($colFecha ? "p.`$colFecha` AS fecha" : "NULL AS fecha") . "
            FROM `pedidos` p
            WHERE p.`$colId` = ?
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([$pedidoId]);
        $pedido = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$pedido) return $base;

        $mesa = [];
        if (!empty($pedido['mesa_id']) && $this->tableExists('mesas')) {
            $mesa = $this->obtenerMesaPorId((int)$pedido['mesa_id']);
        }

        $productos = $this->obtenerProductosCuentaPorPedido($pedidoId);
        $total = (float)($pedido['total'] ?? 0);
        if ($total <= 0) {
            $total = 0;
            foreach ($productos as $it) $total += (float)($it['subtotal'] ?? 0);
        }

        return [
            'mesa' => $mesa,
            'productos' => $productos,
            'total' => $total,
        ];
    }

    // =====================================
    // Cuenta por mesa (lo que usa actionCuenta)
    // =====================================
    public function obtenerDetalleCuentaPorMesa(int $mesaId): array
    {
        $base = ['mesa' => [], 'productos' => [], 'total' => 0];
        if ($mesaId <= 0) return $base;

        $mesa = $this->tableExists('mesas') ? $this->obtenerMesaPorId($mesaId) : [];

        // Pedidos activos de hoy para esa mesa
        $pedidos = $this->obtenerPedidosActivosPorMesa($mesaId);

        $productos = [];
        $total = 0.0;

        foreach ($pedidos as $p) {
            foreach (($p['detalles'] ?? []) as $d) {
                $subtotal = (float)($d['precio'] ?? 0) * (float)($d['cantidad'] ?? 0);
                $productos[] = [
                    'nombre'      => (string)($d['nombre'] ?? ''),
                    'descripcion' => (string)($d['descripcion'] ?? ''),
                    'precio'      => (float)($d['precio'] ?? 0),
                    'cantidad'    => (float)($d['cantidad'] ?? 0),
                    'subtotal'    => $subtotal,
                ];
                $total += $subtotal;
            }
        }

        return [
            'mesa' => $mesa,
            'productos' => $productos,
            'total' => $total,
        ];
    }

    // ==========================
    // Cerrar pedidos de hoy por mesa
    // ==========================
    public function cerrarPedidosDeHoyPorMesa(int $mesaId, string $medioPago): int
    {
        if ($mesaId <= 0) return 0;
        if (!$this->tableExists('pedidos')) return 0;

        $cols = $this->cols('pedidos');
        $colMesaId    = $this->pickCol($cols, ['mesa_id', 'id_mesa']);
        $colCerrado   = $this->pickCol($cols, ['cerrado']);
        $colMetodo    = $this->pickCol($cols, ['metodo_pago', 'medio_pago', 'metodo']);
        $colFecha     = $this->pickCol($cols, ['fecha', 'created_at']);
        $colFechaCier = $this->pickCol($cols, ['fecha_cierre', 'cerrado_en']);

        if (!$colMesaId) return 0;

        $set = [];
        $params = [':mesa_id' => $mesaId];

        if ($colCerrado) { $set[] = "`$colCerrado` = 1"; }
        if ($colMetodo)  { $set[] = "`$colMetodo` = :metodo"; $params[':metodo'] = $medioPago; }
        if ($colFechaCier) { $set[] = "`$colFechaCier` = NOW()"; }

        if (!$set) return 0;

        $where = "`$colMesaId` = :mesa_id";
        if ($colCerrado) $where .= " AND `$colCerrado` = 0";
        if ($colFecha)   $where .= " AND DATE(`$colFecha`) = CURDATE()";

        $sql = "UPDATE `pedidos` SET " . implode(', ', $set) . " WHERE $where";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    // ======================================================
    // Helpers internos para detalle y mesa
    // ======================================================
    private function obtenerMesaPorId(int $mesaId): array
    {
        if ($mesaId <= 0) return [];
        if (!$this->tableExists('mesas')) return [];

        $cols = $this->cols('mesas');
        $colId     = $this->pickCol($cols, ['id']);
        $colNumero = $this->pickCol($cols, ['numero', 'nro', 'num']);
        $colEstado = $this->pickCol($cols, ['estado']);

        if (!$colId) return [];

        $sql = "
            SELECT
                `$colId` AS id,
                " . ($colNumero ? "`$colNumero` AS numero," : "'' AS numero,") . "
                " . ($colEstado ? "`$colEstado` AS estado" : "'' AS estado") . "
            FROM `mesas`
            WHERE `$colId` = ?
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([$mesaId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Detalle “simple” para usar en cuentaMesa y cuenta:
     * devuelve items con: nombre, descripcion, precio, cantidad
     */
    private function obtenerDetalleItemsPorPedido(int $pedidoId): array
    {
        if ($pedidoId <= 0) return [];
        if (!$this->tableExists('pedido_detalle')) return [];
        if (!$this->tableExists('productos')) return [];

        $dCols = $this->cols('pedido_detalle');
        $pCols = $this->cols('productos');

        $dPedidoId   = $this->pickCol($dCols, ['pedido_id', 'id_pedido']);
        $dProdId     = $this->pickCol($dCols, ['producto_id', 'id_producto']);
        $dCantidad   = $this->pickCol($dCols, ['cantidad', 'qty', 'cant']);
        $dPrecioUnit = $this->pickCol($dCols, ['precio_unitario', 'precio', 'importe_unitario']);

        $pId    = $this->pickCol($pCols, ['id', 'producto_id', 'id_producto']);
        $pNom   = $this->pickCol($pCols, ['nombre', 'descripcion', 'titulo']); // nombre preferido
        $pDesc  = $this->pickCol($pCols, ['descripcion', 'detalle']);

        if (!$dPedidoId || !$dProdId || !$pId || !$pNom) return [];

        $selDesc = $pDesc ? "COALESCE(pr.`$pDesc`, '') AS descripcion" : "'' AS descripcion";
        $selCant = $dCantidad ? "d.`$dCantidad` AS cantidad" : "1 AS cantidad";
        $selPrec = $dPrecioUnit ? "d.`$dPrecioUnit` AS precio" : "0 AS precio";

        $sql = "
            SELECT
                $selCant,
                $selPrec,
                pr.`$pNom` AS nombre,
                $selDesc
            FROM `pedido_detalle` d
            JOIN `productos` pr ON pr.`$pId` = d.`$dProdId`
            WHERE d.`$dPedidoId` = ?
            ORDER BY d.id ASC
        ";
        $st = $this->db->prepare($sql);
        $st->execute([$pedidoId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Detalle “completo” para el view cajero/cuenta:
     * nombre, descripcion, precio, cantidad, subtotal
     */
    private function obtenerProductosCuentaPorPedido(int $pedidoId): array
    {
        $items = $this->obtenerDetalleItemsPorPedido($pedidoId);
        $out = [];
        foreach ($items as $it) {
            $cantidad = (float)($it['cantidad'] ?? 0);
            $precio   = (float)($it['precio'] ?? 0);
            $out[] = [
                'nombre'      => (string)($it['nombre'] ?? ''),
                'descripcion' => (string)($it['descripcion'] ?? ''),
                'precio'      => $precio,
                'cantidad'    => $cantidad,
                'subtotal'    => $precio * $cantidad,
            ];
        }
        return $out;
    }

    // ======================================================
    // Utilidades DB (tablas / columnas)
    // ======================================================
    private function tableExists(string $table): bool
    {
        $st = $this->db->prepare("SHOW TABLES LIKE ?");
        $st->execute([$table]);
        return (bool)$st->fetchColumn();
    }

    private function cols(string $table): array
    {
        if (isset($this->colsCache[$table])) return $this->colsCache[$table];
        $st = $this->db->query("DESCRIBE `$table`");
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->colsCache[$table] = array_map(fn($r) => $r['Field'], $rows);
        return $this->colsCache[$table];
    }

    private function pickCol(array $cols, array $candidates): ?string
    {
        $lower = array_map('strtolower', $cols);
        foreach ($candidates as $c) {
            $i = array_search(strtolower($c), $lower, true);
            if ($i !== false) return $cols[$i];
        }
        return null;
    }
}
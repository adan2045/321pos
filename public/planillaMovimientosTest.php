<?php
// ------ SIMULACIÓN DE DATOS ------
// Para probar el diseño antes de la lógica real, podés usar este array:

$fechaSeleccionada = date('Y-m-d');
$inicioCaja = 15000;

$movimientos = [
    [
        'tipo' => 'inicio',
        'detalle' => "INICIO DE CAJA $fechaSeleccionada 08:00",
        'efectivo' => 15000,
        'tarjeta' => '',
        'qr' => '',
        'mp' => '',
        'total' => 15000,
        'mesa' => '',
        'fecha_hora' => "$fechaSeleccionada 08:00",
        'clase_efectivo' => 'entrada',
        'clase_tarjeta' => '',
        'clase_qr' => '',
        'clase_mp' => '',
        'clase_total' => 'entrada',
        'ticket_url' => ''
    ],
    [
        'tipo' => 'venta',
        'detalle' => 'TICKET 0012',
        'efectivo' => 2400,
        'tarjeta' => '',
        'qr' => '',
        'mp' => '',
        'total' => 2400,
        'mesa' => '5',
        'fecha_hora' => "$fechaSeleccionada 09:23",
        'clase_efectivo' => 'venta',
        'clase_tarjeta' => '',
        'clase_qr' => '',
        'clase_mp' => '',
        'clase_total' => 'venta',
        'ticket_url' => 'ticket.php?id=12'
    ],
    [
        'tipo' => 'venta',
        'detalle' => 'TICKET 0013',
        'efectivo' => '',
        'tarjeta' => 3100,
        'qr' => '',
        'mp' => '',
        'total' => 3100,
        'mesa' => '8',
        'fecha_hora' => "$fechaSeleccionada 10:15",
        'clase_efectivo' => '',
        'clase_tarjeta' => 'venta',
        'clase_qr' => '',
        'clase_mp' => '',
        'clase_total' => 'venta',
        'ticket_url' => 'ticket.php?id=13'
    ],
    [
        'tipo' => 'venta',
        'detalle' => 'TICKET 0014',
        'efectivo' => '',
        'tarjeta' => '',
        'qr' => 1100,
        'mp' => 700,
        'total' => 1800,
        'mesa' => '1',
        'fecha_hora' => "$fechaSeleccionada 11:00",
        'clase_efectivo' => '',
        'clase_tarjeta' => '',
        'clase_qr' => 'venta',
        'clase_mp' => 'venta',
        'clase_total' => 'venta',
        'ticket_url' => 'ticket.php?id=14'
    ],
    [
        'tipo' => 'caja_fuerte',
        'detalle' => 'Depósito Caja Fuerte',
        'efectivo' => 3000,
        'tarjeta' => '',
        'qr' => '',
        'mp' => '',
        'total' => 3000,
        'mesa' => '',
        'fecha_hora' => "$fechaSeleccionada 12:25",
        'clase_efectivo' => 'entrada',
        'clase_tarjeta' => '',
        'clase_qr' => '',
        'clase_mp' => '',
        'clase_total' => 'entrada',
        'ticket_url' => ''
    ],
    [
        'tipo' => 'gasto',
        'detalle' => 'GASTO: Compra Queso (aut: Juan)',
        'efectivo' => -1800,
        'tarjeta' => '',
        'qr' => '',
        'mp' => '',
        'total' => -1800,
        'mesa' => '',
        'fecha_hora' => "$fechaSeleccionada 13:30",
        'clase_efectivo' => 'egreso',
        'clase_tarjeta' => '',
        'clase_qr' => '',
        'clase_mp' => '',
        'clase_total' => 'egreso',
        'ticket_url' => ''
    ],
    [
        'tipo' => 'adelanto',
        'detalle' => 'ADELANTO: Mozo Pedro',
        'efectivo' => -2000,
        'tarjeta' => '',
        'qr' => '',
        'mp' => '',
        'total' => -2000,
        'mesa' => '',
        'fecha_hora' => "$fechaSeleccionada 14:00",
        'clase_efectivo' => 'egreso',
        'clase_tarjeta' => '',
        'clase_qr' => '',
        'clase_mp' => '',
        'clase_total' => 'egreso',
        'ticket_url' => ''
    ],
];

// Calcular totales
$totales = [
    'efectivo' => 0, 'tarjeta' => 0, 'qr' => 0, 'mp' => 0, 'total' => 0
];
foreach($movimientos as $mov) {
    $totales['efectivo'] += floatval($mov['efectivo'] ?: 0);
    $totales['tarjeta'] += floatval($mov['tarjeta'] ?: 0);
    $totales['qr']      += floatval($mov['qr'] ?: 0);
    $totales['mp']      += floatval($mov['mp'] ?: 0);
    $totales['total']   += floatval($mov['total'] ?: 0);
}
?>

<!-- Encabezado -->
<div class="planilla-encabezado">
    <form method="get" action="">
        <label>Fecha: </label>
        <input type="date" name="fecha" value="<?= htmlspecialchars($fechaSeleccionada) ?>">
        <button type="submit" class="btn btn-negro">Mostrar</button>
    </form>
    <div class="inicio-caja">
        <b>Inicio de caja:</b> $<?= number_format($inicioCaja,2,',','.') ?>
    </div>
</div>

<!-- Tabla de movimientos -->
<table class="planilla-movimientos">
    <thead>
        <tr>
            <th>Detalle</th>
            <th>Efectivo</th>
            <th>Tarjetas</th>
            <th>QR</th>
            <th>MercadoPago</th>
            <th>Total</th>
            <th>Mesa</th>
            <th>Fecha/Hora</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($movimientos as $mov): ?>
            <tr>
                <td>
                    <?php if($mov['tipo'] === 'venta' && !empty($mov['ticket_url'])): ?>
                        <a href="<?= htmlspecialchars($mov['ticket_url']) ?>" target="_blank">
                            <?= htmlspecialchars($mov['detalle']) ?>
                        </a>
                    <?php else: ?>
                        <?= htmlspecialchars($mov['detalle']) ?>
                    <?php endif; ?>
                </td>
                <td class="<?= $mov['clase_efectivo'] ?>">
                    <?= $mov['efectivo'] !== '' ? number_format($mov['efectivo'],2,',','.') : '' ?>
                </td>
                <td class="<?= $mov['clase_tarjeta'] ?>">
                    <?= $mov['tarjeta'] !== '' ? number_format($mov['tarjeta'],2,',','.') : '' ?>
                </td>
                <td class="<?= $mov['clase_qr'] ?>">
                    <?= $mov['qr'] !== '' ? number_format($mov['qr'],2,',','.') : '' ?>
                </td>
                <td class="<?= $mov['clase_mp'] ?>">
                    <?= $mov['mp'] !== '' ? number_format($mov['mp'],2,',','.') : '' ?>
                </td>
                <td class="<?= $mov['clase_total'] ?>">
                    <?= $mov['total'] !== '' ? number_format($mov['total'],2,',','.') : '' ?>
                </td>
                <td><?= $mov['mesa'] ?></td>
                <td><?= $mov['fecha_hora'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <!-- Pie de totales -->
    <tfoot>
        <tr>
            <td><b>Totales</b></td>
            <td><b><?= number_format($totales['efectivo'],2,',','.') ?></b></td>
            <td><b><?= number_format($totales['tarjeta'],2,',','.') ?></b></td>
            <td><b><?= number_format($totales['qr'],2,',','.') ?></b></td>
            <td><b><?= number_format($totales['mp'],2,',','.') ?></b></td>
            <td><b><?= number_format($totales['total'],2,',','.') ?></b></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<div class="planilla-botones">
    <button onclick="window.print()" class="btn btn-negro">Imprimir</button>
    <button onclick="window.close()" class="btn btn-rojo">Cerrar</button>
</div>

<style>
.planilla-encabezado { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;}
.planilla-encabezado form { display: flex; gap: 8px; }
.planilla-movimientos { width:100%; border-collapse:collapse; margin-bottom:10px; box-shadow:0 2px 6px rgba(0,0,0,0.04);}
.planilla-movimientos th, .planilla-movimientos td { border:1px solid #222; padding:6px 9px; font-size:15px; text-align:center;}
.planilla-movimientos th { background:#f6f6f6;}
.planilla-movimientos td.entrada { color:green; font-weight:bold; }
.planilla-movimientos td.egreso { color:#c00; font-weight:bold; }
.planilla-movimientos td.venta { color:#222; font-weight:500;}
.planilla-movimientos tr:hover td { background: #f5f9fa; }
.btn { padding: 7px 22px; border:none; border-radius:6px; background:#222; color:#fff; cursor:pointer; margin-right:10px; font-size:15px;}
.btn-rojo { background: #c00; color: #fff; }
.btn-negro { background: #222; color: #fff; }
.inicio-caja { background:#fff8e1; border:1px solid #ddd; padding:7px 16px; border-radius:7px; font-size:16px;}
@media (max-width: 900px) {
    .planilla-movimientos th, .planilla-movimientos td { font-size:13px; padding:4px 5px;}
    .planilla-encabezado { flex-direction:column; align-items:flex-start;}
}
</style>
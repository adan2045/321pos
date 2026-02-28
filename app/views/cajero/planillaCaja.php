<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$modeloCaja   = new \app\models\CajaModel();
$cajaId       = $datos['caja_id'] ?? $modeloCaja->obtenerCajaIdDelDia();
$ultimoCierre = $modeloCaja->obtenerUltimoCierre();

$gastosDet     = $datos['gastos_detalle']      ?? [];
$cajaFuerteDet = $datos['caja_fuerte_detalle'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?= $head ?>
    <title>Planilla de Ventas</title>
    <link rel="stylesheet" href="/public/css/crud.css">
    <link rel="stylesheet" href="/public/css/listado.css">
    <style>
        html, body { max-width:100vw; overflow-x:hidden; margin:0; padding:0; }
        .planilla-container { width:100vw; min-height:100vh; background:#fff; font-family:Arial,sans-serif; }
        .planilla-table { width:100%; border-collapse:collapse; margin-top:1rem; }
        .planilla-table th, .planilla-table td { border:1px solid #ccc; padding:8px; font-size:0.98rem; }
        .fecha-selector {
            display:flex; align-items:center; gap:18px; font-size:1.7rem; font-weight:700;
            margin-top:18px; margin-bottom:12px; padding-left:35px;
        }
        .fecha-selector label { font-size:1.8rem; font-weight:700; color:#222; }
        .fecha-selector input[type="date"] { padding:10px 16px; border:1.5px solid #888; border-radius:7px; font-size:1.1rem; background:#f7f7f7; }
        .fecha-selector input[type="date"]:focus { outline:none; border:2px solid #222; }
        .fecha-selector button { background:#111; color:#fff; padding:10px 19px; border:none; border-radius:7px; font-size:1.05rem; font-weight:bold; cursor:pointer; }
        .fecha-selector button:hover { background:#353535; }
        .contenido-principal { display:flex; align-items:flex-start; width:100%; margin:0; }
        .planilla-contenido { flex:1 1 0; min-width:0; padding:2rem; }
        .planilla-botones-derecha {
            display:flex; flex-direction:column; gap:16px; align-items:flex-end;
            min-width:170px; max-width:200px; margin-top:2rem; padding-right:1.5rem;
        }
        .planilla-botones-derecha button {
            background:black; color:white; padding:0.9rem 0; border:none; border-radius:6px;
            cursor:pointer; width:170px; font-size:1rem; text-align:center; transition:background 0.15s;
        }
        .planilla-botones-derecha button:hover { background:#333; }
        .btn-rojo    { background:#c0392b !important; } .btn-rojo:hover    { background:#a93226 !important; }
        .btn-naranja { background:#d35400 !important; } .btn-naranja:hover { background:#b44200 !important; }
        .planilla-resumen { margin-top:1.2rem; }
        .planilla-resumen h4 { font-size:1.8rem !important; font-weight:700 !important; color:#111 !important; margin:0 !important; padding:0 !important; border:none !important; }
        /* Filas */
        .fila-gris td { font-weight:bold; background:#ddd; }
        /* Fila con toggle para desplegar detalle */
        .fila-toggle td:first-child { cursor:pointer; user-select:none; }
        .fila-toggle td:first-child:hover { text-decoration:underline; }
        .fila-toggle .toggle-icon { font-size:0.85rem; margin-left:6px; color:#666; }
        /* Sub-filas de detalle (ocultas por defecto) */
        .fila-sub { display:none; }
        .fila-sub td { background:#f5f5f5; color:#555; font-size:0.93rem; padding-left:24px !important; }
        .fila-sub td:first-child::before { content:"↳ "; color:#aaa; }
        /* Alertas */
        .alerta-ok  { background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin:12px 2rem; font-size:1.1rem; text-align:center; border:1px solid #c3e6cb; }
        .alerta-err { background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin:12px 2rem; font-size:1rem; border:1px solid #f5c6cb; }
        @media(max-width:900px) {
            .contenido-principal { flex-direction:column; }
            .planilla-botones-derecha { align-items:stretch; max-width:none; padding-right:2rem; }
            .planilla-botones-derecha button { width:100%; }
        }
    </style>
</head>
<body>
    <header><?= $nav ?></header>

    <?php if (isset($_GET['cerrada']) && $_GET['cerrada'] == 'ok'): ?>
        <div class="alerta-ok">&#9989; Caja cerrada con éxito.</div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['mensaje_exito'])): ?>
        <div class="alerta-ok">&#9989; <?= htmlspecialchars($_SESSION['mensaje_exito']) ?></div>
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['mensaje_error'])): ?>
        <div class="alerta-err">&#9888; <?= htmlspecialchars($_SESSION['mensaje_error']) ?></div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <main class="planilla-container">
        <div class="planilla-header">
            <div class="fecha-selector">
                <label for="fecha">Planilla del Día de la Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>">
                <button onclick="cambiarFecha()">Cambiar</button>
            </div>
        </div>

        <div class="contenido-principal">
            <div class="planilla-contenido">
                <table class="planilla-table">
                    <thead>
                        <tr><th>Detalle</th><th>Total $</th><th>Cantidad</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>FACTURAS B</td>
                            <td><?= number_format($datos['venta_bruta'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= $datos['cantidad_pedidos'] ?? 0 ?></td>
                        </tr>
                        <tr class="fila-gris"><td colspan="3">TOTAL VENTA BRUTA</td></tr>

                        <tr>
                            <td>EFECTIVO TOTAL</td>
                            <td><?= number_format($datos['efectivo_total'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= $datos['efectivo_cantidad'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>QR</td>
                            <td><?= number_format($datos['qr'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= $datos['qr_cantidad'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>MercadoPago</td>
                            <td><?= number_format($datos['mercadopago'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= $datos['mercadopago_cantidad'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>Tarjetas</td>
                            <td><?= number_format($datos['tarjetas'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= $datos['tarjetas_cantidad'] ?? 0 ?></td>
                        </tr>

                        <tr class="fila-gris"><td colspan="3">TOTAL INGRESO BRUTO</td></tr>

                        <tr>
                            <td>Inicio de Caja</td>
                            <td><?= number_format($datos['inicio_caja'] ?? 0, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Efectivo por Ventas</td>
                            <td><?= number_format($datos['efectivo_ventas'] ?? 0, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>

                        <!-- CAJA FUERTE: clic para desplegar -->
                        <?php if (!empty($cajaFuerteDet)): ?>
                        <tr class="fila-toggle" onclick="toggleDetalle('cf')">
                            <td style="color:red">Caja Fuerte <span class="toggle-icon" id="icon-cf">▶ ver detalle</span></td>
                            <td><?= number_format($datos['caja_fuerte'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= count($cajaFuerteDet) ?></td>
                        </tr>
                        <?php foreach ($cajaFuerteDet as $i => $cf): ?>
                        <tr class="fila-sub fila-cf">
                            <td><?= date('d/m H:i', strtotime($cf['fecha'])) ?> — <?= htmlspecialchars($cf['responsable']) ?></td>
                            <td><?= number_format($cf['monto'], 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td style="color:red">Caja Fuerte</td>
                            <td>0,00</td>
                            <td>0</td>
                        </tr>
                        <?php endif; ?>

                        <!-- GASTOS: clic para desplegar -->
                        <?php if (!empty($gastosDet)): ?>
                        <tr class="fila-toggle" onclick="toggleDetalle('gastos')">
                            <td style="color:#ff6600">Gastos <span class="toggle-icon" id="icon-gastos">▶ ver detalle</span></td>
                            <td><?= number_format($datos['total_gastos'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= count($gastosDet) ?></td>
                        </tr>
                        <?php foreach ($gastosDet as $g): ?>
                        <tr class="fila-sub fila-gastos">
                            <td>
                                <?= date('d/m H:i', strtotime($g['fecha'])) ?> —
                                <?= htmlspecialchars($g['motivo']) ?>
                                <?php if (!empty($g['categoria'])): ?>(<?= htmlspecialchars($g['categoria']) ?>)<?php endif; ?>
                                — aut: <?= htmlspecialchars($g['autorizado_por']) ?>
                            </td>
                            <td><?= number_format($g['monto'], 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td style="color:#ff6600">Gastos</td>
                            <td>0,00</td>
                            <td>0</td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <td style="font-weight:bold">SALDO DE CAJA</td>
                            <td style="font-weight:bold"><?= number_format($datos['saldo'] ?? 0, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <!-- RESUMEN POR PRODUCTO -->
                <div class="planilla-resumen">
                    <h4>Resumen por Producto</h4>
                    <table class="planilla-table">
                        <thead>
                            <tr><th>Producto</th><th>Total $</th><th>Cantidad</th><th>% Venta</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_venta = $datos['venta_bruta'] ?? 0;
                            foreach ($productos as $prod):
                                $pct = ($total_venta > 0) ? ($prod['total'] / $total_venta) * 100 : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                                <td><?= number_format($prod['total'], 2, ',', '.') ?></td>
                                <td><?= $prod['cantidad'] ?></td>
                                <td><?= number_format($pct, 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BOTONES DERECHA -->
            <div class="planilla-botones-derecha">
                <button type="button" onclick="cerrarTurno()" class="btn-naranja">Cerrar Turno</button>
                <button type="button" onclick="cerrarCaja()"  class="btn-rojo">Cerrar Caja</button>
                <button type="button" onclick="exportarPlanilla()">Exportar</button>
                <button type="button" onclick="window.print()">Imprimir</button>
            </div>
        </div>
    </main>

    <script>
        function toggleDetalle(grupo) {
            const filas = document.querySelectorAll('.fila-' + grupo);
            const icon  = document.getElementById('icon-' + grupo);
            const oculto = filas[0].style.display === 'none' || filas[0].style.display === '';
            filas.forEach(f => f.style.display = oculto ? 'table-row' : 'none');
            icon.textContent = oculto ? '▼ ocultar' : '▶ ver detalle';
        }
        function cambiarFecha() {
            const f = document.getElementById('fecha').value;
            if (f) window.location.href = '<?= $ruta ?>/cajero/planillaCaja?fecha=' + f;
        }
        function cerrarTurno() {
            if (confirm('¿Cerrar el turno actual? La caja seguirá abierta.'))
                window.location.href = '<?= $ruta ?>/cajero/cerrarTurno';
        }
        function cerrarCaja() {
            if (confirm('¿Cerrar la caja? No podrá operar hasta abrir una nueva.'))
                window.location.href = '<?= $ruta ?>/cajero/cerrarCaja';
        }
        function exportarPlanilla() {
            const f = document.getElementById('fecha').value || '<?= date('Y-m-d') ?>';
            window.location.href = '<?= $ruta ?>/cajero/exportarPlanilla?fecha=' + f;
        }
        window.addEventListener('message', e => {
            if (!e.data || e.data.type !== 'OPEN_MODAL') return;
            switch(e.data.modal) {
                case 'ABRIR_CAJA':  abrirCajaModal();      break;
                case 'EGRESO':      abrirGastoModal();      break;
                case 'CAJA_FUERTE': abrirCajaFuerteModal(); break;
            }
        });
    </script>
</body>
</html>
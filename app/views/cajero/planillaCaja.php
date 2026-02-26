<?php
$modeloCaja = new \app\models\CajaModel();
$cajaId = $modeloCaja->obtenerCajaIdDelDia();
$ultimoCierre = method_exists($modeloCaja, 'obtenerUltimoCierre') ? $modeloCaja->obtenerUltimoCierre() : 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?= $head ?>
    <title>Planilla de Ventas</title>
    <link rel="stylesheet" href="/public/css/crud.css">
    <link rel="stylesheet" href="/public/css/listado.css">
    <style>
        html, body {
            max-width: 100vw;
            overflow-x: hidden;
        }
        body {
            margin: 0;
            padding: 0;
        }
        .planilla-container {
            width: 100vw;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background-color: #fff;
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.07);
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }
        .planilla-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            overflow-x: auto;
        }
        .planilla-table th, .planilla-table td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 0.98rem;
        }
        .vistaCajero-toggle {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .vistaCajero-toggle a {
            flex: 1;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            font-size: 1rem;
            border: none;
            background-color: #eee;
            color: #000;
            transition: background 0.15s;
        }
        .vistaCajero-toggle .active {
            background-color: black;
            color: white;
        }
        .planilla-header {
            display: block;
            width: 100%;
        }
        .fecha-selector {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 1.7rem;
            font-weight: 700;
            margin-top: 18px;
            margin-bottom: 12px;
            justify-content: flex-start;
            width: 100%;
        }
        .fecha-selector label {
            font-size: 1.8rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 2px;
            margin-right: 8px;
            letter-spacing: 0.01em;
            text-align: left;
            padding-left: 35px;
        }
        .fecha-selector input[type="date"] {
            padding: 10px 16px;
            border: 1.5px solid #888;
            border-radius: 7px;
            font-size: 1.1rem;
            background: #f7f7f7;
            transition: border .18s;
        }
        .fecha-selector input[type="date"]:focus {
            outline: none;
            border: 2px solid #222;
        }
        .fecha-selector button {
            background: #111;
            color: #fff;
            padding: 10px 19px;
            border: none;
            border-radius: 7px;
            font-size: 1.05rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 1px 5px rgba(0, 0, 0, .08);
            transition: background 0.15s, transform 0.09s;
        }
        .fecha-selector button:hover {
            background: #353535;
            transform: scale(1.05);
        }
        .planilla-botones-derecha {
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: flex-end;
            min-width: 170px;
            max-width: 200px;
            margin-left: 6px;
            margin-top: 2rem;
        }
        .planilla-botones-derecha button,
        .planilla-botones-derecha a button {
            background: black;
            color: white;
            padding: 0.9rem 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 170px;
            font-size: 1rem;
            text-align: center;
            margin: 0;
            display: block;
            transition: background 0.15s;
        }
        .planilla-botones-derecha button:hover,
        .planilla-botones-derecha a button:hover {
            background: #222;
        }
        .contenido-principal {
            display: flex;
            align-items: flex-start;
            gap: 0;
            width: 100%;
            margin: 0;
        }
        .planilla-contenido {
            flex: 1 1 0;
            min-width: 0;
            padding: 2rem 2rem 2rem 2rem;
        }
        .planilla-resumen {
            margin-top: 1.2rem;
        }
        .planilla-resumen h4 {
            font-size: 1.8rem !important;
            font-weight: 700 !important;
            color: #111 !important;
            font-family: Arial, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            padding-left: 0 !important;
            border: none !important;
            letter-spacing: 0.01em !important;
            text-align: left !important;
            line-height: 1.2 !important;
            display: flex !important;
            align-items: center !important;
        }
        .estado-btn {
            padding: 4px 10px;
            margin: 2px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #eee;
            color: #333;
            font-size: 0.9rem;
            cursor: pointer;
        }
        @media (max-width: 900px) {
            .contenido-principal {
                flex-direction: column;
            }
            .planilla-botones-derecha {
                align-items: stretch;
                max-width: none;
                margin-left: 0;
                margin-top: 1rem;
            }
        }
        /* MODAL GENERAL */
        .modal-fondo {
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-contenido {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 32px rgba(0, 0, 0, .13);
            max-width: 430px;
            width: 100%;
            padding: 1.2rem 2rem 2rem 2rem;
            animation: modalFadeIn .24s;
        }
        @keyframes modalFadeIn {
            from { transform: translateY(60px); opacity: 0; }
            to { transform: none; opacity: 1; }
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .7rem;
            border-bottom: 1px solid #eee;
            padding-bottom: .3rem;
        }
        @media (max-width: 600px) {
            .modal-contenido {
                padding: 0.7rem 0.7rem 1.4rem 0.7rem;
            }
        }
    </style>
</head>
<body>
    <header><?= $nav ?></header>

    
    <?php if (isset($_GET['cerrada']) && $_GET['cerrada'] == 'ok'): ?>
        <div style="background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin:16px 0; font-size:1.2rem; text-align:center; border:1px solid #c3e6cb;">
            &#9989; Caja cerrada con éxito.
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['mensaje_error'])): ?>
        <div class="alert-error"><?= $_SESSION['mensaje_error'] ?></div>
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
                        <tr>
                            <th>Detalle</th>
                            <th>Total</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>FACTURAS B</td>
                            <td><?= number_format($datos['venta_bruta'], 2) ?></td>
                            <td><?= $datos['cantidad_pedidos'] ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-weight: bold; background: #ddd">TOTAL VENTA BRUTA</td>
                        </tr>
                        <tr>
                            <td>EFECTIVO TOTAL</td>
                            <td><?= number_format($datos['efectivo_total'], 2) ?></td>
                            <td><?= $datos['efectivo_cantidad'] ?></td>
                        </tr>
                        <tr>
                            <td>Qr</td>
                            <td><?= number_format($datos['qr'], 2) ?></td>
                            <td><?= $datos['qr_cantidad'] ?></td>
                        </tr>
                        <tr>
                            <td>MercadoPago</td>
                            <td><?= number_format($datos['mercadopago'], 2) ?></td>
                            <td><?= $datos['mercadopago_cantidad'] ?></td>
                        </tr>
                        <tr>
                            <td>Tarjetas</td>
                            <td><?= number_format($datos['tarjetas'], 2) ?></td>
                            <td><?= $datos['tarjetas_cantidad'] ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-weight: bold; background: #ddd">TOTAL INGRESO BRUTO</td>
                        </tr>
                        <tr>
                            <td>Inicio de Caja</td>
                            <td><?= number_format($datos['inicio_caja'], 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Efectivo por Ventas</td>
                            <td><?= number_format($datos['efectivo_ventas'], 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="color:red">Caja Fuerte</td>
                            <td><?= number_format($datos['caja_fuerte'], 2) ?></td>
                            <td><?= $datos['cantidad_caja_fuerte'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td style="color:#ff6600">Gastos</td>
                            <td><?= number_format($datos['total_gastos'], 2) ?></td>
                            <td><?= $datos['cantidad_gastos'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">SALDO DE CAJA</td>
                            <td style="font-weight: bold;"><?= number_format($datos['saldo'], 2) ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <div class="planilla-resumen">
                    <h4> Resumen por Producto</h4>
                    <table class="planilla-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Total $</th>
                                <th>Cantidad</th>
                                <th>% Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_venta = $datos['venta_bruta'];
                            foreach ($productos as $prod):
                                $porcentaje = ($total_venta > 0) ? ($prod['total'] / $total_venta) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['nombre']) ?></td>
                                    <td><?= number_format($prod['total'], 2) ?></td>
                                    <td><?= $prod['cantidad'] ?></td>
                                    <td class="porcentaje"><?= number_format($porcentaje, 1) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
           <!-- <div class="planilla-botones-derecha">
                <button type="button" onclick="abrirCajaModal()">Abrir Caja</button>
                <button type="button" onclick="abrirGastoModal()">Gastos</button>
                <button type="button" onclick="abrirCajaFuerteModal()">Caja Fuerte</button>
                <button onclick="cerrarCaja()">Cerrar Caja</button>
                <button onclick="window.print()">Imprimir</button>
                <a href="<?= $ruta ?>/cajero/libroDiario"><button>Movimientos</button></a>
                <a href="<?= $ruta ?>/admin/gestion"><button>Menu Principal</button></a>
            </div>
        </div>-->
    </main>

    <!-- MODAL GASTOS -->
    <div id="gastoModal" class="modal-fondo" style="display: none;">
        <div class="modal-contenido">
            <div class="modal-header">
                <h2>Registrar Gasto</h2>
                <span onclick="cerrarGastoModal()" style="cursor:pointer;font-size:1.5rem;">&times;</span>
            </div>
            <form action="<?= $ruta ?>/cajero/registrarGasto" method="POST" class="registro-form" style="padding: 1rem 0 0 0; margin:0;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Motivo</label>
                        <input type="text" name="motivo" class="form-control" required placeholder="Ej: Pan, luz...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monto</label>
                        <input type="number" step="0.01" name="monto" class="form-control" required placeholder="Monto $">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Autorizó</label>
                        <input type="text" name="autorizado_por" class="form-control" required placeholder="Nombre o usuario">
                    </div>
                </div>
                <input type="hidden" name="caja_id" value="<?= htmlspecialchars($cajaId) ?>">
                <button type="submit" class="btn" style="margin-top:12px;">Registrar Gasto</button>
                <button type="button" class="btn" style="background:#aaa;margin-top:12px;" onclick="cerrarGastoModal()">Cancelar</button>
            </form>
        </div>
    </div>
    <!-- MODAL ABRIR CAJA -->
    <div id="abrirCajaModal" class="modal-fondo" style="display: none;">
        <div class="modal-contenido">
            <div class="modal-header">
                <h2>Abrir Caja</h2>
                <span onclick="cerrarCajaModal()" style="cursor:pointer;font-size:1.5rem;">&times;</span>
            </div>
            <form action="<?= $ruta ?>/cajero/abrirCaja" method="GET" class="registro-form" style="padding: 1rem 0 0 0; margin:0;">
                <div class="form-group full-width">
                    <label class="form-label">Monto Inicial</label>
                    <input 
                        type="number"
                        name="monto"
                        id="montoAbrirCaja"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($ultimoCierre) ?>"
                    >
                </div>
                <button type="submit" class="btn" style="margin-top:12px;">Abrir Caja</button>
                <button type="button" class="btn" style="background:#aaa;margin-top:12px;" onclick="cerrarCajaModal()">Cancelar</button>
            </form>
        </div>
    </div>
    <!-- MODAL CAJA FUERTE -->
    <div id="cajaFuerteModal" class="modal-fondo" style="display: none;">
        <div class="modal-contenido">
            <div class="modal-header">
                <h2>Caja Fuerte</h2>
                <span onclick="cerrarCajaFuerteModal()" style="cursor:pointer;font-size:1.5rem;">&times;</span>
            </div>
            <form action="<?= $ruta ?>/cajero/registrarCajaFuerte" method="POST" class="registro-form" style="padding: 1rem 0 0 0; margin:0;">
                <div class="form-group full-width">
                    <label class="form-label">Monto a pasar</label>
                    <input type="number" name="monto" class="form-control" required>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Motivo</label>
                    <input type="text" name="motivo" class="form-control" placeholder="Ej: retiro excedente / seguridad" required>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Responsable</label>
                    <input type="text" name="responsable" class="form-control" required>
                </div>
                <input type="hidden" name="caja_id" value="<?= htmlspecialchars($cajaId) ?>">
                <button type="submit" class="btn" style="margin-top:12px;">Registrar</button>
                <button type="button" class="btn" style="background:#aaa;margin-top:12px;" onclick="cerrarCajaFuerteModal()">Cancelar</button>
            </form>
        </div>
    </div>
    <script>
        function cambiarFecha() {
            const fecha = document.getElementById('fecha').value;
            if (fecha) {
                window.location.href = '<?= $ruta ?>/cajero/planillaCaja?fecha=' + fecha;
            }
        }
        function cerrarCaja() {
            if (confirm('¿Desea cerrar la caja?')) {
                window.location.href = '<?= $ruta ?>/cajero/cerrarCaja';
            }
        }
        // POPUP Gastos
        function abrirGastoModal() {
            document.getElementById('gastoModal').style.display = 'flex';
            setTimeout(() => { document.querySelector('#gastoModal input[name="motivo"]').focus(); }, 150);
        }
        function cerrarGastoModal() { document.getElementById('gastoModal').style.display = 'none'; }
        // POPUP Abrir Caja
        function abrirCajaModal() {
            document.getElementById('abrirCajaModal').style.display = 'flex';
            // Ponemos el valor en el input del modal SOLO cuando se abre
            <?php if ($ultimoCierre): ?>
                document.getElementById('montoAbrirCaja').value = <?= json_encode($ultimoCierre) ?>;
            <?php else: ?>
                document.getElementById('montoAbrirCaja').value = "0";
            <?php endif; ?>
            setTimeout(() => { document.getElementById('montoAbrirCaja').focus(); }, 150);
        }
        function cerrarCajaModal() { document.getElementById('abrirCajaModal').style.display = 'none'; }
        // POPUP Caja Fuerte
        function abrirCajaFuerteModal() {
            document.getElementById('cajaFuerteModal').style.display = 'flex';
            setTimeout(() => { document.querySelector('#cajaFuerteModal input[name="monto"]').focus(); }, 150);
        }
        function cerrarCajaFuerteModal() { document.getElementById('cajaFuerteModal').style.display = 'none'; }
        // ESC para cerrar cualquier modal
        window.addEventListener('keydown', function (e) {
            if (e.key === "Escape") {
                cerrarGastoModal();
                cerrarCajaModal();
                cerrarCajaFuerteModal();
            }
        })

    
        window.addEventListener('message', (e) => {
             if (!e.data || e.data.type !== 'OPEN_MODAL') return;

            switch (e.data.modal) {
              case 'ABRIR_CAJA':   abrirCajaModal(); break;
              case 'EGRESO':       abrirGastoModal(); break;
              case 'CAJA_FUERTE':  abrirCajaFuerteModal(); break;
  }
});
</script>

</body>
</html>
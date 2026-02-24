<!DOCTYPE html>
<html lang="es">

<head>
    <?= $head ?>
    <title><?= $title ?? 'Detalle de Cuenta' ?></title>
    <link rel="stylesheet" href="/public/css/crud.css">
    <style>
        body.cuenta-body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .cuenta-header {
            background-color: black;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cuenta-terminal-title {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .cuenta-main {
            padding: 2rem;
        }
        .cuenta-bill-container {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .cuenta-bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .cuenta-bill-table th,
        .cuenta-bill-table td {
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }
        .cuenta-bill-totals {
            text-align: right;
            margin-top: 1rem;
        }
        .cuenta-total-row {
            margin: 0.2rem 0;
        }
        .cuenta-final {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .cuenta-medio-pago-label {
            margin-bottom: 12px;
            display: block;
        }
        .medio-pago-btn {
            padding: 0.6rem 1rem;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            cursor: pointer;
            background-color: #eee;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .medio-pago-btn.activo {
            background-color: black;
            color: white;
        }
        .cerrar-venta-btn {
            background-color: #28a745;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .cuenta-cerrada-msg {
            margin-top:2rem;
            color:#555;
            font-weight:bold;
        }
    </style>
</head>

<body class="cuenta-body">
    <header class="cuenta-header">
        <div class="cuenta-terminal-title">
            Detalle de Cuenta -
            <?php if (!empty($mesa) && isset($mesa['numero'])): ?>
                Mesa <?= htmlspecialchars($mesa['numero']) ?>
            <?php else: ?>
                Mesa cerrada o no encontrada
            <?php endif; ?>
        </div>
        <div class="cuenta-user-info">Mozo: <?= $_SESSION['usuario']['nombre'] ?? 'Sin nombre' ?></div>
    </header>

    <main class="cuenta-main">
        <div class="cuenta-bill-container">
            <h2>
                <?php if (!empty($mesa) && isset($mesa['numero'])): ?>
                    Mesa <?= htmlspecialchars($mesa['numero']) ?> - Cuenta Final
                <?php else: ?>
                    Ticket cerrado / Mesa no encontrada
                <?php endif; ?>
            </h2>
            <table class="cuenta-bill-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><?= htmlspecialchars($prod['nombre'] ?? 'Producto desconocido') ?><br>
                                <small><?= htmlspecialchars($prod['descripcion'] ?? '') ?></small>
                            </td>
                            <td><?= $prod['cantidad'] ?? 0 ?></td>
                            <td>$<?= number_format($prod['precio'] ?? 0, 2, ',', '.') ?></td>
                            <td>
                                $<?= number_format(
                                    isset($prod['subtotal']) ? $prod['subtotal'] : ($prod['precio'] ?? 0) * ($prod['cantidad'] ?? 0),
                                    2, ',', '.'
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cuenta-bill-totals">
                <div class="cuenta-total-row">
                    TOTAL: <span>$<?= number_format($total, 2, ',', '.') ?></span>
                </div>
            </div>

            <?php
            // Lógica para mostrar botones según estado de la cuenta
            // $esCuentaCerrada llega del controlador
            $mostrarBotones = !empty($mesa) && isset($mesa['id']) && (empty($esCuentaCerrada) || !$esCuentaCerrada);
            $mostrarCambioMp = !empty($esCuentaCerrada) && $esCuentaCerrada && !empty($_GET['id']);
            ?>

            <?php if ($mostrarBotones): ?>
                <!-- Cuenta abierta: medios de pago + cerrar venta -->
                <div style="margin-top: 2rem;">
                    <label class="cuenta-medio-pago-label"><strong>Seleccione medio de pago:</strong></label>
                    <div style="margin-top: 1rem;">
                        <button class="medio-pago-btn" data-medio="efectivo" onclick="seleccionarMedio('efectivo')">Efectivo</button>
                        <button class="medio-pago-btn" data-medio="tarjeta" onclick="seleccionarMedio('tarjeta')">Tarjeta</button>
                        <button class="medio-pago-btn" data-medio="mercadopago" onclick="seleccionarMedio('mercadopago')">Mercado Pago</button>
                        <button class="medio-pago-btn" data-medio="qr" onclick="seleccionarMedio('qr')">QR</button>
                    </div>
                </div>
                <div style="margin-top: 2rem;">
                    <button class="cerrar-venta-btn" onclick="cerrarVenta(<?= $mesa['id'] ?>)">Cerrar Venta</button>
                </div>
            <?php elseif ($mostrarCambioMp): ?>
                <div class="cuenta-cerrada-msg">
                    <em>Venta cerrada.</em><br><br>
                    <button class="cerrar-venta-btn" id="cambiar-mp-btn" onclick="mostrarCambioMedioPago()">Cambiar medio de pago</button>
                </div>
                <div id="cambio-mp-container" style="display:none; margin-top:20px;">
                    <label class="cuenta-medio-pago-label"><strong>Seleccione nuevo medio de pago:</strong></label>
                    <div style="margin-top: 1rem;">
                        <button class="medio-pago-btn" data-medio="efectivo" onclick="cambiarMedioPago('efectivo')">Efectivo</button>
                        <button class="medio-pago-btn" data-medio="tarjeta" onclick="cambiarMedioPago('tarjeta')">Tarjeta</button>
                        <button class="medio-pago-btn" data-medio="mercadopago" onclick="cambiarMedioPago('mercadopago')">Mercado Pago</button>
                        <button class="medio-pago-btn" data-medio="qr" onclick="cambiarMedioPago('qr')">QR</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // --- Para mesa abierta ---
        let medioSeleccionado = '';
        function seleccionarMedio(medio) {
            medioSeleccionado = medio;
            document.querySelectorAll('.medio-pago-btn').forEach(btn => btn.classList.remove('activo'));
            const boton = Array.from(document.querySelectorAll('.medio-pago-btn')).find(btn => btn.getAttribute('data-medio') === medio);
            if (boton) boton.classList.add('activo');
        }
        function cerrarVenta(mesaId) {
            if (!medioSeleccionado) {
                alert("Debe seleccionar un medio de pago.");
                return;
            }
            fetch('<?= $ruta ?>/cajero/marcarPagado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `mesa_id=${mesaId}&medio_pago=${medioSeleccionado}`
            })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        alert("Venta cerrada con éxito.");
                        window.location.href = '<?= $ruta ?>/cajero/vistaCajero';
                    } else {
                        alert("Error al cerrar la venta.");
                    }
                });
        }
        // --- Para ticket cerrado (cambio de medio) ---
        function mostrarCambioMedioPago() {
            document.getElementById('cambio-mp-container').style.display = '';
        }
        function cambiarMedioPago(nuevoMedio) {
            if (!confirm("¿Confirmar cambio de medio de pago?")) return;
            fetch('<?= $ruta ?>/cajero/cambiarMedioPago', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'pedido_id=<?= (int)($_GET['id'] ?? 0) ?>&medio_pago=' + encodeURIComponent(nuevoMedio)
            })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        alert("Medio de pago actualizado.");
                        location.reload();
                    } else {
                        alert("No se pudo cambiar el medio de pago.");
                    }
                });
        }
        // Selecciona efectivo por defecto si hay botones de pago
        window.onload = function () {
            const btn = document.querySelector('.medio-pago-btn[data-medio="efectivo"]');
            if (btn) seleccionarMedio('efectivo');
        };
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <?= $head ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Terminal Cajero' ?></title>
    <style>
        .vistaCajero-user-info {
            display: flex;
            align-items: center;
            gap: 0px;
        }

        .vistaCajero-toggle {
            display: flex;
            justify-content: center;
            margin: 20px 0px 20px 20px;
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
            transition: background 0.18s;
        }

        .vistaCajero-toggle a.active {
            background-color: #000;
            color: #fff;
        }

        .vistaCajero-toggle a:not(.active) {
            background-color: #eee;
            color: #000;
        }

        .vistaCajero-tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 12px;
            padding: 0px;
        }

        .vistaCajero-table-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 12px;
            color: black;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .vistaCajero-table-ocupada {
            border-left: 6px solid red;
        }

        .vistaCajero-table-disponible {
            border-left: 6px solid green;
        }

        .vistaCajero-table-cuenta-solicitada {
            border-left: 6px solid orange;
        }

        .vistaCajero-table-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: black;
            margin: 0;
        }

        .vistaCajero-table-state {
            font-weight: bold;
            color: black;
        }

        .vistaCajero-table-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: black;
        }

        .vistaCajero-action-buttons {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .vistaCajero-action-btn {
            flex: 1;
            padding: 6px 10px;
            font-size: 0.85rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }

        .vistaCajero-btn-view {
            background-color: black;
        }

        .vistaCajero-btn-close {
            background-color: #d35400;
        }

        .vistaCajero-btn-pagado {
            background-color: #27ae60;
        }

        @media (max-width: 900px) {
            .vistaCajero-tables-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 10px;
            }

            .vistaCajero-table-card {
                padding: 10px;
                min-height: 120px;
            }

            .vistaCajero-action-btn {
                font-size: 0.8rem;
                padding: 5px 8px;
            }
        }

        @media (max-width: 600px) {
            .vistaCajero-tables-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .vistaCajero-main {
                padding: 4px;
            }

            .vistaCajero-table-card {
                padding: 8px;
                min-height: 90px;
            }

            .vistaCajero-table-number {
                font-size: 1rem;
            }

            .vistaCajero-table-state {
                font-size: 0.9rem;
            }

            .vistaCajero-action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .vistaCajero-action-btn {
                font-size: 0.85rem;
                width: 100%;
                padding: 7px 0;
            }
        }
    </style>
</head>

<body class="vistaCajero-body">
    <header><?= $nav ?></header>

    <div class="vistaCajero-toggle">
        <a href="<?= $ruta ?>/cajero/vistaCajero" class="active">Estado de Mesas</a>
        <a href="<?= $ruta ?>/pedido/listado">Pedidos en Curso</a>
        <a href="<?= $ruta ?>/cajero/planillaCaja">Planilla del Día</a>
    </div>

    <main class="vistaCajero-main">
        <div id="vistaMesas">
            <div class="vistaCajero-tables-grid">
                <?php foreach ($mesas as $mesa): ?>
                    <?php
                    $estado = strtolower($mesa['estado']);
                    $class = 'vistaCajero-table-card';
                    if ($estado === 'ocupada') {
                        $class .= ' vistaCajero-table-ocupada';
                        $estadoTexto = 'OCUPADA';
                    } elseif ($estado === 'cuenta_solicitada') {
                        $class .= ' vistaCajero-table-cuenta-solicitada';
                        $estadoTexto = 'CUENTA SOLICITADA';
                    } else {
                        $class .= ' vistaCajero-table-disponible';
                        $estadoTexto = 'DISPONIBLE';
                    }
                    ?>
                    <div class="<?= $class ?>">
                        <h3 class="vistaCajero-table-number">Mesa <?= $mesa['numero'] ?></h3>
                        <div class="vistaCajero-table-state">Estado: <?= $estadoTexto ?></div>
                        <div class="vistaCajero-table-info">
                            <div class="vistaCajero-table-time">
                                <?= $estado === 'disponible' ? 'Lista para usar' : '---' ?>
                            </div>
                            <div class="vistaCajero-table-amount">
                                $<?= number_format($mesa['total'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="vistaCajero-action-buttons">
                            <?php if ($estado === 'disponible'): ?>
                                <button class="vistaCajero-action-btn vistaCajero-btn-view">Asignar Mesa</button>
                            <?php else: ?>
                                <!-- VER CUENTA ahora va SIEMPRE por el NUMERO -->
                                <button class="vistaCajero-action-btn vistaCajero-btn-view"
                                    onclick="location.href='<?= $ruta ?>/cajero/cuenta?mesa=<?= $mesa['numero'] ?>'">Ver Cuenta</button>
                                <?php if ($mesa['total'] > 0): ?>
                                    <button class="vistaCajero-action-btn vistaCajero-btn-close"
                                        onclick="cerrarMesa(<?= $mesa['id'] ?>)">Cerrar Mesa</button>
                                <?php else: ?>
                                    <button class="vistaCajero-action-btn vistaCajero-btn-close" disabled
                                        style="background-color: gray; cursor: not-allowed;">Sin Consumo</button>
                                <?php endif; ?>
                                <button class="vistaCajero-action-btn vistaCajero-btn-pagado"
                                    onclick="marcarPagado(<?= $mesa['id'] ?>)">Pagado</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        function marcarPagado(mesaId) {
            fetch('<?= $ruta ?>/cajero/pagarMesa', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mesa_id=' + mesaId
            })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        location.reload();
                    } else {
                        alert('Error al marcar la mesa como pagada');
                    }
                });
        }

        document.addEventListener('click', function (e) {
            const menu = document.getElementById('logoutMenu');
            if (menu && !e.target.matches('.hamburger')) {
                menu.style.display = 'none';
            }
        });

        setInterval(() => {
            fetch(location.href)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nuevasMesas = doc.querySelector('#vistaMesas');
                    if (nuevasMesas) {
                        document.querySelector('#vistaMesas').innerHTML = nuevasMesas.innerHTML;
                    }
                });
        }, 10000);

        function cerrarMesa(mesaId) {
            fetch('<?= $ruta ?>/mesa/solicitarCuenta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mesa_id=' + mesaId
            })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        window.location.href = '<?= $ruta ?>/cajero/vistaCajero';
                    } else {
                        alert('Error al cerrar la mesa');
                    }
                });
        }
    </script>
</body>
</html>
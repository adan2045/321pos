<!DOCTYPE html>
<html lang="es">

<head>
    <title>Libro Diario - Movimientos</title>
    <link rel="stylesheet" href="/public/css/crud.css">
    <link rel="stylesheet" href="/public/css/listado.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #fff;
        }

        .planilla-libro {
            max-width: 800px;
            margin: 24px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 0 14px rgba(0, 0, 0, 0.06);
            padding: 18px 22px 32px 22px;
        }

        .planilla-encabezado {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 8px;
        }

        .planilla-encabezado .inicio-caja {
            background: #fff6e5;
            border: 1.5px solid #f2da9e;
            color: rgb(14, 14, 14);
            padding: 8px 24px;
            border-radius: 7px;
            font-size: 1.15rem;
            font-weight: bold;
            letter-spacing: .02em;
        }

        .planilla-movimientos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .planilla-movimientos th,
        .planilla-movimientos td {
            border: 1px solid #d2d2d2;
            padding: 6px 4px;
            font-size: 0.97rem;
            text-align: center;
        }

        .planilla-movimientos th {
            background: #f8f8f8;
            color: #222;
            font-weight: 600;
            font-size: 1.04rem;
        }

        /* Hover para todo menos la última fila */
        .planilla-movimientos tbody tr:not(.total):hover td {
            background: #f6f7fa;
        }

        /* Totales SIEMPRE fondo negro, texto blanco */
        .planilla-movimientos tr.total td {
            background: #111 !important;
            color: #fff !important;
            font-weight: bold;
            font-size: 1rem;
        }

        /* Fila inicio de caja: todo verde */
        .planilla-movimientos tr.inicio td {
            color: #1d910a !important;
            background: #eaffed !important;
            font-weight: bold;
        }

        /* Fila gasto o caja fuerte: todo rojo */
        .planilla-movimientos tr.egreso td {
            color: #d10808 !important;
            background: #fff0f0 !important;
            font-weight: bold;
        }

        /* Link a ticket: sutil */
        .planilla-movimientos td a {
            color: #0046a7;
            text-decoration: underline dotted;
        }

        .planilla-movimientos td a:hover {
            color: #095cff;
            text-decoration: underline solid;
        }

        .planilla-botones {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 9px;
        }

        .planilla-botones .btn {
            padding: 6px 25px;
            border: none;
            border-radius: 7px;
            background: #111;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
        }

        .planilla-encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .titulo-mov {
            font-size: 2.1rem;
            font-weight: 900;
            color: #181818;
            margin: 0;
            padding: 0 26px 0 0;
            letter-spacing: 0.03em;
            flex: 1 1 0;
            text-align: left;
        }

        @media (max-width: 700px) {
            .planilla-encabezado {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .titulo-mov {
                padding-right: 0;
                text-align: left;
                font-size: 1.4rem;
            }

            .planilla-encabezado .inicio-caja {
                min-width: 0;
                text-align: left;
            }
        }

        .planilla-dia {
            background: #fff6e5;
            border: 1.5px solid #f2da9e;
            color: #222;
            padding: 8px 24px;
            border-radius: 7px;
            font-size: 1.17rem;
            font-weight: bold;
            letter-spacing: .02em;
            min-width: 130px;
            text-align: center;
        }

        .planilla-botones .btn:hover {
            background: #333;
        }
        .btn-cancelar{
            margin-left:8px;
             padding:2px 8px;
             font-size:0.8rem;
             border:none;
             background:#b00000;
             color:#fff;
             border-radius:4px;
             cursor:pointer;
        }
        .btn-cancelar:hover{
            background:#ff0000;
        }

        @media (max-width: 850px) {
            .planilla-libro {
                max-width: 99vw;
                padding: 6px 2vw 18px 2vw;
            }
        }

        @media (max-width: 600px) {
            .planilla-libro {
                padding: 4px 1vw 8px 1vw;
            }

            .planilla-movimientos th,
            .planilla-movimientos td {
                font-size: 0.93rem;
                padding: 3px 1px;
            }
        }
    </style>
</head>

<body>
    <div class="planilla-libro">
        <div class="planilla-encabezado">
            <h1 class="titulo-mov">Movimientos del Día</h1>
            <div class="planilla-dia">
                <?= date('d/m/Y') ?>
            </div>
        </div>
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
                    <th>Hora</th>
                </tr>
            </thead>
           <tbody>
<?php foreach ($movimientos as $mov): 

    // Clase visual por tipo
    $trClass = '';

    if ($mov['tipo'] === 'inicio') {
        $trClass = 'inicio';
    }

    if (in_array($mov['tipo'], ['gasto', 'retiro', 'nota_credito', 'ajuste_metodo'])) {
        $trClass = 'egreso';
    }

    $esVenta = ($mov['tipo'] === 'venta');
?>

<tr class="<?= $trClass ?>">

    <!-- DETALLE -->
    <td>

        <?php if ($esVenta && !empty($mov['ticket_url'])): ?>

            <a href="<?= htmlspecialchars($mov['ticket_url']) ?>" target="_blank">
                <?= htmlspecialchars($mov['detalle']) ?>
            </a>

            <!-- BOTÓN CANCELAR -->
            <button 
                class="btn-cancelar"
                data-ticket="<?= $mov['numero_ticket'] ?>">
                Cancelar
            </button>

        <?php else: ?>

            <?= htmlspecialchars($mov['detalle']) ?>

        <?php endif; ?>

    </td>

    <!-- EFECTIVO -->
    <td>
        <?= ($mov['efectivo'] !== '' 
            ? number_format($mov['efectivo'], 0, '', '.') 
            : '') ?>
    </td>

    <!-- TARJETA -->
    <td>
        <?= ($mov['tarjeta'] !== '' 
            ? number_format($mov['tarjeta'], 0, '', '.') 
            : '') ?>
    </td>

    <!-- QR -->
    <td>
        <?= ($mov['qr'] !== '' 
            ? number_format($mov['qr'], 0, '', '.') 
            : '') ?>
    </td>

    <!-- MERCADOPAGO -->
    <td>
        <?= ($mov['mp'] !== '' 
            ? number_format($mov['mp'], 0, '', '.') 
            : '') ?>
    </td>

    <!-- TOTAL -->
    <td>
        <?= ($mov['total'] !== '' 
            ? number_format($mov['total'], 0, '', '.') 
            : '') ?>
    </td>

    <!-- MESA -->
    <td><?= $mov['mesa'] ?? '' ?></td>

    <!-- HORA -->
    <td><?= date('H:i', strtotime($mov['fecha_hora'])) ?></td>

</tr>

<?php endforeach; ?>
</tbody>
            <tfoot>
                <tr class="total">
                    <td><b>Totales</b></td>
                    <td><b><?= number_format($totales['efectivo'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format($totales['tarjeta'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format($totales['qr'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format($totales['mp'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format($totales['total'], 0, '', '.') ?></b></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <div class="planilla-botones">
            <button onclick="window.print()" class="btn">Imprimir</button>
        </div>
    </div>
    <!-- ═════ MODAL CANCELAR TICKET ═════ -->
<div id="modalCancelar" style="
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    z-index:99999;
    align-items:center;
    justify-content:center;
">
    <div style="
        background:#fff;
        width:360px;
        padding:22px;
        border-radius:10px;
        box-shadow:0 15px 50px rgba(0,0,0,.3);
        display:flex;
        flex-direction:column;
        gap:14px;
    ">
        <h3 style="margin:0;">Cancelar Ticket</h3>

        <div id="cancelTicketInfo" style="font-size:14px;color:#555;"></div>

        <input type="password"
               id="cancelPassword"
               placeholder="Contraseña de supervisor"
               style="padding:8px;border:1px solid #ccc;border-radius:6px;">

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button id="btnCerrarCancel"
                    style="padding:6px 12px;border:1px solid #aaa;background:#fff;border-radius:6px;cursor:pointer;">
                Cerrar
            </button>

            <button id="btnConfirmarCancel"
                    style="padding:6px 14px;background:#d10808;color:#fff;border:none;border-radius:6px;cursor:pointer;">
                Confirmar
            </button>
        </div>
    </div>
</div>
<script>

let ticketACancelar = null;

// Abrir modal
document.querySelectorAll('.btn-cancelar').forEach(btn => {

    btn.addEventListener('click', function(){

        ticketACancelar = this.dataset.ticket;

        document.getElementById('cancelTicketInfo').innerText =
            "Ticket: #" + ticketACancelar;

        document.getElementById('cancelPassword').value = '';

        document.getElementById('modalCancelar').style.display = 'flex';
    });

});

// Cerrar
document.getElementById('btnCerrarCancel').addEventListener('click', function(){
    document.getElementById('modalCancelar').style.display = 'none';
});

// Confirmar
document.getElementById('btnConfirmarCancel').addEventListener('click', function(){

    const pass = document.getElementById('cancelPassword').value.trim();

    if(!pass){
        alert("Ingresá la contraseña");
        return;
    }

    fetch('/321POS/public/cajero/cancelarTicket', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            ticket: ticketACancelar,
            password: pass
        })
    })
    .then(res => res.json())
    .then(data => {

        if(data.status === 'ok'){
            alert("Ticket cancelado correctamente");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }

    });

});

</script>
</body>

</html>
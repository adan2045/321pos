<!DOCTYPE html>
<html lang="es">
<head>
    <title>Libro Diario - Movimientos</title>
    <link rel="stylesheet" href="/public/css/crud.css">
    <link rel="stylesheet" href="/public/css/listado.css">
    <style>
        body { margin:0; padding:0; font-family: Arial, sans-serif; background:#fff; }

        .planilla-libro{
            max-width:800px;
            margin:24px auto 0 auto;
            background:#fff;
            border-radius:14px;
            box-shadow:0 0 14px rgba(0,0,0,0.06);
            padding:18px 22px 32px 22px;
        }

        .planilla-encabezado{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:8px;
        }

        .titulo-mov{
            font-size:2.1rem;
            font-weight:900;
            color:#181818;
            margin:0;
            padding:0 26px 0 0;
            letter-spacing:0.03em;
            flex:1 1 0;
            text-align:left;
        }

        .planilla-dia{
            background:#fff6e5;
            border:1.5px solid #f2da9e;
            color:#222;
            padding:8px 24px;
            border-radius:7px;
            font-size:1.17rem;
            font-weight:bold;
            letter-spacing:.02em;
            min-width:130px;
            text-align:center;
        }

        .planilla-movimientos{
            width:100%;
            border-collapse:collapse;
            margin-bottom:5px;
        }

        .planilla-movimientos th,
        .planilla-movimientos td{
            border:1px solid #d2d2d2;
            padding:6px 4px;
            font-size:0.97rem;
            text-align:center;
        }

        .planilla-movimientos th{
            background:#f8f8f8;
            color:#222;
            font-weight:600;
            font-size:1.04rem;
        }

        .planilla-movimientos tbody tr:not(.total):hover td{
            background:#f6f7fa;
        }

        .planilla-movimientos tr.total td{
            background:#111 !important;
            color:#fff !important;
            font-weight:bold;
            font-size:1rem;
        }

        .planilla-movimientos tr.inicio td{
            color:#1d910a !important;
            background:#eaffed !important;
            font-weight:bold;
        }

        .planilla-movimientos tr.caja-fuerte td{
            color:#0046a7 !important;
            background:#eef4ff !important;
            font-weight:bold;
        }

        .planilla-movimientos tr.egreso td{
            color:#d10808 !important;
            background:#fff0f0 !important;
            font-weight:bold;
        }

        /* Link ticket */
        .planilla-movimientos td a{
            color:#0046a7;
            text-decoration: underline dotted;
            cursor:pointer;
        }
        .planilla-movimientos td a:hover{
            color:#095cff;
            text-decoration: underline solid;
        }

        .planilla-botones{
            display:flex;
            justify-content:flex-end;
            gap:12px;
            margin-top:9px;
        }

        .btn-cerrar{
            padding:8px 18px;
            border:none;
            border-radius:8px;
            background:#111;
            color:#fff;
            font-size:1rem;
            cursor:pointer;
            font-weight:700;
            white-space:nowrap;
        }
        .btn-cerrar:hover{ background:#333; }

        .planilla-botones .btn{
            padding:6px 25px;
            border:none;
            border-radius:7px;
            background:#111;
            color:#fff;
            font-size:1rem;
            cursor:pointer;
            font-weight:600;
        }
        .planilla-botones .btn:hover{ background:#333; }

        @media (max-width:700px){
            .planilla-encabezado{ flex-direction:column; align-items:stretch; gap:10px; }
            .titulo-mov{ padding-right:0; text-align:left; font-size:1.4rem; }
            .planilla-dia{ min-width:0; text-align:left; }
        }

        @media (max-width:850px){
            .planilla-libro{ max-width:99vw; padding:6px 2vw 18px 2vw; }
        }
        @media (max-width:600px){
            .planilla-libro{ padding:4px 1vw 8px 1vw; }
            .planilla-movimientos th,
            .planilla-movimientos td{ font-size:0.93rem; padding:3px 1px; }
        }

        /* ===== Modal Ticket ===== */
        #ticketModalOverlay{
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.55);
            z-index:99999;
            align-items:center;
            justify-content:center;
            padding:18px;
        }
        #ticketModalBox{
            width:760px;
            max-width:95vw;
            background:#fff;
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 18px 60px rgba(0,0,0,.35);
        }
        #ticketModalHeader{
            background:#2c3e50;
            color:#fff;
            padding:12px 16px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        #ticketModalClose{
            background:none;border:none;color:#fff;
            font-size:20px;cursor:pointer;
        }
        #ticketModalMeta{
            font-size:12px;color:#6b7280;margin-bottom:10px;
        }
        #ticketModalBody{
            border:1px solid #e5e7eb;
            border-radius:10px;
            padding:12px;
            min-height:240px;
        }
        .ticket-actions{
            width:220px;
            display:flex;
            flex-direction:column;
            gap:10px;
        }
        .ticket-btn{
            padding:12px;
            border-radius:10px;
            border:1px solid #e5e7eb;
            cursor:pointer;
            font-weight:800;
            background:#fff;
        }
        .ticket-btn:hover{ background:#f6f7fa; }
    </style>
</head>

<body>
    <div class="planilla-libro">
        <div class="planilla-encabezado">
            <h1 class="titulo-mov">Movimientos del Día</h1>

            <div style="display:flex; gap:12px; align-items:center; justify-content:flex-end; flex-wrap:wrap;">
                <div class="planilla-dia"><?= date('d/m/Y') ?></div>
                <button type="button" class="btn-cerrar" onclick="cerrarLibroDiario()">Cerrar</button>
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

                    $tipo = $mov['tipo'] ?? '';

                    $trClass = '';
                    if ($tipo === 'inicio')      $trClass = 'inicio';
                    if ($tipo === 'gasto')       $trClass = 'egreso';
                    if ($tipo === 'caja_fuerte') $trClass = 'caja-fuerte';

                    $isVenta = ($tipo === 'venta');
                    $tid     = (int)($mov['ticket_id'] ?? 0);
                ?>
                    <tr class="<?= $trClass ?>">
                        <td>
                            <?php if ($isVenta && $tid > 0): ?>
                                <a href="#" class="ticket-link" data-ticket-id="<?= $tid ?>">
                                    <?= htmlspecialchars($mov['detalle'] ?? '') ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($mov['detalle'] ?? '') ?>
                            <?php endif; ?>
                        </td>

                        <td><?= (($mov['efectivo'] ?? '') !== '' ? number_format((float)$mov['efectivo'], 0, '', '.') : '') ?></td>
                        <td><?= (($mov['tarjeta']  ?? '') !== '' ? number_format((float)$mov['tarjeta'],  0, '', '.') : '') ?></td>
                        <td><?= (($mov['qr']       ?? '') !== '' ? number_format((float)$mov['qr'],       0, '', '.') : '') ?></td>
                        <td><?= (($mov['mp']       ?? '') !== '' ? number_format((float)$mov['mp'],       0, '', '.') : '') ?></td>
                        <td><?= (($mov['total']    ?? '') !== '' ? number_format((float)$mov['total'],    0, '', '.') : '') ?></td>

                        <td><?= htmlspecialchars((string)($mov['mesa'] ?? '')) ?></td>
                        <td><?= !empty($mov['fecha_hora']) ? date('H:i', strtotime($mov['fecha_hora'])) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

            <tfoot>
                <tr class="total">
                    <td><b>Totales</b></td>
                    <td><b><?= number_format((float)$totales['efectivo'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format((float)$totales['tarjeta'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format((float)$totales['qr'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format((float)$totales['mp'], 0, '', '.') ?></b></td>
                    <td><b><?= number_format((float)$totales['total'], 0, '', '.') ?></b></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>

        <div class="planilla-botones">
            <button onclick="window.print()" class="btn">Imprimir</button>
        </div>
    </div>

    <!-- ===== Modal Ticket ===== -->
    <div id="ticketModalOverlay">
        <div id="ticketModalBox">
            <div id="ticketModalHeader">
                <div style="font-weight:900;">🧾 Ticket <span id="ticketModalTitle"></span></div>
                <button id="ticketModalClose" type="button">✕</button>
            </div>

            <div style="display:flex; gap:14px; padding:14px 16px;">
                <div style="flex:1;">
                    <div id="ticketModalMeta"></div>
                    <div id="ticketModalBody">Cargando...</div>
                </div>

                <div class="ticket-actions">
                    <button id="ticketBtnNotaCredito" class="ticket-btn" type="button">🧾 Eliminar / Nota de crédito</button>
                    <button id="ticketBtnCerrar" class="ticket-btn" type="button">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

<script>
function cerrarLibroDiario(){
  try{
    const p = window.parent;
    if(p && p !== window && p.document){
      const iframes = p.document.querySelectorAll('iframe');
      for(const ifr of iframes){
        if(ifr && ifr.contentWindow === window){
          let node = ifr.parentElement;
          while(node && node !== p.document.body){
            const cls = (node.className || '').toString().toLowerCase();
            const style = p.getComputedStyle(node);

            const esOverlay =
              style.position === 'fixed' ||
              cls.includes('modal') ||
              cls.includes('overlay') ||
              cls.includes('backdrop');

            if(esOverlay){
              node.style.display = 'none';
              ifr.src = 'about:blank';
              return;
            }
            node = node.parentElement;
          }

          ifr.style.display = 'none';
          ifr.src = 'about:blank';
          return;
        }
      }
    }
  }catch(e){}

  window.location.href = '<?= \App::baseUrl() ?>/pos';
}

document.addEventListener('keydown', (e)=>{
  if(e.key === 'Escape') cerrarLibroDiario();
});
</script>

<script>
(function(){
  const overlay = document.getElementById('ticketModalOverlay');
  const closeX  = document.getElementById('ticketModalClose');
  const closeB  = document.getElementById('ticketBtnCerrar');

  const titleEl = document.getElementById('ticketModalTitle');
  const metaEl  = document.getElementById('ticketModalMeta');
  const bodyEl  = document.getElementById('ticketModalBody');

  const btnNC   = document.getElementById('ticketBtnNotaCredito');

  let currentTicketId = null;

  function openModal(){ overlay.style.display = 'flex'; }
  function closeModal(){
    overlay.style.display = 'none';
    currentTicketId = null;
    titleEl.textContent = '';
    metaEl.textContent = '';
    bodyEl.textContent = '';
  }

  closeX?.addEventListener('click', closeModal);
  closeB?.addEventListener('click', closeModal);
  overlay?.addEventListener('click', (e)=>{ if(e.target === overlay) closeModal(); });

  window.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape' && overlay.style.display === 'flex') closeModal();
  });

  async function loadTicket(id){
    currentTicketId = id;
    titleEl.textContent = '#' + String(id).padStart(4,'0');
    metaEl.textContent = '';
    bodyEl.textContent = 'Cargando...';
    openModal();

    const url = '<?= \App::baseUrl() ?>/cajero/ticketJson?id=' + encodeURIComponent(id);
    const res = await fetch(url);
    const data = await res.json().catch(()=>null);

    if(!res.ok || !data || data.status !== 'ok'){
      bodyEl.innerHTML = '<div style="color:#b91c1c;font-weight:900;">Error cargando ticket</div>';
      return;
    }

    metaEl.textContent = `${data.header.fecha} · Mesa: ${data.header.mesa || '-'} · Pago: ${data.header.metodo}`;

    const rows = (data.items || []).map(it => `
      <tr>
        <td style="padding:6px 4px; border-bottom:1px solid #f3f4f6;">${it.cantidad}</td>
        <td style="padding:6px 4px; border-bottom:1px solid #f3f4f6;">${it.nombre}</td>
        <td style="padding:6px 4px; border-bottom:1px solid #f3f4f6; text-align:right;">$ ${it.precio}</td>
        <td style="padding:6px 4px; border-bottom:1px solid #f3f4f6; text-align:right; font-weight:800;">$ ${it.total}</td>
      </tr>
    `).join('');

    bodyEl.innerHTML = `
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="text-align:left; color:#6b7280;">
            <th style="padding:6px 4px; border-bottom:1px solid #e5e7eb;">Cant</th>
            <th style="padding:6px 4px; border-bottom:1px solid #e5e7eb;">Producto</th>
            <th style="padding:6px 4px; border-bottom:1px solid #e5e7eb; text-align:right;">Precio</th>
            <th style="padding:6px 4px; border-bottom:1px solid #e5e7eb; text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
      <div style="margin-top:10px; text-align:right; font-weight:900; font-size:16px;">
        TOTAL: $ ${data.header.total}
      </div>
    `;
  }

  document.querySelectorAll('.ticket-link').forEach(a=>{
    a.addEventListener('click', (e)=>{
      e.preventDefault();
      const id = parseInt(a.dataset.ticketId || '0', 10);
      if(!id) return;
      loadTicket(id);
    });
  });

  btnNC?.addEventListener('click', ()=>{
    if(!currentTicketId) return;
    alert('Siguiente paso: anular ticket #' + currentTicketId + ' y generar Nota de Crédito (con PIN admin).');
  });
})();
</script>

</body>
</html>
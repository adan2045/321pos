<?php
// app/views/partials/gestion.modals.php
// Requiere: $ruta (string) y opcional $ultimoCierre (float/int)

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$cajaIdSesion = $_SESSION['caja_id'] ?? '';
?>

<!-- ===================== -->
<!-- MODAL: ABRIR CAJA -->
<!-- ===================== -->
<div id="abrirCajaModal" class="modal-backdrop" style="display:none;">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="abrirCajaTitle">
    <div class="modal-head">
      <div class="modal-title" id="abrirCajaTitle">🏦 Inicio de Caja</div>
      <button type="button" class="modal-x" onclick="cerrarCajaModal()">✕</button>
    </div>

    <!-- Tu controller usa $_GET['monto'] -->
    <form method="GET" action="<?= $ruta ?>/cajero/abrirCaja" class="modal-body">
      <div class="modal-row">
        <label class="modal-label">Monto</label>
        <input id="montoAbrirCaja" name="monto" type="number" step="0.01" min="0" class="modal-input" required>
      </div>

      <div class="modal-actions">
        <button type="button" class="modal-btn ghost" onclick="cerrarCajaModal()">Cancelar</button>
        <button type="submit" class="modal-btn primary">Abrir Caja</button>
      </div>
    </form>
  </div>
</div>

<!-- ===================== -->
<!-- MODAL: GASTO / EGRESO -->
<!-- ===================== -->

<div id="gastoModal" class="modal-backdrop" style="display:none;">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="gastoTitle">
    <div class="modal-head">
      <div class="modal-title" id="gastoTitle">💸 Registrar Egreso</div>
      <button type="button" class="modal-x" onclick="cerrarGastoModal()">✕</button>
    </div>

    <!-- Tu controller exige: motivo, monto, autorizado_por, caja_id -->
    <form method="POST" action="<?= $ruta ?>/cajero/registrarGasto" class="modal-body">
      <input type="hidden" name="caja_id" value="<?= htmlspecialchars($cajaIdSesion) ?>">
      <input type="hidden" name="redirect" value="<?= $ruta ?>/pos">

      <div class="modal-row">
        <label class="modal-label">Motivo</label>
        <input name="motivo" type="text" class="modal-input" placeholder="Ej: Compra insumos" required>
      </div>

      <div class="modal-row">
        <label class="modal-label">Monto</label>
        <input name="monto" type="number" step="0.01" min="0" class="modal-input" required>
      </div>

      <div class="modal-row">
        <label class="modal-label">Autorizado por</label>
        <input name="autorizado_por" type="text" class="modal-input" placeholder="Ej: Encargado" required>
      </div>

      <div class="modal-actions">
        <button type="button" class="modal-btn ghost" onclick="cerrarGastoModal()">Cancelar</button>
        <button type="submit" class="modal-btn primary">Guardar</button>
      </div>
    </form>
  </div>
</div>


<!-- ===================== -->
<!-- MODAL: CAJA FUERTE -->
<!-- ===================== -->
<div id="cajaFuerteModal" class="modal-backdrop" style="display:none;">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="cfTitle">
    <div class="modal-head">
      <div class="modal-title" id="cfTitle">🔒 Caja Fuerte</div>
      <button type="button" class="modal-x" onclick="cerrarCajaFuerteModal()">✕</button>
    </div>

    <!-- Tu controller exige: monto, responsable, caja_id -->
    <form method="POST" action="<?= $ruta ?>/cajero/registrarCajaFuerte" class="modal-body">
      <input type="hidden" name="caja_id" value="<?= htmlspecialchars($cajaIdSesion) ?>">
      <input type="hidden" name="redirect" value="<?= $ruta ?>/pos">

      <div class="modal-row">
        <label class="modal-label">Monto</label>
        <input name="monto" type="number" step="0.01" min="0" class="modal-input" required>
      </div>

      <div class="modal-row">
        <label class="modal-label">Responsable</label>
        <input name="responsable" type="text" class="modal-input" placeholder="Ej: Barbara" required>
      </div>

      <div class="modal-actions">
        <button type="button" class="modal-btn ghost" onclick="cerrarCajaFuerteModal()">Cancelar</button>
        <button type="submit" class="modal-btn primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- ===================== -->
<!-- ESTILOS (si ya tenés estilos de modal, podés borrar esta parte) -->
<!-- ===================== -->
<style>
  .modal-backdrop{
    position: fixed; inset: 0;
    background: rgba(0,0,0,.35);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 14px;
  }
  .modal-card{
    width: min(520px, 96vw);
    background: #fff;
    border: 1px solid #bdc3c7;
    border-radius: 14px;
    box-shadow: 0 14px 40px rgba(0,0,0,.25);
    overflow: hidden;
  }
  .modal-head{
    background: #2c3e50;
    color: #fff;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding: 12px 14px;
  }
  .modal-title{ font-weight: 800; }
  .modal-x{
    border:0;
    background: rgba(255,255,255,.10);
    color:#fff;
    width:34px; height:34px;
    border-radius:10px;
    cursor:pointer;
  }
  .modal-body{ padding: 14px; }
  .modal-row{ display:grid; gap:8px; margin-bottom: 12px; }
  .modal-label{ font-weight: 800; font-size: 12px; color:#2c3e50; }
  .modal-input{
    padding: 10px 12px;
    border: 1px solid #bdc3c7;
    border-radius: 10px;
    outline:none;
    font-size: 14px;
  }
  .modal-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top: 14px;
  }
  .modal-btn{
    padding: 10px 14px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 800;
    border: 1px solid transparent;
  }
  .modal-btn.ghost{
    background: #fff;
    border-color: #bdc3c7;
    color: #2c3e50;
  }
  .modal-btn.primary{
    background: #27ae60;
    color: #fff;
  }
</style>

<script>
  // Cerrar caja (usa $ruta)
  function cerrarCaja() {
    if (confirm('¿Desea cerrar la caja?')) {
      window.location.href = '<?= $ruta ?>/cajero/cerrarCaja';
    }
  }

  // ===== POPUP Gastos =====
  function abrirGastoModal() {
    const m = document.getElementById('gastoModal');
    if (!m) { console.warn("No existe #gastoModal"); return; }
    m.style.display = 'flex';
    setTimeout(() => {
      const input = document.querySelector('#gastoModal input[name="motivo"]');
      input?.focus();
    }, 150);
  }
  function cerrarGastoModal() {
    const m = document.getElementById('gastoModal');
    if (m) m.style.display = 'none';
  }

  // ===== POPUP Abrir Caja =====
  function abrirCajaModal() {
    const m = document.getElementById('abrirCajaModal');
    if (!m) { console.warn("No existe #abrirCajaModal"); return; }
    m.style.display = 'flex';

    // Setear monto sugerido (ultimo cierre) si existe
    const input = document.getElementById('montoAbrirCaja');
    if (input) {
      <?php if (!empty($ultimoCierre)): ?>
        input.value = <?= json_encode($ultimoCierre) ?>;
      <?php else: ?>
        input.value = "0";
      <?php endif; ?>
    }

    setTimeout(() => { input?.focus(); }, 150);
  }
  function cerrarCajaModal() {
    const m = document.getElementById('abrirCajaModal');
    if (m) m.style.display = 'none';
  }

  // ===== POPUP Caja Fuerte =====
  function abrirCajaFuerteModal() {
    const m = document.getElementById('cajaFuerteModal');
    if (!m) { console.warn("No existe #cajaFuerteModal"); return; }
    m.style.display = 'flex';
    setTimeout(() => {
      const input = document.querySelector('#cajaFuerteModal input[name="monto"]');
      input?.focus();
    }, 150);
  }
  function cerrarCajaFuerteModal() {
    const m = document.getElementById('cajaFuerteModal');
    if (m) m.style.display = 'none';
  }

  // Click afuera para cerrar
  document.getElementById('abrirCajaModal')?.addEventListener('click', (e)=>{ if(e.target.id==='abrirCajaModal') cerrarCajaModal(); });
  document.getElementById('gastoModal')?.addEventListener('click', (e)=>{ if(e.target.id==='gastoModal') cerrarGastoModal(); });
  document.getElementById('cajaFuerteModal')?.addEventListener('click', (e)=>{ if(e.target.id==='cajaFuerteModal') cerrarCajaFuerteModal(); });

  // ===== ESC para cerrar =====
  window.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
      cerrarGastoModal();
      cerrarCajaModal();
      cerrarCajaFuerteModal();
    }
  });
</script>
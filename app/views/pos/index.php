<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>POS Mostrador - Sistema de Ventas</title>
<style>
  :root {
    --primary: #2c3e50;
    --secondary: #34495e;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #e74c3c;
    --info: #3498db;
    --light: #ecf0f1;
    --dark: #2c3e50;
    --white: #ffffff;
    --border: #bdc3c7;
    --text: #2c3e50;
    --muted: #7f8c8d;
    --shadow: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-lg: 0 4px 8px rgba(0,0,0,0.15);

    --violet:   #7c5cbf;
    --violet-l: #9b7fd4;
    --violet-bg: rgba(124,92,191,0.07);
    --green-a:  #0ea87a;
    --green-bg: rgba(14,168,122,0.07);
    --cyan-a:   #0ea5c9;
    --cyan-bg:  rgba(14,165,201,0.07);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f5f7;
    color: var(--text);
    height: 100vh;
    overflow: hidden;
  }

  .header {
    background: var(--dark);
    color: var(--white);
    padding: 10px 20px;
    display: flex; justify-content: space-between; align-items: center;
    height: 50px;
    box-shadow: var(--shadow-lg);
    position: relative;
  }
  .header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--violet), var(--cyan-a), var(--green-a));
    opacity: 0.7;
  }
  .header-left { font-size: 16px; font-weight: bold; display: flex; align-items: center; gap: 8px; }
  .header-left .rocket { font-size: 18px; }
  .header-right { display: flex; gap: 20px; font-size: 14px; }

  .main-container {
    display: grid;
    grid-template-columns: 250px 1fr 300px;
    height: calc(100vh - 50px);
  }

  .left-panel {
    background: var(--white);
    border-right: 1px solid var(--border);
    padding: 15px;
    display: flex; flex-direction: column; gap: 12px;
    overflow-y: auto;
  }

  .field-group { display: flex; flex-direction: column; gap: 6px; }

  .field-label {
    font-size: 12px; font-weight: bold;
    color: var(--muted); text-transform: uppercase;
  }

  .input-with-icon { position: relative; display: flex; align-items: center; }
  .input-with-icon .field-input { padding-right: 36px; width: 100%; }
  .input-lupa {
    position: absolute; right: 8px;
    background: none; border: none;
    cursor: pointer; font-size: 16px; color: var(--muted); padding: 0; line-height: 1;
    transition: color 0.2s;
  }
  .input-lupa:hover { color: var(--cyan-a); }

  .field-input {
    padding: 10px; border: 1px solid var(--border);
    border-radius: 4px; font-size: 14px; background: var(--white);
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .field-input:focus {
    outline: none; border-color: var(--violet);
    box-shadow: 0 0 0 2px rgba(124,92,191,0.12);
  }

  .desc-display {
    padding: 10px; border: 1px solid var(--border);
    border-radius: 4px; font-size: 13px; font-weight: 600;
    background: #f0faf4; color: var(--dark);
    min-height: 38px; cursor: default; user-select: none;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .desc-display.empty     { color: var(--border); font-weight: normal; font-style: italic; }
  .desc-display.found     { color: var(--green-a); border-color: var(--green-a); background: var(--green-bg); }
  .desc-display.not-found { color: var(--danger);  border-color: var(--danger); }

  .price-display {
    padding: 10px; border: 1px solid var(--border); border-radius: 4px;
    font-size: 16px; font-weight: bold; text-align: right;
    background: var(--cyan-bg); color: var(--dark);
    border-color: rgba(14,165,201,0.25);
    cursor: default; user-select: none;
  }
  .price-label-note { font-size: 10px; color: var(--muted); font-style: italic; margin-top: -4px; }

  .qty-input-plain { width: 100%; text-align: center; font-weight: bold; }

  .btn-row { display: flex; gap: 9px; }

  .ubtn {
    flex: 1; padding: 12px 6px;
    border-radius: 6px; font-weight: 600;
    cursor: pointer; transition: all 0.18s;
    font-size: 12px; text-align: center; line-height: 1.4;
    box-shadow: var(--shadow);
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    border: 1px solid transparent;
  }
  .ubtn .icon { font-size: 16px; }
  .ubtn:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }

  .ubtn.borrar       { background: #fef0ee; border-color: #f1a9a0; color: #b03a2e; }
  .ubtn.borrar:hover { background: #fde0dd; }
  .ubtn.cancelar       { background: #f4f6f7; border-color: #bdc3c7; color: #5d6d7e; }
  .ubtn.cancelar:hover { background: #eaecee; }

  .ubtn.inicio-caja       { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); }
  .ubtn.inicio-caja:hover { background: rgba(124,92,191,0.13); }
  .ubtn.fichar       { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); }
  .ubtn.fichar:hover { background: rgba(124,92,191,0.13); }
  .ubtn.egresos       { background: #fef9ec; border-color: #f9e79f; color: #9a6400; }
  .ubtn.egresos:hover { background: #fdf2ce; }
  .ubtn.caja-fuerte       { background: var(--cyan-bg); border-color: rgba(14,165,201,0.25); color: var(--cyan-a); }
  .ubtn.caja-fuerte:hover { background: rgba(14,165,201,0.13); }

  .ubtn.dividido       { background: #fef9ec; border-color: #f9e79f; color: #9a6400; }
  .ubtn.dividido:hover { background: #fdf2ce; }
  .ubtn.mercadopago       { background: var(--cyan-bg); border-color: rgba(14,165,201,0.25); color: var(--cyan-a); }
  .ubtn.mercadopago:hover { background: rgba(14,165,201,0.13); }
  .ubtn.tarjeta       { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); }
  .ubtn.tarjeta:hover { background: rgba(124,92,191,0.13); }
  .ubtn.efectivo       { background: var(--green-bg); border-color: rgba(14,168,122,0.25); color: var(--green-a); }
  .ubtn.efectivo:hover { background: rgba(14,168,122,0.13); }

  .ubtn.planilla {
    background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet);
    flex-direction: row; gap: 6px; padding: 11px 16px; font-size: 13px; flex: 1;
  }
  .ubtn.planilla:hover { background: rgba(124,92,191,0.13); }
  .ubtn.planilla .icon { font-size: 15px; }

  .section-divider {
    display: flex; align-items: center; gap: 8px;
    color: var(--border); font-size: 10px;
    text-transform: uppercase; letter-spacing: 0.5px;
  }
  .section-divider::before, .section-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
  }

  .center-panel {
    background: var(--white);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
  }
  .panel-header {
    background: var(--dark); padding: 12px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
    position: relative;
  }
  .panel-header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--violet), var(--cyan-a));
    opacity: 0.5;
  }
  .panel-title { font-size: 15px; font-weight: bold; color: var(--white); }
  .item-count {
    background: rgba(255,255,255,0.15); color: var(--white);
    padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;
  }

  .table-container { height: calc(6 * 49px); overflow-y: auto; flex-shrink: 0; }

  .products-table { width: 100%; border-collapse: collapse; }
  .products-table thead th {
    background: #f8f9fa; padding: 12px 8px;
    text-align: left; font-size: 11px; font-weight: bold;
    color: var(--muted); text-transform: uppercase;
    border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 1;
  }
  .products-table tbody td { padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
  .products-table tbody tr { cursor: pointer; transition: background 0.15s; }
  .products-table tbody tr:hover { background: rgba(124,92,191,0.05); }
  .products-table tbody tr.selected {
    background: rgba(124,92,191,0.09);
    outline: 2px solid rgba(124,92,191,0.35); outline-offset: -1px;
  }
  .products-table tbody tr.selected td { font-weight: 600; color: var(--violet); }

  .qty-cell {
    width: 55px; padding: 4px 6px;
    border: 1px solid transparent; border-radius: 4px;
    font-size: 13px; font-weight: bold; text-align: center;
    background: transparent; cursor: text; font-family: inherit;
  }
  .qty-cell:focus { outline: none; border-color: var(--violet); background: var(--white); }

  .delete-item { color: var(--danger); cursor: pointer; padding: 2px 6px; border-radius: 3px; transition: all 0.2s; }
  .delete-item:hover { background: var(--danger); color: var(--white); }

  .center-notes { padding: 10px 20px; background: #fafbfc; border-top: 1px solid #eee; display: flex; flex-direction: column; gap: 5px; }
  .center-notes-label { font-size: 11px; color: var(--muted); font-weight: bold; text-transform: uppercase; }
  .center-notes textarea {
    width: 100%; height: 62px; border: 1px solid var(--border); border-radius: 4px;
    padding: 6px 8px; font-size: 12px; resize: none; font-family: inherit; background: var(--white);
  }
  .center-notes textarea:focus { outline: none; border-color: var(--violet); }

  .total-section {
    background: var(--dark); color: var(--white);
    padding: 16px 20px; text-align: right; font-size: 20px; font-weight: bold;
    position: relative; overflow: hidden;
  }
  .total-section::before {
    content: '';
    position: absolute; top: 0; left: 0; bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--violet), var(--green-a));
  }

  .payment-bar {
    background: var(--white); border-top: 1px solid var(--border);
    padding: 12px 16px; display: flex; gap: 9px;
  }

  .right-panel { background: var(--white); display: flex; flex-direction: column; overflow: hidden; }

  .right-panel-header {
    background: var(--dark); padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center; gap: 8px;
    flex-shrink: 0; position: relative;
  }
  .right-panel-header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--green-a), var(--cyan-a));
    opacity: 0.5;
  }

  .btn-historial {
    padding: 5px 10px;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
    border-radius: 5px; color: var(--white); font-size: 11px; font-weight: bold;
    cursor: pointer; white-space: nowrap; transition: all 0.2s;
  }
  .btn-historial:hover { background: rgba(255,255,255,0.25); }

  .products-grid {
    flex: 1; min-height: 0;
    padding: 12px;
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 8px; overflow-y: auto; align-content: start;
  }

  .quick-product {
    background: var(--white); border: 1px solid var(--border);
    border-radius: 6px; padding: 8px; text-align: center;
    cursor: pointer; transition: all 0.2s;
    box-shadow: var(--shadow); height: 70px;
    display: flex; flex-direction: column; justify-content: center;
    font-size: 11px; position: relative; overflow: hidden;
  }
  .quick-product:hover {
    background: var(--green-a); color: var(--white);
    border-color: var(--green-a);
    transform: translateY(-1px); box-shadow: var(--shadow-lg);
  }
  .quick-product-name { font-weight: bold; margin-bottom: 3px; line-height: 1.2; }
  .quick-product-price { color: var(--muted); font-size: 10px; }
  .quick-product:hover .quick-product-price { color: rgba(255,255,255,0.85); }

  .quick-product-img {
    position: absolute; inset: 0; object-fit: cover;
    width: 100%; height: 100%; opacity: 0.15; border-radius: 6px; pointer-events: none;
  }
  .quick-product:hover .quick-product-img { opacity: 0.08; }

  .quick-badge {
    position: absolute; top: 3px; right: 3px;
    background: var(--violet); color: var(--white);
    border-radius: 50%; width: 18px; height: 18px;
    font-size: 10px; font-weight: bold;
    display: none; align-items: center; justify-content: center; z-index: 2;
  }

  .quick-add-btn {
    background: #f8f9fa; border: 2px dashed var(--border);
    border-radius: 6px; height: 70px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px; cursor: pointer; transition: all 0.2s;
    font-size: 11px; color: var(--muted);
  }
  .quick-add-btn:hover { border-color: var(--violet); color: var(--violet); background: var(--violet-bg); }
  .quick-add-btn .plus { font-size: 22px; line-height: 1; }

  .right-panel-footer {
    flex-shrink: 0; padding: 10px 12px;
    border-top: 1px solid var(--border);
    background: var(--white); display: flex;
  }

  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 1000;
    align-items: center; justify-content: center;
  }
  .modal-overlay.open { display: flex; }
  .modal {
    background: var(--white); border-radius: 8px;
    width: 680px; max-width: 95vw; max-height: 85vh;
    display: flex; flex-direction: column;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25); overflow: hidden;
  }
  .modal.modal-sm { width: 460px; }
  .modal-header {
    background: var(--dark); color: var(--white);
    padding: 14px 20px;
    display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
    position: relative;
  }
  .modal-header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--violet), var(--cyan-a), var(--green-a));
    opacity: 0.6;
  }
  .modal-header h2 { font-size: 15px; }
  .modal-close { background: none; border: none; color: var(--white); font-size: 20px; cursor: pointer; opacity: 0.8; transition: opacity 0.2s; }
  .modal-close:hover { opacity: 1; }

  .modal-summary {
    background: #f8f9fa; padding: 12px 20px;
    display: flex; gap: 30px; border-bottom: 1px solid var(--border); flex-shrink: 0;
  }
  .summary-stat { display: flex; flex-direction: column; gap: 2px; }
  .summary-stat .lbl { font-size: 10px; color: var(--muted); text-transform: uppercase; font-weight: bold; }
  .summary-stat .val { font-size: 18px; font-weight: bold; color: var(--violet); }
  .modal-body { overflow-y: auto; flex: 1; padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }

  .sale-card { border: 1px solid var(--border); border-radius: 6px; overflow: hidden; box-shadow: var(--shadow); }
  .sale-card-header { background: #f8f9fa; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; }
  .sale-card-header:hover { background: #edf2f7; }
  .sale-info { display: flex; gap: 14px; align-items: center; }
  .sale-num  { font-weight: bold; font-size: 13px; color: var(--violet); }
  .sale-time { font-size: 12px; color: var(--muted); }
  .sale-method { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
  .sale-method.efectivo    { background: rgba(14,168,122,0.12); color: var(--green-a); border: 1px solid rgba(14,168,122,0.25); }
  .sale-method.mercadopago { background: var(--cyan-bg); color: var(--cyan-a); border: 1px solid rgba(14,165,201,0.25); }
  .sale-method.tarjeta     { background: var(--violet-bg); color: var(--violet); border: 1px solid rgba(124,92,191,0.25); }
  .sale-method.dividido    { background: #fef9ec; color: #9a6400; border: 1px solid #f9e79f; }
  .sale-total { font-weight: bold; font-size: 14px; }
  .sale-chevron { color: var(--muted); font-size: 12px; transition: transform 0.2s; }
  .sale-card.expanded .sale-chevron { transform: rotate(180deg); }
  .sale-card-body { display: none; padding: 10px 14px; border-top: 1px solid #eee; }
  .sale-card.expanded .sale-card-body { display: block; }
  .sale-detail-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .sale-detail-table th { text-align: left; padding: 5px 6px; color: var(--muted); font-size: 10px; text-transform: uppercase; border-bottom: 1px solid #eee; }
  .sale-detail-table td { padding: 5px 6px; border-bottom: 1px solid #f5f5f5; }
  .sale-notes { margin-top: 8px; font-size: 11px; color: var(--muted); font-style: italic; }

  .prod-search-bar { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; gap: 8px; flex-shrink: 0; }
  .prod-search-bar input { flex: 1; padding: 8px 12px; border: 1px solid var(--border); border-radius: 4px; font-size: 14px; }
  .prod-search-bar input:focus { outline: none; border-color: var(--violet); }
  .prod-list { overflow-y: auto; flex: 1; }
  .prod-row { padding: 12px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 14px; cursor: pointer; transition: background 0.15s; }
  .prod-row:hover { background: var(--violet-bg); }
  .prod-code { font-size: 11px; color: var(--muted); width: 44px; flex-shrink: 0; }
  .prod-name { flex: 1; font-size: 14px; font-weight: 500; }
  .prod-price { font-size: 13px; font-weight: bold; color: var(--green-a); white-space: nowrap; }

  .add-quick-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; overflow-y: auto; flex: 1; }
  .add-quick-selected { background: #f8f9fa; border: 1px solid var(--border); border-radius: 6px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; }
  .add-quick-selected .prod-emoji { font-size: 26px; }
  .add-quick-selected .prod-info { flex: 1; }
  .add-quick-selected .prod-info strong { display: block; font-size: 14px; }
  .add-quick-selected .prod-info span { font-size: 12px; color: var(--muted); }

  .img-upload-area { border: 2px dashed var(--border); border-radius: 6px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; }
  .img-upload-area:hover { border-color: var(--violet); background: var(--violet-bg); }
  .img-upload-area input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
  .img-upload-area .upload-label { font-size: 13px; color: var(--muted); pointer-events: none; }
  .img-upload-area img { max-height: 100px; border-radius: 4px; display: none; }
  .img-upload-area.has-img img { display: block; margin: 0 auto; }
  .img-upload-area.has-img .upload-label { display: none; }

  .modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0; }
  .btn-modal { padding: 9px 20px; border-radius: 5px; border: none; font-weight: bold; font-size: 13px; cursor: pointer; transition: all 0.2s; }
  .btn-modal.primary { background: var(--violet); color: var(--white); }
  .btn-modal.primary:hover { background: var(--violet-l); }
  .btn-modal.secondary { background: var(--light); color: var(--text); border: 1px solid var(--border); }
  .btn-modal.secondary:hover { background: var(--border); }

  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: #f1f1f1; }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
  ::-webkit-scrollbar-thumb:hover { background: var(--muted); }

  @media (max-width: 1024px) {
    .main-container { grid-template-columns: 220px 1fr 280px; }
    .products-grid { grid-template-columns: repeat(2, 1fr); }
  }
</style>
</head>
<body>

<div class="header">
  <div class="header-left"><span class="rocket">🚀</span> POS Mostrador</div>
  <div class="header-right">
    <span id="fechaHdr">📅</span>
    <span id="horaHdr">⏰</span>
    <span>👤 Usuario: Barbara</span>
  </div>
</div>

<div class="main-container">

  <div class="left-panel">
    <div class="field-group">
      <label class="field-label">Código de Producto</label>
      <div class="input-with-icon">
        <input type="number" class="field-input" id="codigoProducto" placeholder="ej: 1001" min="0">
        <button class="input-lupa" id="btnLupa" title="Buscar producto">🔍</button>
      </div>
    </div>
    <div class="field-group">
      <label class="field-label">Descripción</label>
      <div class="desc-display empty" id="descDisplay">— sin producto —</div>
    </div>
    <div class="field-group">
      <label class="field-label">Precio Unitario</label>
      <div class="price-display" id="precioDisplay">$ —</div>
      <span class="price-label-note">🔒 Precio desde base de datos</span>
    </div>
    <div class="field-group">
      <label class="field-label">Cantidad</label>
      <input type="number" class="field-input qty-input-plain" id="cantidadInput" value="1" min="1">
    </div>
    <div class="btn-row">
      <button class="ubtn borrar"   id="btnBorrarItem"><span class="icon">🗑</span>Borrar Ítem</button>
      <button class="ubtn cancelar" id="btnCancelarVenta"><span class="icon">❌</span>Cancelar</button>
    </div>
    <div class="section-divider">Gestión</div>
    <div class="btn-row">
      <button class="ubtn inicio-caja" id="btnInicioCaja"><span class="icon">🏦</span>Inicio de Caja</button>
      <button class="ubtn fichar"      id="btnFichar"><span class="icon">🕐</span>Fichar</button>
    </div>
    <div class="btn-row">
      <button class="ubtn egresos"     id="btnEgresos"><span class="icon">💸</span>Egresos</button>
      <button class="ubtn caja-fuerte" id="btnCajaFuerte"><span class="icon">🔒</span>Caja Fuerte</button>
    </div>
  </div>

  <div class="center-panel">
    <div class="panel-header">
      <div class="panel-title">Ticket de Venta</div>
      <div class="item-count" id="itemCount">0 ítems</div>
    </div>
    <div class="table-container">
      <table class="products-table">
        <thead>
          <tr>
            <th style="width:43%">Descripción</th>
            <th style="width:15%">Cant.</th>
            <th style="width:20%">Precio</th>
            <th style="width:17%">Total</th>
            <th style="width:5%"></th>
          </tr>
        </thead>
        <tbody id="ticketBody"></tbody>
      </table>
    </div>
    <div class="center-notes">
      <div class="center-notes-label">📝 Notas / Observaciones</div>
      <textarea id="notasVenta" placeholder="Ingresá notas opcionales sobre la venta..."></textarea>
    </div>
    <div class="total-section">
      TOTAL: $ <span id="totalAmount">0,00</span>
    </div>
    <div class="payment-bar">
      <button class="ubtn dividido"    id="payDividido"><span class="icon">🔀</span>Cobro Dividido</button>
      <button class="ubtn mercadopago" id="payMP"><span class="icon">📱</span>MercadoPago</button>
      <button class="ubtn tarjeta"     id="payTarjeta"><span class="icon">💳</span>Tarjeta</button>
      <button class="ubtn efectivo"    id="payEfectivo"><span class="icon">💵</span>Efectivo</button>
    </div>
  </div>

  <div class="right-panel">
    <div class="right-panel-header">
      <div class="panel-title">Accesos Rápidos</div>
      <button class="btn-historial" id="btnHistorial">📋 Libro Diario</button>
    </div>
    <div class="products-grid" id="productosGrid"></div>

    <div class="right-panel-footer">
      <a class="ubtn planilla" id="btnPlanilla" href="/321POS/public/planilla/pos" style="text-decoration:none;">
        <span class="icon">📋</span>Planilla de Caja
      </a>
    </div>
  </div>

</div>

<!-- ══ MODAL HISTORIAL ══ -->
<div class="modal-overlay" id="modalHistorial">
  <div class="modal">
    <div class="modal-header">
      <h2>📋 Ventas del Turno</h2>
      <button class="modal-close" id="closeHistorial">✕</button>
    </div>
    <div class="modal-summary" id="modalSummary"></div>
    <div class="modal-body"    id="modalBody"></div>
  </div>
</div>

<!-- ══ MODAL BÚSQUEDA ══ -->
<div class="modal-overlay" id="modalBusqueda">
  <div class="modal modal-sm" style="max-height:70vh;">
    <div class="modal-header">
      <h2>🔍 Buscar Producto</h2>
      <button class="modal-close" id="closeBusqueda">✕</button>
    </div>
    <div class="prod-search-bar">
      <input type="text" id="busquedaInput" placeholder="Escribí nombre o código...">
    </div>
    <div class="prod-list" id="busquedaList"></div>
  </div>
</div>

<!-- ══ MODAL AGREGAR ACCESO ══ -->
<div class="modal-overlay" id="modalAgregarAcceso">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h2>➕ Nuevo Acceso Rápido</h2>
      <button class="modal-close" id="closeAgregarAcceso">✕</button>
    </div>
    <div id="pasoSeleccion">
      <div class="prod-search-bar">
        <input type="text" id="busquedaAccesoInput" placeholder="Buscá el producto...">
      </div>
      <div class="prod-list" id="busquedaAccesoList"></div>
    </div>
    <div id="pasoImagen" style="display:none; flex-direction:column; flex:1; overflow:hidden;">
      <div class="add-quick-body">
        <div class="add-quick-selected" id="accesoSeleccionado"></div>
        <div>
          <div class="field-label" style="margin-bottom:8px;">Imagen del botón (opcional)</div>
          <div class="img-upload-area" id="imgUploadArea">
            <input type="file" id="imgFileInput" accept="image/*">
            <img id="imgPreview" src="" alt="">
            <div class="upload-label">
              🖼 Hacé click o arrastrá una imagen<br>
              <span style="font-size:11px;color:#aaa;">PNG, JPG — se mostrará de fondo en el botón</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-modal secondary" id="btnVolverSeleccion">← Volver</button>
        <button class="btn-modal primary"   id="btnConfirmarAcceso">✅ Agregar acceso</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ MODAL PLANILLA (DEBE ESTAR ANTES DEL SCRIPT PARA QUE EXISTAN LOS IDS) -->
<div id="planillaOverlay"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:99999; align-items:center; justify-content:center; padding:18px;">
  <div style="width:92vw; height:88vh; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 18px 60px rgba(0,0,0,.35); position:relative;">
    <iframe id="planillaFrame" src="" style="width:100%; height:100%; border:0; display:block;"></iframe>
  </div>
</div>

<?php include __DIR__ . '/../partials/gestion.modals.php'; ?>

<script>
// ---------- DB DEMO ----------
const productosDB = {
  1001:{nombre:'☕ Café Americano',precio:1500},1002:{nombre:'🥛 Capuchino',precio:1900},
  1003:{nombre:'🍵 Té',precio:1200},1004:{nombre:'🥐 Medialuna',precio:900},
  1005:{nombre:'🥪 Sándwich',precio:3200},1006:{nombre:'💧 Agua Mineral',precio:1000},
  1007:{nombre:'🥤 Gaseosa',precio:1400},1008:{nombre:'🧃 Jugo',precio:1600},
  1009:{nombre:'🍕 Pizza',precio:5500},1010:{nombre:'🥟 Empanada',precio:1100},
  1011:{nombre:'🥤 Milkshake',precio:2600},1012:{nombre:'🍦 Helado',precio:3800},
  1013:{nombre:'🍫 Chocolate',precio:1300},1014:{nombre:'☕ Espresso',precio:2100},
  1015:{nombre:'🥛 Latte',precio:2000},1016:{nombre:'🍮 Flan',precio:1700},
  1017:{nombre:'🧁 Budín',precio:1600},1018:{nombre:'🍞 Tostado',precio:2900},
  1019:{nombre:'🥗 Ensalada',precio:4200},1020:{nombre:'🍔 Burger',precio:5900},
};

let accesosRapidos = Object.keys(productosDB).map(c => ({ codigo: parseInt(c), imgDataUrl: null }));
let ticket = [];
let filaSeleccionada = null;
let productoSeleccionado = null;
let historialVentas = [];
let contadorVenta = 1;

function formatNum(n){ return n.toLocaleString('es-AR',{minimumFractionDigits:2}); }
function horaActual(){ return new Date().toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }

// ---------- Ticket ----------
function renderTicket(){
  const tbody = document.getElementById('ticketBody');
  tbody.innerHTML = '';

  ticket.forEach((item, idx) => {
    const tr = document.createElement('tr');
    if (idx === filaSeleccionada) tr.classList.add('selected');

    tr.innerHTML = `
      <td>${item.nombre}</td>
      <td><input type="number" class="qty-cell" value="${item.cantidad}" min="1" data-idx="${idx}"></td>
      <td>$ ${formatNum(item.precio)}</td>
      <td class="tc-${idx}">$ ${formatNum(item.precio*item.cantidad)}</td>
      <td><span class="delete-item" data-idx="${idx}">🗑</span></td>
    `;

    tr.addEventListener('click', (e) => {
      if (e.target.classList.contains('delete-item') || e.target.classList.contains('qty-cell')) return;
      filaSeleccionada = (filaSeleccionada === idx) ? null : idx;
      renderTicket();
    });

    tbody.appendChild(tr);
  });

  tbody.querySelectorAll('.qty-cell').forEach(inp => {
    inp.addEventListener('click', e => e.stopPropagation());
    inp.addEventListener('change', function(){
      const i = parseInt(this.dataset.idx);
      const nv = parseInt(this.value);
      if (isNaN(nv) || nv < 1) { this.value = ticket[i].cantidad; return; }
      ticket[i].cantidad = nv;
      const tc = document.querySelector(`.tc-${i}`);
      if (tc) tc.textContent = '$ ' + formatNum(ticket[i].precio * nv);
      recalcularTotal();
      actualizarBadges();
    });
    inp.addEventListener('keydown', e => { if (e.key === 'Enter') inp.blur(); });
  });

  recalcularTotal();
  actualizarBadges();
}

function recalcularTotal(){
  document.getElementById('totalAmount').textContent =
    formatNum(ticket.reduce((s,i)=>s+i.precio*i.cantidad,0));
  const ti = ticket.reduce((s,i)=>s+i.cantidad,0);
  document.getElementById('itemCount').textContent =
    ti + ' ítem' + (ti!==1?'s':'');
}

function agregarAlTicket(codigo, cantidad){
  const prod = productosDB[codigo];
  if(!prod) return;

  const ex = ticket.find(i=>i.codigo===codigo);
  if(ex) ex.cantidad += cantidad;
  else ticket.push({ codigo, nombre: prod.nombre, precio: prod.precio, cantidad });

  renderTicket();
}

document.getElementById('ticketBody').addEventListener('click', function(e){
  if(!e.target.classList.contains('delete-item')) return;
  const idx = parseInt(e.target.dataset.idx);
  ticket.splice(idx, 1);
  if (filaSeleccionada === idx) filaSeleccionada = null;
  else if (filaSeleccionada > idx) filaSeleccionada--;
  renderTicket();
});

document.getElementById('btnBorrarItem').addEventListener('click', () => {
  if(!ticket.length){ alert('No hay ítems.'); return; }
  const idx = (filaSeleccionada !== null) ? filaSeleccionada : ticket.length - 1;
  if(ticket[idx].cantidad > 1) ticket[idx].cantidad--;
  else{
    ticket.splice(idx, 1);
    if (filaSeleccionada === idx) filaSeleccionada = null;
    else if (filaSeleccionada > idx) filaSeleccionada--;
  }
  renderTicket();
});

function cancelarVenta(){
  ticket = [];
  filaSeleccionada = null;
  productoSeleccionado = null;
  document.getElementById('codigoProducto').value = '';
  limpiarDescripcion();
  document.getElementById('cantidadInput').value = 1;
  document.getElementById('notasVenta').value = '';
  renderTicket();
}
document.getElementById('btnCancelarVenta').addEventListener('click', cancelarVenta);

// ---------- Descripción / Código ----------
function limpiarDescripcion(){
  const d = document.getElementById('descDisplay');
  d.textContent = '— sin producto —';
  d.className = 'desc-display empty';
  document.getElementById('precioDisplay').textContent = '$ —';
  productoSeleccionado = null;
}

function mostrarDescripcion(codigo){
  const prod = productosDB[parseInt(codigo)];
  const d = document.getElementById('descDisplay');

  if(prod){
    productoSeleccionado = parseInt(codigo);
    d.textContent = prod.nombre;
    d.className = 'desc-display found';
    document.getElementById('precioDisplay').textContent = '$ ' + formatNum(prod.precio);
  } else {
    productoSeleccionado = null;
    d.textContent = codigo ? '✗ Código no encontrado' : '— sin producto —';
    d.className = codigo ? 'desc-display not-found' : 'desc-display empty';
    document.getElementById('precioDisplay').textContent = '$ —';
  }
}

document.getElementById('codigoProducto').addEventListener('input', function(){
  mostrarDescripcion(this.value);
});

document.getElementById('codigoProducto').addEventListener('keydown', function(e){
  if(e.key === 'Enter' && productoSeleccionado){
    document.getElementById('cantidadInput').focus();
    document.getElementById('cantidadInput').select();
  }
});

document.getElementById('cantidadInput').addEventListener('keydown', function(e){
  if(e.key === 'Enter'){
    if(!productoSeleccionado){ alert('Buscá un código válido.'); return; }
    agregarAlTicket(productoSeleccionado, parseInt(this.value) || 1);
    document.getElementById('codigoProducto').value = '';
    limpiarDescripcion();
    this.value = 1;
    document.getElementById('codigoProducto').focus();
  }
});

// ---------- Accesos Rápidos ----------
function buildProductosGrid(){
  const grid = document.getElementById('productosGrid');
  grid.innerHTML = '';

  accesosRapidos.forEach(ac => {
    const prod = productosDB[ac.codigo];
    if(!prod) return;

    const div = document.createElement('div');
    div.className = 'quick-product';
    div.innerHTML = `
      ${ac.imgDataUrl ? `<img class="quick-product-img" src="${ac.imgDataUrl}">` : ''}
      <span class="quick-badge" id="badge-${ac.codigo}"></span>
      <div class="quick-product-name">${prod.nombre}</div>
      <div class="quick-product-price">$ ${formatNum(prod.precio)}</div>
    `;
    div.addEventListener('click', () => agregarAlTicket(ac.codigo, 1));
    grid.appendChild(div);
  });

  const addBtn = document.createElement('div');
  addBtn.className = 'quick-add-btn';
  addBtn.innerHTML = '<span class="plus">＋</span><span>Nuevo acceso</span>';
  addBtn.addEventListener('click', abrirModalAgregarAcceso);
  grid.appendChild(addBtn);
}

function actualizarBadges(){
  document.querySelectorAll('.quick-badge').forEach(b => b.style.display = 'none');
  ticket.forEach(item => {
    const b = document.getElementById('badge-' + item.codigo);
    if (b){
      b.textContent = item.cantidad;
      b.style.display = 'flex';
    }
  });
}
buildProductosGrid();

// ---------- Pagos ----------
const pagos = {
  payEfectivo:{label:'Efectivo',css:'efectivo'},
  payMP:{label:'MercadoPago',css:'mercadopago'},
  payTarjeta:{label:'Tarjeta',css:'tarjeta'},
  payDividido:{label:'Cobro Dividido',css:'dividido'}
};

Object.entries(pagos).forEach(([id,meta]) => {
  document.getElementById(id).addEventListener('click', function(){
    if(!ticket.length){ alert('El ticket está vacío.'); return; }
    const total = ticket.reduce((s,i)=>s+i.precio*i.cantidad,0);
    historialVentas.unshift({
      numero: contadorVenta++,
      hora: horaActual(),
      metodo: meta.label,
      metodoCss: meta.css,
      items: ticket.map(i=>({...i})),
      total,
      totalItems: ticket.reduce((s,i)=>s+i.cantidad,0),
      notas: document.getElementById('notasVenta').value.trim()
    });
    cancelarVenta();

    document.querySelectorAll('.payment-bar .ubtn').forEach(b=>b.style.opacity='0.55');
    this.style.opacity='1';
    this.style.transform='scale(1.03)';
    setTimeout(()=> {
      document.querySelectorAll('.payment-bar .ubtn').forEach(b=>{
        b.style.opacity='';
        b.style.transform='';
      });
    }, 1000);
  });
});

// ---------- Historial ----------
document.getElementById('btnHistorial')?.addEventListener('click', (e) => {
  e.preventDefault();
  openOverlay("/321POS/public/cajero/libroDiario"); // Movimientos reales
});
document.getElementById('closeHistorial').addEventListener('click', () =>
  document.getElementById('modalHistorial').classList.remove('open')
);
document.getElementById('modalHistorial').addEventListener('click', function(e){
  if(e.target === this) this.classList.remove('open');
});

function renderHistorial(){
  const body = document.getElementById('modalBody');
  const sum  = document.getElementById('modalSummary');
  body.innerHTML = '';

  if(!historialVentas.length){
    body.innerHTML = '<p style="color:var(--muted);text-align:center;padding:30px 0;">No hay ventas en este turno.</p>';
    sum.innerHTML = '';
    return;
  }

  const tot = historialVentas.reduce((s,v)=>s+v.total,0);
  sum.innerHTML = `
    <div class="summary-stat"><span class="lbl">Ventas</span><span class="val">${historialVentas.length}</span></div>
    <div class="summary-stat"><span class="lbl">Ítems</span><span class="val">${historialVentas.reduce((s,v)=>s+v.totalItems,0)}</span></div>
    <div class="summary-stat"><span class="lbl">Total turno</span><span class="val">$ ${formatNum(tot)}</span></div>
  `;

  historialVentas.forEach(v => {
    const card = document.createElement('div');
    card.className = 'sale-card';

    card.innerHTML = `
      <div class="sale-card-header">
        <div class="sale-info">
          <span class="sale-num">Venta #${v.numero}</span>
          <span class="sale-time">⏰ ${v.hora}</span>
          <span class="sale-method ${v.metodoCss}">${v.metodo}</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
          <span class="sale-total">$ ${formatNum(v.total)}</span>
          <span class="sale-chevron">▼</span>
        </div>
      </div>
      <div class="sale-card-body">
        <table class="sale-detail-table">
          <thead><tr><th>Descripción</th><th>Cant.</th><th>Precio</th><th>Total</th></tr></thead>
          <tbody>
            ${v.items.map(it=>`
              <tr>
                <td>${it.nombre}</td>
                <td>${it.cantidad}</td>
                <td>$ ${formatNum(it.precio)}</td>
                <td>$ ${formatNum(it.precio*it.cantidad)}</td>
              </tr>`).join('')}
          </tbody>
        </table>
        ${v.notas ? `<div class="sale-notes">📝 ${v.notas}</div>` : ''}
      </div>
    `;
    card.querySelector('.sale-card-header').addEventListener('click', () => card.classList.toggle('expanded'));
    body.appendChild(card);
  });
}

// ---------- Buscador (lupa + modal) ----------
function renderListaBusqueda(filtro, contenedor, alSeleccionar){
  contenedor.innerHTML = '';
  const f = filtro.toLowerCase();

  Object.entries(productosDB)
    .filter(([cod,p]) => !f || p.nombre.toLowerCase().includes(f) || cod.includes(f))
    .forEach(([cod,prod]) => {
      const row = document.createElement('div');
      row.className = 'prod-row';
      row.innerHTML = `
        <span class="prod-code">${cod}</span>
        <span class="prod-name">${prod.nombre}</span>
        <span class="prod-price">$ ${formatNum(prod.precio)}</span>
      `;
      row.addEventListener('click', () => alSeleccionar(parseInt(cod), prod));
      contenedor.appendChild(row);
    });

  if (!contenedor.children.length){
    contenedor.innerHTML = '<p style="padding:20px;color:var(--muted);text-align:center;">Sin resultados</p>';
  }
}

document.getElementById('btnLupa').addEventListener('click', () => {
  document.getElementById('busquedaInput').value = '';
  renderListaBusqueda('', document.getElementById('busquedaList'), (codigo) => {
    document.getElementById('codigoProducto').value = codigo;
    mostrarDescripcion(codigo);
    document.getElementById('modalBusqueda').classList.remove('open');
    document.getElementById('cantidadInput').focus();
    document.getElementById('cantidadInput').select();
  });
  document.getElementById('modalBusqueda').classList.add('open');
  setTimeout(()=>document.getElementById('busquedaInput').focus(),100);
});

document.getElementById('closeBusqueda').addEventListener('click', () =>
  document.getElementById('modalBusqueda').classList.remove('open')
);
document.getElementById('modalBusqueda').addEventListener('click', function(e){
  if(e.target === this) this.classList.remove('open');
});
document.getElementById('busquedaInput').addEventListener('input', function(){
  renderListaBusqueda(this.value, document.getElementById('busquedaList'), (codigo) => {
    document.getElementById('codigoProducto').value = codigo;
    mostrarDescripcion(codigo);
    document.getElementById('modalBusqueda').classList.remove('open');
    document.getElementById('cantidadInput').focus();
    document.getElementById('cantidadInput').select();
  });
});

// ---------- Modal Agregar Acceso Rápido ----------
let accesoTempCodigo = null;
let accesoTempImg = null;

function abrirModalAgregarAcceso(){
  accesoTempCodigo = null;
  accesoTempImg = null;

  document.getElementById('pasoSeleccion').style.display = 'flex';
  document.getElementById('pasoSeleccion').style.flexDirection = 'column';
  document.getElementById('pasoImagen').style.display = 'none';

  document.getElementById('busquedaAccesoInput').value = '';
  document.getElementById('imgUploadArea').classList.remove('has-img');
  document.getElementById('imgPreview').src = '';

  renderListaBusqueda('', document.getElementById('busquedaAccesoList'), seleccionarProductoAcceso);

  document.getElementById('modalAgregarAcceso').classList.add('open');
  setTimeout(()=>document.getElementById('busquedaAccesoInput').focus(),100);
}

function seleccionarProductoAcceso(codigo, prod){
  accesoTempCodigo = codigo;

  document.getElementById('pasoSeleccion').style.display = 'none';
  document.getElementById('pasoImagen').style.display = 'flex';

  document.getElementById('accesoSeleccionado').innerHTML = `
    <span class="prod-emoji">${prod.nombre.split(' ')[0]}</span>
    <div class="prod-info">
      <strong>${prod.nombre}</strong>
      <span>Código ${codigo} · $ ${formatNum(prod.precio)}</span>
    </div>
  `;
}

document.getElementById('btnVolverSeleccion').addEventListener('click', () => {
  document.getElementById('pasoSeleccion').style.display = 'flex';
  document.getElementById('pasoSeleccion').style.flexDirection = 'column';
  document.getElementById('pasoImagen').style.display = 'none';
});

document.getElementById('imgFileInput').addEventListener('change', function(){
  const file = this.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    accesoTempImg = e.target.result;
    document.getElementById('imgPreview').src = accesoTempImg;
    document.getElementById('imgUploadArea').classList.add('has-img');
  };
  reader.readAsDataURL(file);
});

document.getElementById('btnConfirmarAcceso').addEventListener('click', () => {
  if(!accesoTempCodigo) return;

  const ex = accesosRapidos.find(a=>a.codigo===accesoTempCodigo);
  if(ex){
    if(accesoTempImg) ex.imgDataUrl = accesoTempImg;
  } else {
    accesosRapidos.push({ codigo: accesoTempCodigo, imgDataUrl: accesoTempImg });
  }

  buildProductosGrid();
  document.getElementById('modalAgregarAcceso').classList.remove('open');
});

document.getElementById('closeAgregarAcceso').addEventListener('click', () =>
  document.getElementById('modalAgregarAcceso').classList.remove('open')
);
document.getElementById('modalAgregarAcceso').addEventListener('click', function(e){
  if(e.target === this) this.classList.remove('open');
});
document.getElementById('busquedaAccesoInput').addEventListener('input', function(){
  renderListaBusqueda(this.value, document.getElementById('busquedaAccesoList'), seleccionarProductoAcceso);
});

// ---------- Gestión (modales del partial) ----------
document.getElementById('btnInicioCaja')?.addEventListener('click', (e) => { e.preventDefault(); abrirCajaModal(); });
document.getElementById('btnEgresos')?.addEventListener('click', (e) => { e.preventDefault(); abrirGastoModal(); });
document.getElementById('btnCajaFuerte')?.addEventListener('click', (e) => { e.preventDefault(); abrirCajaFuerteModal(); });
document.getElementById('btnFichar')?.addEventListener('click', (e) => { e.preventDefault(); alert('🕐 Fichar (pendiente)'); });

// ---------- ventas del turno ----------
function openOverlay(url){
  const overlay = document.getElementById('planillaOverlay');
  const frame = document.getElementById('planillaFrame');
  frame.src = url;
  overlay.style.display = "flex";
}

// ---------- Modal Planilla (iframe) ----------
function openPlanilla(){
  const overlay = document.getElementById('planillaOverlay');
  const frame = document.getElementById('planillaFrame');
  frame.src = "/321POS/public/planilla/pos";
  overlay.style.display = "flex";
}
function closePlanilla(){
  const overlay = document.getElementById('planillaOverlay');
  const frame = document.getElementById('planillaFrame');
  overlay.style.display = "none";
  frame.src = "";
}

document.getElementById('btnPlanilla').addEventListener('click', (e) => {
  e.preventDefault();
  openPlanilla();
});

// cerrar tocando el fondo
document.getElementById('planillaOverlay').addEventListener('click', (e) => {
  if (e.target.id === "planillaOverlay") closePlanilla();
});

// cerrar desde el iframe (botón Cerrar de la planilla)
window.addEventListener('message', (e) => {
  if (e.data && e.data.type === 'CLOSE_PLANILLA') closePlanilla();
});

// cerrar con ESC (planilla)
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closePlanilla();
});

// ---------- Reloj ----------
function actualizarReloj(){
  const a=new Date();
  document.getElementById('horaHdr').textContent='⏰ '+a.toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit'});
  document.getElementById('fechaHdr').textContent='📅 '+a.toLocaleDateString('es-AR',{weekday:'short',day:'2-digit',month:'2-digit',year:'numeric'});
}
actualizarReloj();
setInterval(actualizarReloj, 30000);

// Init
renderTicket();

// ===== btnHistorial -> abrir Libro Diario en overlay =====
document.getElementById('btnHistorial')?.addEventListener('click', (e) => {
  e.preventDefault();
  openOverlay("/321POS/public/cajero/libroDiario");
});
</script>

</body>
</html>
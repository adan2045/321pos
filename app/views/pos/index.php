<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>POS Mostrador - Sistema de Ventas</title>
<style>
  :root {
    --primary: #2c3e50; --secondary: #34495e; --success: #27ae60;
    --warning: #f39c12; --danger: #e74c3c; --info: #3498db;
    --light: #ecf0f1; --dark: #2c3e50; --white: #ffffff;
    --border: #bdc3c7; --text: #2c3e50; --muted: #7f8c8d;
    --shadow: 0 2px 4px rgba(0,0,0,0.1); --shadow-lg: 0 4px 8px rgba(0,0,0,0.15);
    --violet: #7c5cbf; --violet-l: #9b7fd4; --violet-bg: rgba(124,92,191,0.07);
    --green-a: #0ea87a; --green-bg: rgba(14,168,122,0.07);
    --cyan-a: #0ea5c9; --cyan-bg: rgba(14,165,201,0.07);
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f5f7; color: var(--text); height: 100vh; overflow: hidden; }

  .header { background: var(--dark); color: var(--white); padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; height: 50px; box-shadow: var(--shadow-lg); position: relative; }
  .header::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--violet), var(--cyan-a), var(--green-a)); opacity: 0.7; }
  .header-left { font-size: 16px; font-weight: bold; display: flex; align-items: center; gap: 8px; }
  .header-left .rocket { font-size: 18px; }
  .header-right { display: flex; gap: 20px; font-size: 14px; }

  .main-container { display: grid; grid-template-columns: 250px 1fr 300px; height: calc(100vh - 50px); }

  .left-panel {
  background: var(--white);
  border-right: 1px solid var(--border);
  padding: 10px 15px;
  display: flex; flex-direction: column; gap: 7px;
  overflow-y: auto;
}
  .field-group { display: flex; flex-direction: column; gap: 6px; }
  .field-label { font-size: 12px; font-weight: bold; color: var(--muted); text-transform: uppercase; }
  .input-with-icon { position: relative; display: flex; align-items: center; }
  .input-with-icon .field-input { padding-right: 36px; width: 100%; }
  .input-lupa { position: absolute; right: 8px; background: none; border: none; cursor: pointer; font-size: 16px; color: var(--muted); padding: 0; line-height: 1; transition: color 0.2s; }
  .input-lupa:hover { color: var(--cyan-a); }
  .field-input { padding: 10px; border: 1px solid var(--border); border-radius: 4px; font-size: 14px; background: var(--white); transition: border-color 0.2s, box-shadow 0.2s; }
  .field-input:focus { outline: none; border-color: var(--violet); box-shadow: 0 0 0 2px rgba(124,92,191,0.12); }
  .desc-display { padding: 10px; border: 1px solid var(--border); border-radius: 4px; font-size: 13px; font-weight: 600; background: #f0faf4; color: var(--dark); min-height: 38px; cursor: default; user-select: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .desc-display.empty     { color: var(--border); font-weight: normal; font-style: italic; }
  .desc-display.found     { color: var(--green-a); border-color: var(--green-a); background: var(--green-bg); }
  .desc-display.not-found { color: var(--danger); border-color: var(--danger); }
  .price-display { padding: 10px; border: 1px solid rgba(14,165,201,0.25); border-radius: 4px; font-size: 16px; font-weight: bold; text-align: right; background: var(--cyan-bg); color: var(--dark); cursor: default; user-select: none; }
  .price-label-note { font-size: 10px; color: var(--muted); font-style: italic; margin-top: -4px; }
  .qty-input-plain { width: 100%; text-align: center; font-weight: bold; }

  .btn-row { display: flex; gap: 9px; }
  .ubtn { flex: 1; padding: 12px 6px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.18s; font-size: 12px; text-align: center; line-height: 1.4; box-shadow: var(--shadow); display: flex; flex-direction: column; align-items: center; gap: 3px; border: 1px solid transparent; }
  .ubtn .icon { font-size: 16px; }
  .ubtn:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }
  .ubtn.borrar       { background: #fef0ee; border-color: #f1a9a0; color: #b03a2e; }
  .ubtn.borrar:hover { background: #fde0dd; }
  .ubtn.cancelar       { background: #f4f6f7; border-color: #bdc3c7; color: #5d6d7e; }
  .ubtn.cancelar:hover { background: #eaecee; }
  .ubtn.inicio-caja       { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); }
  .ubtn.inicio-caja:hover { background: rgba(124,92,191,0.13); }
  .ubtn.fichar            { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); }
  .ubtn.fichar:hover      { background: rgba(124,92,191,0.13); }
  .ubtn.ingresos          { background: #edfbf3; border-color: rgba(14,168,122,0.3); color: var(--green-a); }
  .ubtn.ingresos:hover    { background: rgba(14,168,122,0.15); }
  .ubtn.egresos           { background: #fef9ec; border-color: #f9e79f; color: #9a6400; }
  .ubtn.egresos:hover     { background: #fdf2ce; }
  .ubtn.caja-fuerte       { background: var(--cyan-bg); border-color: rgba(14,165,201,0.25); color: var(--cyan-a); }
  .ubtn.caja-fuerte:hover { background: rgba(14,165,201,0.13); }
  .ubtn.herramientas       { background: #f5f0ff; border-color: rgba(124,92,191,0.35); color: var(--violet); }
  .ubtn.herramientas:hover { background: rgba(124,92,191,0.15); }
  .ubtn.dividido       { background: #fef9ec; border-color: #f9e79f; color: #9a6400; }
  .ubtn.dividido:hover { background: #fdf2ce; }
  .ubtn.mercadopago       { background: var(--cyan-bg); border-color: rgba(14,165,201,0.25); color: var(--cyan-a); }
  .ubtn.mercadopago:hover { background: rgba(14,165,201,0.13); }
  .ubtn.qr       { background: #eef4ff; border-color: rgba(0,70,167,0.25); color: #0046a7; }
  .ubtn.qr:hover { background: rgba(0,70,167,0.08); }
  .ubtn.tarjeta       { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); }
  .ubtn.tarjeta:hover { background: rgba(124,92,191,0.13); }
  .ubtn.efectivo       { background: var(--green-bg); border-color: rgba(14,168,122,0.25); color: var(--green-a); }
  .ubtn.efectivo:hover { background: rgba(14,168,122,0.13); }
  .ubtn.planilla { background: var(--violet-bg); border-color: rgba(124,92,191,0.25); color: var(--violet); flex-direction: row; gap: 6px; padding: 11px 16px; font-size: 13px; flex: 1; }
  .ubtn.planilla:hover { background: rgba(124,92,191,0.13); }
  .ubtn.planilla .icon { font-size: 15px; }

  .section-divider { display: flex; align-items: center; gap: 8px; color: var(--border); font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
  .section-divider::before, .section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

  .center-panel { background: var(--white); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
  .panel-header { background: var(--dark); padding: 12px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: relative; }
  .panel-header::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--violet), var(--cyan-a)); opacity: 0.5; }
  .panel-title { font-size: 15px; font-weight: bold; color: var(--white); }
  .item-count { background: rgba(255,255,255,0.15); color: var(--white); padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
  .table-container { height: calc(6 * 49px); overflow-y: auto; flex-shrink: 0; }
  .products-table { width: 100%; border-collapse: collapse; }
  .products-table thead th { background: #f8f9fa; padding: 12px 8px; text-align: left; font-size: 11px; font-weight: bold; color: var(--muted); text-transform: uppercase; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 1; }
  .products-table tbody td { padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
  .products-table tbody tr { cursor: pointer; transition: background 0.15s; }
  .products-table tbody tr:hover { background: rgba(124,92,191,0.05); }
  .products-table tbody tr.selected { background: rgba(124,92,191,0.09); outline: 2px solid rgba(124,92,191,0.35); outline-offset: -1px; }
  .products-table tbody tr.selected td { font-weight: 600; color: var(--violet); }
  .qty-cell { width: 55px; padding: 4px 6px; border: 1px solid transparent; border-radius: 4px; font-size: 13px; font-weight: bold; text-align: center; background: transparent; cursor: text; font-family: inherit; }
  .qty-cell:focus { outline: none; border-color: var(--violet); background: var(--white); }
  .delete-item { color: var(--danger); cursor: pointer; padding: 2px 6px; border-radius: 3px; transition: all 0.2s; }
  .delete-item:hover { background: var(--danger); color: var(--white); }
  .center-notes { padding: 10px 20px; background: #fafbfc; border-top: 1px solid #eee; display: flex; flex-direction: column; gap: 5px; }
  .center-notes-label { font-size: 11px; color: var(--muted); font-weight: bold; text-transform: uppercase; }
  .center-notes textarea { width: 100%; height: 62px; border: 1px solid var(--border); border-radius: 4px; padding: 6px 8px; font-size: 12px; resize: none; font-family: inherit; background: var(--white); }
  .center-notes textarea:focus { outline: none; border-color: var(--violet); }
  .total-section { background: var(--dark); color: var(--white); padding: 16px 20px; text-align: right; font-size: 20px; font-weight: bold; position: relative; overflow: hidden; }
  .total-section::before { content: ''; position: absolute; top: 0; left: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, var(--violet), var(--green-a)); }
  .payment-bar { background: var(--white); border-top: 1px solid var(--border); padding: 12px 16px; display: flex; gap: 9px; }

  .right-panel { background: var(--white); display: flex; flex-direction: column; overflow: hidden; }
  .right-panel-header { background: var(--dark); padding: 10px 14px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-shrink: 0; position: relative; }
  .right-panel-header::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--green-a), var(--cyan-a)); opacity: 0.5; }
  .btn-historial { padding: 5px 10px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 5px; color: var(--white); font-size: 11px; font-weight: bold; cursor: pointer; white-space: nowrap; transition: all 0.2s; }
  .btn-historial:hover { background: rgba(255,255,255,0.25); }
  .products-grid { flex: 1; min-height: 0; padding: 12px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; overflow-y: auto; align-content: start; }
  .quick-product { background: var(--white); border: 1px solid var(--border); border-radius: 6px; padding: 8px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow); height: 70px; display: flex; flex-direction: column; justify-content: center; font-size: 11px; position: relative; overflow: hidden; }
  .quick-product:hover { background: var(--green-a); color: var(--white); border-color: var(--green-a); transform: translateY(-1px); box-shadow: var(--shadow-lg); }
  .quick-product-name { font-weight: bold; margin-bottom: 3px; line-height: 1.2; }
  .quick-product-price { color: var(--muted); font-size: 10px; }
  .quick-product:hover .quick-product-price { color: rgba(255,255,255,0.85); }
  .quick-product-img { position: absolute; inset: 0; object-fit: cover; width: 100%; height: 100%; opacity: 0.15; border-radius: 6px; pointer-events: none; }
  .quick-product:hover .quick-product-img { opacity: 0.08; }
  .quick-badge { position: absolute; top: 3px; right: 3px; background: var(--violet); color: var(--white); border-radius: 50%; width: 18px; height: 18px; font-size: 10px; font-weight: bold; display: none; align-items: center; justify-content: center; z-index: 2; }
  .quick-add-btn { background: #f8f9fa; border: 2px dashed var(--border); border-radius: 6px; height: 70px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; transition: all 0.2s; font-size: 11px; color: var(--muted); }
  .quick-add-btn:hover { border-color: var(--violet); color: var(--violet); background: var(--violet-bg); }
  .quick-add-btn .plus { font-size: 22px; line-height: 1; }
  .right-panel-footer { flex-shrink: 0; padding: 10px 12px; border-top: 1px solid var(--border); background: var(--white); display: flex; }

  /* Modales */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; align-items: center; justify-content: center; }
  .modal-overlay.open { display: flex; }
  .modal { background: var(--white); border-radius: 8px; width: 680px; max-width: 95vw; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.25); overflow: hidden; }
  .modal.modal-sm { width: 460px; }
  .modal-header { background: var(--dark); color: var(--white); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; position: relative; }
  .modal-header::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--violet), var(--cyan-a), var(--green-a)); opacity: 0.6; }
  .modal-header h2 { font-size: 15px; }
  .modal-close { background: none; border: none; color: var(--white); font-size: 20px; cursor: pointer; opacity: 0.8; transition: opacity 0.2s; }
  .modal-close:hover { opacity: 1; }
  .modal-summary { background: #f8f9fa; padding: 12px 20px; display: flex; gap: 30px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
  .summary-stat { display: flex; flex-direction: column; gap: 2px; }
  .summary-stat .lbl { font-size: 10px; color: var(--muted); text-transform: uppercase; font-weight: bold; }
  .summary-stat .val { font-size: 18px; font-weight: bold; color: var(--violet); }
  .modal-body { overflow-y: auto; flex: 1; padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }
  .sale-card { border: 1px solid var(--border); border-radius: 6px; overflow: hidden; box-shadow: var(--shadow); }
  .sale-card-header { background: #f8f9fa; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; }
  .sale-card-header:hover { background: #edf2f7; }
  .sale-info { display: flex; gap: 14px; align-items: center; }
  .sale-num { font-weight: bold; font-size: 13px; color: var(--violet); }
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

  /* PIN Admin */
  #modalAdminPin .pin-display { display: flex; gap: 10px; justify-content: center; margin: 18px 0 10px; }
  #modalAdminPin .pin-dot { width: 14px; height: 14px; border-radius: 50%; border: 2px solid var(--violet); background: transparent; transition: background 0.2s; }
  #modalAdminPin .pin-dot.filled { background: var(--violet); }
  #modalAdminPin .pin-input { position: absolute; opacity: 0; pointer-events: none; width: 1px; }
  #modalAdminPin .pin-error { text-align: center; font-size: 13px; color: var(--danger); min-height: 20px; margin-bottom: 8px; font-weight: 600; opacity: 0; transition: opacity 0.2s; }
  #modalAdminPin .pin-error.show { opacity: 1; }
  #modalAdminPin .numpad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 0 20px 20px; }
  #modalAdminPin .np-btn { padding: 14px; font-size: 18px; font-weight: bold; border: 1px solid var(--border); border-radius: 8px; background: var(--white); cursor: pointer; transition: all 0.15s; font-family: inherit; }
  #modalAdminPin .np-btn:hover  { background: var(--violet-bg); border-color: var(--violet); color: var(--violet); }
  #modalAdminPin .np-btn:active { transform: scale(0.95); }
  #modalAdminPin .np-btn.del { background: #fef0ee; border-color: #f1a9a0; color: var(--danger); }
  #modalAdminPin .np-btn.ok  { background: var(--violet); color: #fff; border-color: var(--violet); grid-column: span 2; }
  #modalAdminPin .np-btn.ok:hover { background: var(--violet-l); }

  /* Herramientas */
  #modalHerramientas .tools-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 20px; }
  #modalHerramientas .tool-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 18px 10px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); cursor: pointer; transition: all 0.2s; font-size: 12px; font-weight: 600; color: var(--text); text-align: center; line-height: 1.3; box-shadow: var(--shadow); font-family: inherit; }
  #modalHerramientas .tool-btn .tb-icon { font-size: 26px; }
  #modalHerramientas .tool-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
  #modalHerramientas .tool-btn.t-estadisticas:hover { background: var(--cyan-bg); color: var(--cyan-a); border-color: var(--cyan-a); }
  #modalHerramientas .tool-btn.t-fichadas:hover { background: var(--violet-bg); color: var(--violet); border-color: var(--violet); }
  #modalHerramientas .tool-btn.t-precios:hover { background: var(--green-bg); color: var(--green-a); border-color: var(--green-a); }
  #modalHerramientas .tool-btn.t-ventas:hover { background: #fef9ec; color: #9a6400; border-color: #f9e79f; }
  #modalHerramientas .tool-btn.t-gastos:hover { background: #fef0ee; color: var(--danger); border-color: var(--danger); }
  #modalHerramientas .tool-btn.t-config:hover { background: #f4f5f7; color: var(--dark); }
  #modalHerramientas .admin-badge { background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3); color: #fff; font-size: 11px; padding: 2px 9px; border-radius: 10px; font-weight: 600; }

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
<body data-base="<?= htmlspecialchars($ruta ?? '/321POS/public', ENT_QUOTES) ?>">

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
      <button class="ubtn ingresos" id="btnIngresos"><span class="icon">💰</span>Ingresos</button>
      <button class="ubtn egresos"  id="btnEgresos"><span class="icon">💸</span>Egresos</button>
    </div>
    <div class="btn-row">
      <button class="ubtn caja-fuerte"  id="btnCajaFuerte"><span class="icon">🔒</span>A Caja Fuerte</button>
      <button class="ubtn herramientas" id="btnHerramientas"><span class="icon">🛠</span>Herramientas</button>
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
    <div class="total-section">TOTAL: $ <span id="totalAmount">0,00</span></div>
    <div class="payment-bar">
      <button class="ubtn dividido"    id="payDividido"><span class="icon">🔀</span>Cobro Dividido</button>
      <button class="ubtn mercadopago" id="payMP"><span class="icon">📱</span>MercadoPago</button>
      <button class="ubtn qr"          id="payQR"><span class="icon">🔳</span>QR</button>
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
    <div class="modal-body" id="modalBody"></div>
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
            <div class="upload-label">🖼 Hacé click o arrastrá una imagen<br><span style="font-size:11px;color:#aaa;">PNG, JPG</span></div>
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

<!-- ══ OVERLAY IFRAME ══ -->
<div id="planillaOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:99999; align-items:center; justify-content:center; padding:18px;">
  <div style="width:92vw; height:88vh; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 18px 60px rgba(0,0,0,.35); position:relative;">
    <iframe id="planillaFrame" src="" style="width:100%; height:100%; border:0; display:block;"></iframe>
  </div>
</div>

<!-- ══ MODAL PAGO DIVIDIDO ══ -->
<div class="modal-overlay" id="modalPagoDividido">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h2>🔀 Cobro Dividido</h2>
      <button class="modal-close" id="closePagoDividido">✕</button>
    </div>
    <div class="modal-summary">
      <div class="summary-stat"><span class="lbl">Total a cobrar</span><span class="val" id="splitTotal">$ 0,00</span></div>
    </div>
    <div class="modal-body" style="gap:14px;">
      <div style="display:grid;grid-template-columns: 1fr 140px; gap:10px; align-items:end;">
        <div>
          <div class="field-label" style="margin-bottom:6px;">Medio 1</div>
          <select id="splitMetodo1" class="field-input" style="width:100%;"><option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="mercadopago">MercadoPago</option><option value="qr">QR</option></select>
        </div>
        <div>
          <div class="field-label" style="margin-bottom:6px;">Monto 1</div>
          <input id="splitMonto1" type="number" step="0.01" min="0" class="field-input" style="width:100%; text-align:right;" value="0">
        </div>
      </div>
      <div style="display:grid;grid-template-columns: 1fr 140px; gap:10px; align-items:end;">
        <div>
          <div class="field-label" style="margin-bottom:6px;">Medio 2</div>
          <select id="splitMetodo2" class="field-input" style="width:100%;"><option value="tarjeta">Tarjeta</option><option value="mercadopago">MercadoPago</option><option value="qr">QR</option><option value="efectivo">Efectivo</option></select>
        </div>
        <div>
          <div class="field-label" style="margin-bottom:6px;">Monto 2</div>
          <input id="splitMonto2" type="number" step="0.01" min="0" class="field-input" style="width:100%; text-align:right;" value="0">
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
        <div style="font-size:12px;color:var(--muted);">La suma debe ser igual al total.</div>
        <div style="font-weight:800;">Restante: <span id="splitRestante">$ 0,00</span></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-modal secondary" id="btnCancelarSplit">Cancelar</button>
      <button class="btn-modal primary"   id="btnCobrarSplit">Cobrar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL PIN ADMIN ══ -->
<div class="modal-overlay" id="modalAdminPin">
  <div class="modal modal-sm" style="width:340px;">
    <div class="modal-header">
      <h2>🔐 Acceso Administrador</h2>
      <button class="modal-close" id="closeAdminPin">✕</button>
    </div>
    <div style="padding: 18px 20px 0;">
      <p style="text-align:center; font-size:13px; color:var(--muted); margin-bottom:4px;">Ingresá el PIN de administrador</p>
      <div class="pin-display" id="pinDisplay">
        <span class="pin-dot" id="pd0"></span>
        <span class="pin-dot" id="pd1"></span>
        <span class="pin-dot" id="pd2"></span>
        <span class="pin-dot" id="pd3"></span>
      </div>
      <div class="pin-error" id="pinError">PIN incorrecto</div>
      <input class="pin-input" id="pinInput" type="password" maxlength="4" inputmode="numeric">
    </div>
    <div class="numpad">
      <button class="np-btn" data-n="1">1</button>
      <button class="np-btn" data-n="2">2</button>
      <button class="np-btn" data-n="3">3</button>
      <button class="np-btn" data-n="4">4</button>
      <button class="np-btn" data-n="5">5</button>
      <button class="np-btn" data-n="6">6</button>
      <button class="np-btn" data-n="7">7</button>
      <button class="np-btn" data-n="8">8</button>
      <button class="np-btn" data-n="9">9</button>
      <button class="np-btn del" id="pinDel">⌫</button>
      <button class="np-btn" data-n="0">0</button>
      <button class="np-btn ok" id="pinOk">Ingresar ✓</button>
    </div>
  </div>
</div>

<!-- ══ MODAL HERRAMIENTAS ADMIN ══ -->
<div class="modal-overlay" id="modalHerramientas">
  <div class="modal" style="width:560px;">
    <div class="modal-header">
      <h2>🛠 Herramientas</h2>
      <span class="admin-badge">👑 ADMIN</span>
      <button class="modal-close" id="closeHerramientas">✕</button>
    </div>
    <div class="tools-grid">
      <button class="tool-btn t-estadisticas" id="toolEstadisticas"><span class="tb-icon">📊</span>Estadísticas</button>
      <button class="tool-btn t-fichadas"     id="toolFichadas"><span class="tb-icon">🕐</span>Ver Fichadas</button>
      <button class="tool-btn t-precios"      id="toolPrecios"><span class="tb-icon">🏷</span>Cambiar Precios</button>
      <button class="tool-btn t-ventas"       id="toolVentasProducto"><span class="tb-icon">📦</span>Ventas por Producto</button>
      <button class="tool-btn t-gastos"       id="toolGastos"><span class="tb-icon">💸</span>Gastos</button>
      <button class="tool-btn t-config"       id="toolConfig"><span class="tb-icon">⚙️</span>Configuración</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/gestion.modals.php'; ?>

<script>
// ---------- BASE URL ----------
const BASE = (document.body.dataset.base || '').replace(/\/$/, '');
const url = (p) => BASE + '/' + String(p || '').replace(/^\/+/, '');

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
      recalcularTotal(); actualizarBadges();
    });
    inp.addEventListener('keydown', e => { if (e.key === 'Enter') inp.blur(); });
  });
  recalcularTotal(); actualizarBadges();
}

function recalcularTotal(){
  document.getElementById('totalAmount').textContent = formatNum(ticket.reduce((s,i)=>s+i.precio*i.cantidad,0));
  const ti = ticket.reduce((s,i)=>s+i.cantidad,0);
  document.getElementById('itemCount').textContent = ti + ' ítem' + (ti!==1?'s':'');
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
  else {
    ticket.splice(idx, 1);
    if (filaSeleccionada === idx) filaSeleccionada = null;
    else if (filaSeleccionada > idx) filaSeleccionada--;
  }
  renderTicket();
});

function cancelarVenta(){
  ticket = []; filaSeleccionada = null; productoSeleccionado = null;
  document.getElementById('codigoProducto').value = '';
  limpiarDescripcion();
  document.getElementById('cantidadInput').value = 1;
  document.getElementById('notasVenta').value = '';
  renderTicket();
}
document.getElementById('btnCancelarVenta').addEventListener('click', cancelarVenta);

// ---------- Descripción ----------
function limpiarDescripcion(){
  const d = document.getElementById('descDisplay');
  d.textContent = '— sin producto —'; d.className = 'desc-display empty';
  document.getElementById('precioDisplay').textContent = '$ —';
  productoSeleccionado = null;
}
function mostrarDescripcion(codigo){
  const prod = productosDB[parseInt(codigo)];
  const d = document.getElementById('descDisplay');
  if(prod){
    productoSeleccionado = parseInt(codigo);
    d.textContent = prod.nombre; d.className = 'desc-display found';
    document.getElementById('precioDisplay').textContent = '$ ' + formatNum(prod.precio);
  } else {
    productoSeleccionado = null;
    d.textContent = codigo ? '✗ Código no encontrado' : '— sin producto —';
    d.className = codigo ? 'desc-display not-found' : 'desc-display empty';
    document.getElementById('precioDisplay').textContent = '$ —';
  }
}
document.getElementById('codigoProducto').addEventListener('input', function(){ mostrarDescripcion(this.value); });
document.getElementById('codigoProducto').addEventListener('keydown', function(e){
  if(e.key === 'Enter' && productoSeleccionado){ document.getElementById('cantidadInput').focus(); document.getElementById('cantidadInput').select(); }
});
document.getElementById('cantidadInput').addEventListener('keydown', function(e){
  if(e.key === 'Enter'){
    if(!productoSeleccionado){ alert('Buscá un código válido.'); return; }
    agregarAlTicket(productoSeleccionado, parseInt(this.value) || 1);
    document.getElementById('codigoProducto').value = '';
    limpiarDescripcion(); this.value = 1;
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
    if (b){ b.textContent = item.cantidad; b.style.display = 'flex'; }
  });
}
buildProductosGrid();

// ---------- Pagos ----------
async function cobrarEnBD(pagosArr){
  if(!ticket.length){ alert('El ticket está vacío.'); return; }
  const total = ticket.reduce((s,i)=>s+i.precio*i.cantidad,0);
  const payload = {
    items: ticket.map(i => ({ codigo:i.codigo, precio:i.precio, cantidad:i.cantidad })),
    notas: document.getElementById('notasVenta').value.trim(),
    pagos: pagosArr
  };
  const res = await fetch(url('pos/cobrar'), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
  const data = await res.json().catch(()=>null);
  if(!res.ok || !data || data.status !== 'ok'){
    alert('Error al cobrar: ' + (data?.message || 'revisá servidor'));
    return null;
  }
  historialVentas.unshift({
    numero: data.pedido_id, hora: horaActual(),
    metodo: (pagosArr.length > 1) ? 'Cobro Dividido' : pagosArr[0].metodo,
    metodoCss: (pagosArr.length > 1) ? 'dividido' : (pagosArr[0].metodo === 'mercadopago' ? 'mercadopago' : pagosArr[0].metodo),
    items: ticket.map(i=>({...i})), total,
    totalItems: ticket.reduce((s,i)=>s+i.cantidad,0),
    notas: document.getElementById('notasVenta').value.trim()
  });
  cancelarVenta();
  return data.pedido_id;
}
function animarBotonPago(btn){
  document.querySelectorAll('.payment-bar .ubtn').forEach(b=>b.style.opacity='0.55');
  btn.style.opacity='1'; btn.style.transform='scale(1.03)';
  setTimeout(()=>{ document.querySelectorAll('.payment-bar .ubtn').forEach(b=>{ b.style.opacity=''; b.style.transform=''; }); }, 900);
}
document.getElementById('payEfectivo').addEventListener('click', async function(){ const total=ticket.reduce((s,i)=>s+i.precio*i.cantidad,0); const ok=await cobrarEnBD([{metodo:'efectivo',monto:total}]); if(ok) animarBotonPago(this); });
document.getElementById('payTarjeta').addEventListener('click', async function(){ const total=ticket.reduce((s,i)=>s+i.precio*i.cantidad,0); const ok=await cobrarEnBD([{metodo:'tarjeta',monto:total}]); if(ok) animarBotonPago(this); });
document.getElementById('payMP').addEventListener('click', async function(){ const total=ticket.reduce((s,i)=>s+i.precio*i.cantidad,0); const ok=await cobrarEnBD([{metodo:'mercadopago',monto:total}]); if(ok) animarBotonPago(this); });
document.getElementById('payQR').addEventListener('click', async function(){ const total=ticket.reduce((s,i)=>s+i.precio*i.cantidad,0); const ok=await cobrarEnBD([{metodo:'qr',monto:total}]); if(ok) animarBotonPago(this); });

// Pago dividido
function abrirPagoDividido(){
  if(!ticket.length){ alert('El ticket está vacío.'); return; }
  const total = ticket.reduce((s,i)=>s+i.precio*i.cantidad,0);
  document.getElementById('splitTotal').textContent = '$ ' + formatNum(total);
  document.getElementById('splitMonto1').value = total.toFixed(2);
  document.getElementById('splitMonto2').value = '0.00';
  actualizarRestanteSplit();
  document.getElementById('modalPagoDividido').classList.add('open');
  setTimeout(()=>document.getElementById('splitMonto1').focus(),80);
}
function cerrarPagoDividido(){ document.getElementById('modalPagoDividido').classList.remove('open'); }
function actualizarRestanteSplit(){
  const total=ticket.reduce((s,i)=>s+i.precio*i.cantidad,0);
  const m1=parseFloat(document.getElementById('splitMonto1').value||'0')||0;
  const m2=parseFloat(document.getElementById('splitMonto2').value||'0')||0;
  document.getElementById('splitRestante').textContent='$ '+formatNum(total-(m1+m2));
}
document.getElementById('payDividido').addEventListener('click', abrirPagoDividido);
document.getElementById('closePagoDividido').addEventListener('click', cerrarPagoDividido);
document.getElementById('btnCancelarSplit').addEventListener('click', cerrarPagoDividido);
document.getElementById('modalPagoDividido').addEventListener('click', function(e){ if(e.target===this) cerrarPagoDividido(); });
['splitMonto1','splitMonto2','splitMetodo1','splitMetodo2'].forEach(id=>{ document.getElementById(id).addEventListener('input', actualizarRestanteSplit); document.getElementById(id).addEventListener('change', actualizarRestanteSplit); });
document.getElementById('btnCobrarSplit').addEventListener('click', async function(){
  const total=ticket.reduce((s,i)=>s+i.precio*i.cantidad,0);
  const m1=parseFloat(document.getElementById('splitMonto1').value||'0')||0;
  const m2=parseFloat(document.getElementById('splitMonto2').value||'0')||0;
  if(m1<=0&&m2<=0){ alert('Ingresá al menos un monto.'); return; }
  if(Math.abs(+(m1+m2).toFixed(2) - +total.toFixed(2)) > 0.01){ alert('La suma de los montos debe ser igual al total.'); return; }
  const pagosArr=[];
  if(m1>0) pagosArr.push({metodo:document.getElementById('splitMetodo1').value, monto:+m1.toFixed(2)});
  if(m2>0) pagosArr.push({metodo:document.getElementById('splitMetodo2').value, monto:+m2.toFixed(2)});
  const ok=await cobrarEnBD(pagosArr);
  if(ok){ cerrarPagoDividido(); animarBotonPago(document.getElementById('payDividido')); }
});

// ---------- Overlay iframe ----------
function openOverlay(ruta){
  document.getElementById('planillaFrame').src = ruta;
  document.getElementById('planillaOverlay').style.display = 'flex';
}
function closePlanilla(){
  document.getElementById('planillaOverlay').style.display = 'none';
  document.getElementById('planillaFrame').src = '';
}
document.getElementById('btnPlanilla').addEventListener('click', e => { e.preventDefault(); openOverlay(url('planilla/pos')); });
document.getElementById('planillaOverlay').addEventListener('click', e => { if(e.target.id==='planillaOverlay') closePlanilla(); });
window.addEventListener('message', e => { if(e.data?.type==='CLOSE_PLANILLA') closePlanilla(); });
window.addEventListener('keydown', e => { if(e.key==='Escape') closePlanilla(); });

// ---------- Historial ----------
document.getElementById('btnHistorial')?.addEventListener('click', e => { e.preventDefault(); openOverlay(url('cajero/libroDiario')); });
document.getElementById('closeHistorial').addEventListener('click', () => document.getElementById('modalHistorial').classList.remove('open'));
document.getElementById('modalHistorial').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });

// ---------- Buscador ----------
function renderListaBusqueda(filtro, contenedor, alSeleccionar){
  contenedor.innerHTML = '';
  const f = filtro.toLowerCase();
  Object.entries(productosDB).filter(([cod,p]) => !f || p.nombre.toLowerCase().includes(f) || cod.includes(f)).forEach(([cod,prod]) => {
    const row = document.createElement('div');
    row.className = 'prod-row';
    row.innerHTML = `<span class="prod-code">${cod}</span><span class="prod-name">${prod.nombre}</span><span class="prod-price">$ ${formatNum(prod.precio)}</span>`;
    row.addEventListener('click', () => alSeleccionar(parseInt(cod), prod));
    contenedor.appendChild(row);
  });
  if(!contenedor.children.length) contenedor.innerHTML = '<p style="padding:20px;color:var(--muted);text-align:center;">Sin resultados</p>';
}
document.getElementById('btnLupa').addEventListener('click', () => {
  document.getElementById('busquedaInput').value = '';
  renderListaBusqueda('', document.getElementById('busquedaList'), (codigo) => {
    document.getElementById('codigoProducto').value = codigo; mostrarDescripcion(codigo);
    document.getElementById('modalBusqueda').classList.remove('open');
    document.getElementById('cantidadInput').focus(); document.getElementById('cantidadInput').select();
  });
  document.getElementById('modalBusqueda').classList.add('open');
  setTimeout(()=>document.getElementById('busquedaInput').focus(),100);
});
document.getElementById('closeBusqueda').addEventListener('click', () => document.getElementById('modalBusqueda').classList.remove('open'));
document.getElementById('modalBusqueda').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
document.getElementById('busquedaInput').addEventListener('input', function(){
  renderListaBusqueda(this.value, document.getElementById('busquedaList'), (codigo) => {
    document.getElementById('codigoProducto').value = codigo; mostrarDescripcion(codigo);
    document.getElementById('modalBusqueda').classList.remove('open');
    document.getElementById('cantidadInput').focus(); document.getElementById('cantidadInput').select();
  });
});

// ---------- Agregar Acceso ----------
let accesoTempCodigo = null, accesoTempImg = null;
function abrirModalAgregarAcceso(){
  accesoTempCodigo = null; accesoTempImg = null;
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
  document.getElementById('accesoSeleccionado').innerHTML = `<span class="prod-emoji">${prod.nombre.split(' ')[0]}</span><div class="prod-info"><strong>${prod.nombre}</strong><span>Código ${codigo} · $ ${formatNum(prod.precio)}</span></div>`;
}
document.getElementById('btnVolverSeleccion').addEventListener('click', () => { document.getElementById('pasoSeleccion').style.display='flex'; document.getElementById('pasoSeleccion').style.flexDirection='column'; document.getElementById('pasoImagen').style.display='none'; });
document.getElementById('imgFileInput').addEventListener('change', function(){
  const file=this.files[0]; if(!file) return;
  const reader=new FileReader();
  reader.onload=e=>{ accesoTempImg=e.target.result; document.getElementById('imgPreview').src=accesoTempImg; document.getElementById('imgUploadArea').classList.add('has-img'); };
  reader.readAsDataURL(file);
});
document.getElementById('btnConfirmarAcceso').addEventListener('click', () => {
  if(!accesoTempCodigo) return;
  const ex=accesosRapidos.find(a=>a.codigo===accesoTempCodigo);
  if(ex){ if(accesoTempImg) ex.imgDataUrl=accesoTempImg; } else { accesosRapidos.push({codigo:accesoTempCodigo,imgDataUrl:accesoTempImg}); }
  buildProductosGrid(); document.getElementById('modalAgregarAcceso').classList.remove('open');
});
document.getElementById('closeAgregarAcceso').addEventListener('click', () => document.getElementById('modalAgregarAcceso').classList.remove('open'));
document.getElementById('modalAgregarAcceso').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
document.getElementById('busquedaAccesoInput').addEventListener('input', function(){ renderListaBusqueda(this.value, document.getElementById('busquedaAccesoList'), seleccionarProductoAcceso); });

// ---------- Gestión (modales del partial) ----------
document.getElementById('btnInicioCaja')?.addEventListener('click', e => { e.preventDefault(); abrirCajaModal(); });
document.getElementById('btnEgresos')?.addEventListener('click',    e => { e.preventDefault(); abrirGastoModal(); });
document.getElementById('btnCajaFuerte')?.addEventListener('click', e => { e.preventDefault(); abrirCajaFuerteModal(); });
document.getElementById('btnFichar')?.addEventListener('click',     e => { e.preventDefault(); abrirFicharModal(); });
document.getElementById('btnIngresos')?.addEventListener('click',   e => { e.preventDefault(); openOverlay(url('cajero/ingresos')); });

// ---------- PIN Admin ----------
const ADMIN_PIN = '1234'; // ← Cambiá por tu PIN real
let pinActual = '', pinDestinoCallback = null;

function abrirPinAdmin(callback){
  pinActual = ''; pinDestinoCallback = callback;
  actualizarPinDisplay();
  document.getElementById('pinError').classList.remove('show');
  document.getElementById('modalAdminPin').classList.add('open');
  setTimeout(()=>document.getElementById('pinInput').focus(), 80);
}
function cerrarPinAdmin(){ document.getElementById('modalAdminPin').classList.remove('open'); pinActual=''; pinDestinoCallback=null; }
function actualizarPinDisplay(){ for(let i=0;i<4;i++){ const d=document.getElementById('pd'+i); if(d) d.classList.toggle('filled', i<pinActual.length); } }
function agregarDigitoPin(d){ if(pinActual.length>=4) return; pinActual+=d; actualizarPinDisplay(); document.getElementById('pinError').classList.remove('show'); if(pinActual.length===4) setTimeout(validarPin,120); }
function borrarDigitoPin(){ pinActual=pinActual.slice(0,-1); actualizarPinDisplay(); document.getElementById('pinError').classList.remove('show'); }
function validarPin(){
  if(pinActual===ADMIN_PIN){ cerrarPinAdmin(); if(typeof pinDestinoCallback==='function') pinDestinoCallback(); }
  else {
    const dots=document.querySelectorAll('.pin-dot');
    dots.forEach(d=>{ d.style.borderColor='var(--danger)'; d.style.background='rgba(231,76,60,0.15)'; });
    document.getElementById('pinError').classList.add('show');
    setTimeout(()=>{ dots.forEach(d=>{ d.style.borderColor=''; d.style.background=''; }); pinActual=''; actualizarPinDisplay(); }, 700);
  }
}
document.querySelectorAll('.np-btn[data-n]').forEach(btn => btn.addEventListener('click', ()=>agregarDigitoPin(btn.dataset.n)));
document.getElementById('pinDel')?.addEventListener('click', borrarDigitoPin);
document.getElementById('pinOk')?.addEventListener('click', validarPin);
document.getElementById('pinInput')?.addEventListener('keydown', e => {
  if(e.key>='0'&&e.key<='9'){ e.preventDefault(); agregarDigitoPin(e.key); }
  else if(e.key==='Backspace'){ e.preventDefault(); borrarDigitoPin(); }
  else if(e.key==='Enter'){ e.preventDefault(); validarPin(); }
});
document.getElementById('closeAdminPin')?.addEventListener('click', cerrarPinAdmin);
document.getElementById('modalAdminPin')?.addEventListener('click', function(e){ if(e.target===this) cerrarPinAdmin(); });

// ---------- Herramientas ----------
document.getElementById('btnHerramientas')?.addEventListener('click', e => { e.preventDefault(); abrirPinAdmin(()=>document.getElementById('modalHerramientas').classList.add('open')); });
document.getElementById('closeHerramientas')?.addEventListener('click', ()=>document.getElementById('modalHerramientas').classList.remove('open'));
document.getElementById('modalHerramientas')?.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
document.getElementById('toolEstadisticas')?.addEventListener('click',  ()=>{ document.getElementById('modalHerramientas').classList.remove('open'); openOverlay(url('admin/estadisticas')); });
document.getElementById('toolFichadas')?.addEventListener('click',      ()=>{ document.getElementById('modalHerramientas').classList.remove('open'); openOverlay(url('admin/fichadas')); });
document.getElementById('toolPrecios')?.addEventListener('click',       ()=>{ document.getElementById('modalHerramientas').classList.remove('open'); openOverlay(url('admin/precios')); });
document.getElementById('toolVentasProducto')?.addEventListener('click',()=>{ document.getElementById('modalHerramientas').classList.remove('open'); openOverlay(url('admin/ventas-producto')); });
document.getElementById('toolGastos')?.addEventListener('click',        ()=>{ document.getElementById('modalHerramientas').classList.remove('open'); openOverlay(url('admin/gastos')); });
document.getElementById('toolConfig')?.addEventListener('click',        ()=>{ document.getElementById('modalHerramientas').classList.remove('open'); openOverlay(url('admin/configuracion')); });

// ===== FICHAR =====
let ficharEmpleadoId = null, ficharTimerInt = null;

function abrirFicharModal(){
  const m=document.getElementById('ficharModal');
  if(!m){ alert('No existe ficharModal'); return; }
  m.style.display='flex';
  ficharEmpleadoId=null; stopFicharTimer();
  const info=document.getElementById('ficharInfo'); if(info) info.style.display='none';
  const n=document.getElementById('ficharNombre'); if(n) n.textContent='—';
  const e=document.getElementById('ficharEstado'); if(e) e.textContent='—';
  const t=document.getElementById('ficharTimer');  if(t) t.textContent='00:00:00';
  const b1=document.getElementById('btnFicharEntrada'); if(b1) b1.disabled=true;
  const b2=document.getElementById('btnFicharSalida');  if(b2) b2.disabled=true;
  const inp=document.getElementById('ficharEmpleadoNum');
  if(inp){ inp.value=''; setTimeout(()=>inp.focus(),80); }
}
function cerrarFicharModal(){ const m=document.getElementById('ficharModal'); if(m) m.style.display='none'; stopFicharTimer(); }
function stopFicharTimer(){ if(ficharTimerInt){ clearInterval(ficharTimerInt); ficharTimerInt=null; } }
function fmt2(n){ return String(n).padStart(2,'0'); }
function renderTimer(sec){
  const el=document.getElementById('ficharTimer'); if(!el) return;
  sec=Math.max(0,sec|0);
  el.textContent=`${fmt2(Math.floor(sec/3600))}:${fmt2(Math.floor((sec%3600)/60))}:${fmt2(sec%60)}`;
}

async function ficharCargar(){
  const num=(document.getElementById('ficharEmpleadoNum')?.value||'').trim();
  if(!num){ alert('Ingresá un número de empleado.'); return; }
  const res=await fetch(url('cajero/ficharEstado'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({empleado_numero:num})});
  const data=await res.json().catch(()=>null);
  if(!res.ok||!data||data.status!=='ok'){ alert(data?.message||'No se pudo cargar empleado'); return; }
  ficharEmpleadoId=data.empleado_id;
  const info=document.getElementById('ficharInfo'); if(info) info.style.display='block';
  const nombre=document.getElementById('ficharNombre'); if(nombre) nombre.textContent=data.empleado_nombre;
  const b1=document.getElementById('btnFicharEntrada');
  const b2=document.getElementById('btnFicharSalida');
  const estado=document.getElementById('ficharEstado');
  const resumenViejo=document.getElementById('ficharResumen'); if(resumenViejo) resumenViejo.remove();
  document.getElementById('ficharTimer').textContent='00:00:00';

  if(data.en_turno){
    if(b1) b1.disabled=true; if(b2) b2.disabled=false;
    if(estado) estado.textContent='En turno desde '+data.entrada_hora;
    let sec=data.segundos_trabajados||0;
    renderTimer(sec); stopFicharTimer();
    ficharTimerInt=setInterval(()=>{ sec++; renderTimer(sec); },1000);
  } else if(data.entrada_hora && data.salida_hora){
    if(b1) b1.disabled=false; if(b2) b2.disabled=true;
    stopFicharTimer(); renderTimer(0);
    document.getElementById('ficharTimer').textContent='';
    const [hE,mE]=data.entrada_hora.split(':').map(Number);
    const [hS,mS]=data.salida_hora.split(':').map(Number);
    const totalMin=(hS*60+mS)-(hE*60+mE);
    if(estado) estado.innerHTML=`Último turno — Entrada: <strong>${data.entrada_hora}</strong> &nbsp;|&nbsp; Salida: <strong>${data.salida_hora}</strong>`;
    const resumen=document.createElement('div');
    resumen.id='ficharResumen'; resumen.style.cssText='margin-top:8px; font-size:14px; color:#374151;';
    resumen.innerHTML=`⏱ Tiempo trabajado: <strong>${String(Math.floor(totalMin/60)).padStart(2,'0')}:${String(totalMin%60).padStart(2,'0')}</strong>`;
    document.getElementById('ficharTimer').after(resumen);
  } else {
    if(b1) b1.disabled=false; if(b2) b2.disabled=true;
    stopFicharTimer(); renderTimer(0);
    if(estado) estado.textContent='Hoy aún no ha ingresado';
  }
}

async function ficharRegistrar(tipo){
  if(!ficharEmpleadoId){ alert('Primero confirmá el empleado.'); return; }
  const res=await fetch(url('cajero/ficharRegistrar'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({empleado_id:ficharEmpleadoId,tipo})});
  const data=await res.json().catch(()=>null);
  if(!res.ok||!data||data.status!=='ok'){ alert(data?.message||'Error al fichar'); return; }

  await fetch(url('cajero/ficharEstado'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({empleado_id:ficharEmpleadoId})})
  .then(r=>r.json()).then(st=>{
    if(!st||st.status!=='ok') return;
    const nombre=document.getElementById('ficharNombre'); if(nombre) nombre.textContent=st.empleado_nombre;
    const estado=document.getElementById('ficharEstado');
    const b1=document.getElementById('btnFicharEntrada');
    const b2=document.getElementById('btnFicharSalida');
    const resumenViejo=document.getElementById('ficharResumen'); if(resumenViejo) resumenViejo.remove();
    stopFicharTimer();

    if(st.en_turno){
      if(b1) b1.disabled=true; if(b2) b2.disabled=false;
      if(estado) estado.textContent='En turno desde '+st.entrada_hora;
      let sec=st.segundos_trabajados||0;
      renderTimer(sec);
      ficharTimerInt=setInterval(()=>{ sec++; renderTimer(sec); },1000);
    } else {
      if(b1) b1.disabled=false; if(b2) b2.disabled=true;
      renderTimer(0); document.getElementById('ficharTimer').textContent='';
      if(st.entrada_hora && st.salida_hora){
        const [hE,mE]=st.entrada_hora.split(':').map(Number);
        const [hS,mS]=st.salida_hora.split(':').map(Number);
        const totalMin=(hS*60+mS)-(hE*60+mE);
        if(estado) estado.innerHTML=`Último turno — Entrada: <strong>${st.entrada_hora}</strong> &nbsp;|&nbsp; Salida: <strong>${st.salida_hora}</strong>`;
        const resumen=document.createElement('div');
        resumen.id='ficharResumen'; resumen.style.cssText='margin-top:8px; font-size:14px; color:#374151;';
        resumen.innerHTML=`⏱ Tiempo trabajado: <strong>${String(Math.floor(totalMin/60)).padStart(2,'0')}:${String(totalMin%60).padStart(2,'0')}</strong>`;
        document.getElementById('ficharTimer').after(resumen);
      } else {
        if(estado) estado.textContent='Hoy aún no ha ingresado';
      }
    }
  });
}

// ---------- Reloj ----------
function actualizarReloj(){
  const a=new Date();
  document.getElementById('horaHdr').textContent='⏰ '+a.toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit'});
  document.getElementById('fechaHdr').textContent='📅 '+a.toLocaleDateString('es-AR',{weekday:'short',day:'2-digit',month:'2-digit',year:'numeric'});
}
actualizarReloj();
setInterval(actualizarReloj, 30000);
renderTicket();
</script>
</body>
</html>
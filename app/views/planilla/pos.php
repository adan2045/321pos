<?php
/**
 * app/views/planilla/pos.php
 * Planilla “modo POS” (popup centrado) reutilizando la planilla original que ya calcula todo bien.
 * NO vuelve a inventar el render: incluye la vista vieja y solo oculta lo que no querés.
 */

// Si la vista incluida espera estas variables, las definimos para evitar warnings.
$ruta   = $ruta   ?? (\App::baseUrl() ?? '');
$head   = $head   ?? '';
$nav    = $nav    ?? '';
$footer = $footer ?? '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Planilla del día</title>
  <style>
    body{
      margin:0;
      background:#f3f4f6;
      font-family:Segoe UI,Tahoma,Verdana,sans-serif;
    }

    /* Popup centrado (60% ancho) */
    .pos-wrap{
      width: 60vw;
      max-width: 980px;
      min-width: 720px;
      margin: 24px auto;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      box-shadow: 0 12px 30px rgba(0,0,0,.12);
      padding: 16px;
      position: relative;
    }

    .pos-close{
      position:absolute;
      top:14px; right:14px;
      border:none;
      border-radius:10px;
      padding:8px 12px;
      font-weight:700;
      cursor:pointer;
      background:#111;
      color:#fff;
      z-index:9999;
    }

    /* ✅ Ocultar lo que NO querés (solo en este modo POS) */
    nav, header, .navbar, .topbar { display:none !important; }

    /* Tabs superiores (Estado de Mesas / Pedidos / Planilla) */
    .tabs, .tab, .nav-tabs,
    .menuSuperior, .menu-superior,
    .barraTabs, .barra-tabs {
      display:none !important;
    }

    /* Botonera derecha */
    .acciones, .botonera, .panel-derecho, .right-buttons,
    .sidebar, aside,
    .col-botones, .colBotones,
    .btnsDerecha, .botonesDerecha {
      display:none !important;
    }

    /* Forzar que el contenido ocupe el ancho del popup */
    .container, .contenedor, main, .content, .contenido {
      width:100% !important;
      max-width:100% !important;
    }
  </style>
</head>
<body>

  <div class="pos-wrap">
    <button class="pos-close" onclick="window.parent.postMessage({type:'CLOSE_PLANILLA'}, '*')">Cerrar</button>

    <?php
      // ✅ Reutiliza tu planilla “buena” que ya muestra FACTURAS B, EFECTIVO, QR, MP, etc.
      // Ajustá este include si tu archivo está en otro lugar.
      include __DIR__ . '/../cajero/planillaCaja.php';
    ?>
  </div>
  <?php
  // por las dudas, si $ruta no está, lo definimos acá
  if (!isset($ruta)) $ruta = \App::$ruta;

  // $ultimoCierre debería venir del controller; si no, evitamos error
  if (!isset($ultimoCierre)) $ultimoCierre = 0;

  include __DIR__ . '/../partials/gestion.modals.php';
?>
</body>
</html>
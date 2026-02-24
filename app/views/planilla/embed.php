<?php
// Planilla embed: sin head/nav/footer, con CSS para ocultar botones/tabs
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Planilla</title>

  <style>
    /* Fondo limpio */
    html, body { margin:0; padding:0; background:#fff; }

    /* Ocultar nav/linkeos superiores si aparecieran */
    nav, header, .navbar, .topbar { display:none !important; }

    /* Ocultar la fila de tabs (Estado de Mesas / Pedidos / Planilla) */
    .tabs, .tab, .nav-tabs, .menuSuperior, .menu-superior, .barraTabs, .barra-tabs {
      display: none !important;
    }

    /* Ocultar botonera / panel derecho */
    .acciones, .botonera, .panel-derecho, .right-buttons, .sidebar, aside,
    .col-botones, .colBotones, .btnsDerecha, .botonesDerecha {
      display: none !important;
    }

    /* Forzar ancho total del contenido */
    .container, .contenedor, main, .content, .contenido {
      width: 100% !important;
      max-width: 100% !important;
    }

    body { padding: 12px; font-family: Arial, sans-serif; }
  </style>
</head>
<body>

<?php
// ✅ Reusa la planilla que ya funciona
// Ajustá esta ruta si tu archivo está en otro lugar:
include __DIR__ . '/../cajero/planillaCaja.php';
?>

</body>
</html>
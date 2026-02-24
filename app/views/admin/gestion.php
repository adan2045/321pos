<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head ?>
    <title><?= $title ?></title>
</head>
<body>
    <header><?= $nav ?></header>
    <main>
    <h2 class="main-title">Gestionar Locales</h2>
    <div class="locals-grid">
        <div class="local-container">
            <div class="local-card tall-card">
                <div class="local-icon">
                    <i class="fas fa-store"></i>
                </div>
                <a href="<?=$ruta?>cajero/vistaCajero" class="action-btn">Pizzeria Santa Maria</a>
            </div>
            <div class="buttons-column">
             <a href="<?=$ruta?>mesa/listado" class="action-btn">Mesas</a>
             <a href="<?=$ruta?>usuario/listado" class="action-btn delete-btn">Usuarios</a>
             <a href="<?=$ruta?>producto/listado" class="action-btn">Carta</a> 
        </div>
        

       
    </div>
</main>
    <?= $footer ?>   
</body>
</html>
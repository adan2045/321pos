<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="stylesheet" href="/MVC2/css/main.css">
  <title><?= $title ?? 'Inicio' ?></title>
</head>
<body>
    
<header>
        
<header>
    <nav>
        <div class="logo-space">Gestion.IO</div>
        
        <div class="auth-container">
            <form action="http://localhost/MVC2/login/login" method="post">
                <button type="submit" class="btn">Iniciar Sesión</button>
            </form>
            
        </div>
    </nav>
</header>
    </header>

<section class="hero">
    <div class="hero-content">
        <h1>Revoluciona la Gestión de tu Restaurante</h1>
        <p>Sistema integral de automatización para pedidos, cartas digitales y gestión de personal. El futuro de la hostelería está aquí.</p>
        <a href="#" class="cta-button">Solicitar más información</a>
    </div>
</section>

<section class="features" id="features">
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-cogs feature-icon"></i>
            <h3>Automatización Inteligente</h3>
            <p>Reduce costos operativos y errores humanos con nuestro sistema de gestión automatizado.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-hourglass-half feature-icon"></i>
            <h3>Ahorro de Tiempo</h3>
            <p>Optimiza los tiempos de servicio y mejora la experiencia de tus clientes.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-dollar-sign feature-icon"></i>
            <h3>Mayor Rentabilidad</h3>
            <p>Reduce costos de personal mientras aumentas la eficiencia operativa.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-rocket feature-icon"></i>
            <h3>Innovación Continua</h3>
            <p>Optimiza tus sistemas de trabajo con tecnología de vanguardia que evoluciona con las necesidades de tu negocio.</p>
        </div>
    </div>

</section>

<section class="statistics">
    <div class="stats-grid">
	</div>
	<div class="stat-item left">
    <div class="stat-content">
        <div class="stat-number">30%</div>
        <div class="stat-description">
            <h4>Reducción en costos operativos</h4>
            <p>Optimiza tus gastos mediante la automatización de procesos y la reducción de errores en la gestión diaria.</p>
        </div>
    </div>
</div>

<div class="stat-item right">
    <div class="stat-content">
        <div class="stat-number">50%</div>
        <div class="stat-description">
            <h4>Menos errores en pedidos</h4>
            <p>La precisión es clave en la satisfacción del cliente. Nuestro sistema reduce drásticamente los errores.</p>
        </div>
    </div>
</div>

<div class="stat-item left">
    <div class="stat-content">
        <div class="stat-number">2x</div>
        <div class="stat-description">
            <h4>Más eficiencia en servicio</h4>
            <p>Duplica la velocidad de atención al cliente mediante procesos optimizados y automatizados.</p>
        </div>
    </div>
</div>
</section>


	
	<?=$footer ?>
   
</body>
</body>
</html>
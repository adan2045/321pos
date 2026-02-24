<!DOCTYPE html>
<html lang="es">

<head>
    <?= $head ?>
    <title><?= $title ?? 'Alta de Usuario' ?></title>
    <link rel="stylesheet" href="/public/css/crud.css">
</head>

<body class="body-registro">
    <div class="registro-container">
        <div class="registro-header">
            <h1>Alta de Usuario</h1>
            <p>Sistema de Gestión</p>
        </div>

        <form class="registro-form" method="POST" action="/usuario/guardar">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Apellido</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="apellido" class="form-control" required>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Correo Electrónico</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">DNI</label>
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="dni" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <i class="fas fa-user-tag"></i>
                    <select name="rol" class="form-control" required>
                        <option value="">Seleccionar rol</option>
                        <option value="mozo">Mozo</option>
                        <option value="cajero">Cajero</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>

            <div class="terms-check">
                <input type="checkbox" name="terminos" id="terms" required>
                <label for="terms">Acepto los términos y condiciones</label>
            </div>

            <button type="submit" class="btn">Guardar Usuario</button>
        </form>

        <div class="registro-footer">
            <p><a href="/admin/gestion">Volver a Gestión</a></p>
        </div>
    </div>

    <?= $footer ?>
</body>
</html>
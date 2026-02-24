<!DOCTYPE html>
<html lang="es">
<head>
    <?= $head ?>
    <title><?= $title ?></title>
</head>
<body>
    <header><?= $nav ?></header> <!-- Menú principal -->

    <main>
        <div class="registro-container">
            <div class="registro-header">
                <h1><?= isset($usuario) ? 'Modificación de Usuario' : 'Nuevo Usuario' ?></h1>
            </div>

            <form action="<?= isset($usuario) ? $ruta . '/usuario/actualizar' : $ruta . '/usuario/guardar' ?>" method="POST" class="registro-form">
                <?php if (isset($usuario)): ?>
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= $usuario['nombre'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required value="<?= $usuario['apellido'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= $usuario['email'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" <?= isset($usuario) ? '' : 'required' ?>>
                    </div>

                    <div class="form-group">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" required value="<?= $usuario['dni'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-control">
                            <option value="mozo" <?= (isset($usuario['rol']) && $usuario['rol'] == 'mozo') ? 'selected' : '' ?>>Mozo</option>
                            <option value="admin" <?= (isset($usuario['rol']) && $usuario['rol'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <?php if (!isset($usuario)): ?>
                        <div class="form-group full-width">
                            <label>
                                <input type="checkbox" name="terminos" required> Acepto los términos y condiciones
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn"><?= isset($usuario) ? 'Actualizar' : 'Guardar' ?> Usuario</button>
            </form>

            <div class="registro-footer">
                <a href="<?= $ruta ?>/usuario/listado">Volver al Listado</a>
            </div>
        </div>
    </main>

    <?= $footer ?>
</body>
</html>
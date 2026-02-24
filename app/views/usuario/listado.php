<!DOCTYPE html>
<html lang="es">
<head>
    <?= $head ?>
    <title><?= $title ?? 'Listado de Usuarios' ?></title>
</head>
<body>
    

    <main>
        <div class="listado-container">
            <div class="listado-header">
                <h1>Usuarios del Sistema</h1>
                <p>Sistema de Gestión</p>
            </div>

            <div class="listado-top-actions">
                <a href="<?= $ruta ?>/usuario/formulario" class="listado-btn listado-btn-nuevo">➕ Nuevo Usuario</a>
            </div>

            <div class="listado-tabla-wrapper">
                <table class="listado-tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>DNI</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= $usuario['id'] ?></td>
                                <td><?= $usuario['nombre'] ?></td>
                                <td><?= $usuario['apellido'] ?></td>
                                <td><?= $usuario['email'] ?></td>
                                <td><?= $usuario['dni'] ?? '-' ?></td>
                                <td><?= $usuario['rol'] ?></td>
                                <td>
                                    <div class="listado-acciones">
                                        <a href="<?= $ruta ?>/usuario/modificar?id=<?= $usuario['id'] ?>" class="listado-btn-mini">Modificar</a>
                                        <a href="<?= $ruta ?>/usuario/eliminar?id=<?= $usuario['id'] ?>" class="listado-btn-mini listado-btn-eliminar" onclick="return confirm('¿Eliminar este usuario?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="listado-footer">
                <a href="<?= $ruta ?>/admin/gestion">Volver al Panel</a>
            </div>
        </div>
    </main>

    
</body>
</html>
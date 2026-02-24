<!DOCTYPE html>
<html lang="es">
<head>
    <?= $head ?>
    <title><?= $title ?? 'Listado de Productos' ?></title>
    <link rel="stylesheet" href="/public/css/listado.css">
    <link rel="stylesheet" href="/public/css/switch.css">
</head>
<body>
    <header><?= $nav ?></header>

    <main>
        <div class="listado-container">
            <div class="listado-header">
                <h1>Productos del Sistema</h1>
                <p>Sistema de Gestión</p>
            </div>

            <div class="listado-top-actions">
                <a href="<?= App::baseUrl() ?>/producto/formulario" class="listado-btn producto-btn-nuevo">➕ Agregar Producto</a>
            </div>

            <div class="listado-tabla-wrapper">
                <table class="listado-tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= $producto['id'] ?></td>
                                <td><?= $producto['nombre'] ?></td>
                                <td><?= $producto['descripcion'] ?></td>
                                <td>$<?= number_format($producto['precio'], 2) ?></td>
                                <td><?= $producto['categoria'] ?></td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="toggle-estado" data-id="<?= $producto['id'] ?>" <?= $producto['activo'] ? 'checked' : '' ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="listado-acciones">
                                        <a href="<?= App::baseUrl() ?>/producto/modificar?id=<?= $producto['id'] ?>" class="listado-btn-mini">Modificar</a>
                                        <a href="<?= App::baseUrl() ?>/producto/eliminar?id=<?= $producto['id'] ?>" class="listado-btn-mini listado-btn-eliminar" onclick="return confirm('¿Eliminar este producto?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="listado-footer">
                <a href="<?= App::baseUrl() ?>/admin/gestion">Volver al Panel</a>
            </div>
        </div>
    </main>

    <?= $footer ?>

    <script>
    document.querySelectorAll('.toggle-estado').forEach(switchElem => {
        switchElem.addEventListener('change', function() {
            const productoId = this.dataset.id;
            const nuevoEstado = this.checked ? 1 : 0;

            fetch('<?= App::baseUrl() ?>/producto/toggleEstado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${productoId}&activo=${nuevoEstado}`
            })
            .then(response => response.text())
            .then(data => {
                console.log('Estado actualizado:', data);
            })
            .catch(error => {
                alert('Error al cambiar el estado');
                this.checked = !this.checked;
            });
        });
    });
    </script>
</body>
</html>
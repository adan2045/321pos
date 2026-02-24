<!DOCTYPE html>
<html lang="es">
<head>
    <?= $head ?>
    <title><?= $title ?></title>
</head>
<body class="body-registro">


<main>
    <div class="registro-container">
        <div class="registro-header">
            <h1><?= isset($producto) ? 'Editar Producto' : 'Nuevo Producto' ?></h1>
        </div>

        <form action="<?= isset($producto) ? $ruta . '/producto/actualizar' : $ruta . '/producto/guardar' ?>" method="POST" class="registro-form">
            <?php if (isset($producto)): ?>
                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= $producto['nombre'] ?? '' ?>" required>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= $producto['descripcion'] ?? '' ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Precio</label>
                    <input type="number" name="precio" step="0.01" class="form-control" value="<?= $producto['precio'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="categoria" class="form-control">
                        <option value="bebida" <?= (isset($producto['categoria']) && $producto['categoria'] == 'bebida') ? 'selected' : '' ?>>Bebida</option>
                        <option value="postre" <?= (isset($producto['categoria']) && $producto['categoria'] == 'postre') ? 'selected' : '' ?>>Postre</option>
                        <option value="comida" <?= (isset($producto['categoria']) && $producto['categoria'] == 'comida') ? 'selected' : '' ?>>Comida</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn"><?= isset($producto) ? 'Actualizar' : 'Guardar' ?> Producto</button>
        </form>

        <div class="registro-footer">
            <a href="<?= $ruta ?>/producto/listado">Volver al Listado</a>
        </div>
    </div>
</main>


</body>
</html>
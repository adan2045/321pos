<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Producto</title>
  <style>
    body {
      background-color: #f5f5f5;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
      margin: 0;
      font-family: sans-serif;
    }

    .registro-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 500px;
      margin: 2rem;
      overflow: hidden;
    }

    .registro-header {
      background: linear-gradient(135deg, #ff6b6b 0%, #ff3333 100%);
      color: white;
      padding: 1.5rem;
      text-align: center;
    }

    .registro-form {
      padding: 2rem;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
      grid-column: span 2;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      color: #333;
      font-weight: bold;
    }

    .form-control {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .btn {
      width: 100%;
      padding: 0.8rem;
      border: none;
      border-radius: 4px;
      background: #1a1a1a;
      color: white;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .registro-footer {
      text-align: center;
      padding: 1rem;
      border-top: 1px solid #eee;
    }

    .registro-footer a {
      color: #ff3333;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <main>
    <div class="registro-container">
      <div class="registro-header">
        <h1>Editar Producto</h1>
      </div>

      <form action="<?= App::baseUrl() ?>/producto/actualizar" method="POST" class="registro-form">
        <input type="hidden" name="id" value="<?= $producto['id'] ?>">

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= $producto['nombre'] ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" required><?= $producto['descripcion'] ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Precio</label>
            <input type="number" name="precio" step="0.01" class="form-control" value="<?= $producto['precio'] ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Categoría</label>
            <select name="categoria" class="form-control">
              <option value="bebida" <?= $producto['categoria'] === 'bebida' ? 'selected' : '' ?>>Bebida</option>
              <option value="postre" <?= $producto['categoria'] === 'postre' ? 'selected' : '' ?>>Postre</option>
              <option value="comida" <?= $producto['categoria'] === 'comida' ? 'selected' : '' ?>>Comida</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn">Actualizar Producto</button>
      </form>

      <div class="registro-footer">
        <a href="<?= App::baseUrl() ?>/producto/listado">Volver al Listado</a>
      </div>
    </div>
  </main>
</body>
</html>
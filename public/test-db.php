<?php
require_once __DIR__.'/../app/Core/database.php';

try {
    // Obtener instancia de la base de datos (Singleton)
    $db = DataBase::getInstance();
    $connection = $db->getConnection();
    
    // 1. Prueba básica de conexión
    $stmt = $connection->query("SELECT 1 + 1 AS result, NOW() AS server_time");
    $data = $stmt->fetch();
    
    echo "<h1>✅ Conexión exitosa</h1>";
    echo "<pre>Resultado de prueba: " . print_r($data, true) . "</pre>";
    
    // 2. Probando los métodos estáticos originales
    echo "<h2>Probando métodos estáticos:</h2>";
    
    // a) Probando query()
    $usuarios = DataBase::query("SHOW TABLES");
    echo "<h3>Tablas en la base de datos:</h3>";
    echo "<pre>" . print_r($usuarios, true) . "</pre>";
    
    // b) Probando execute() (crea una tabla temporal si quieres probar)
    /*
    $creacion = DataBase::execute("CREATE TABLE IF NOT EXISTS prueba_temp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50)
    )");
    echo "<p>Tabla creada: $creacion</p>";
    */
    
    // 3. Probando transacción
    echo "<h3>Probando transacción:</h3>";
    $resultadoTransaccion = DataBase::ejecutar("SELECT * FROM information_schema.tables LIMIT 1");
    echo "<pre>" . print_r($resultadoTransaccion, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h1>❌ Error de conexión</h1>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . " (línea " . $e->getLine() . ")</p>";
    
    // Debug adicional
    echo "<h3>Configuración usada:</h3>";
    echo "<pre>Host: " . DataBase::getInstance()->getConnection()->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "</pre>";
}
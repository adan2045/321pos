<?php
/**
 * Conexión con la Base de Datos utilizando PDO
 */
class DataBase
{
    private static $host = "localhost";
    private static $dbname = "bar_db";
    private static $dbuser = "root";
    private static $dbpass = "";

    private static $instance = null;
    private static $dbh = null;
    private static $error;

    // Singleton
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Conexión PDO
    private static function connection()
    {
        if (self::$dbh === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname;
            $opciones = [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];

            try {
                self::$dbh = new PDO($dsn, self::$dbuser, self::$dbpass, $opciones);
                self::$dbh->exec('SET NAMES utf8');
                self::$dbh->exec('SET time_zone = "-03:00";');
            } catch (PDOException $e) {
                self::$error = $e->getMessage();
                throw new Exception("Error de conexión: " . self::$error);
            }
        }
        return self::$dbh;
    }

    public function getConnection()
    {
        return self::connection();
    }

    public static function query($sql, $params = [], $asArray = false)
    {
        $statement = self::prepareAndExecute($sql, $params);
        return $statement->fetchAll($asArray ? PDO::FETCH_ASSOC : PDO::FETCH_OBJ);
    }

    public static function execute($sql, $params = [])
    {
        return self::prepareAndExecute($sql, $params)->rowCount();
    }

    public static function rowCount($sql, $params = [])
    {
        return self::prepareAndExecute($sql, $params)->rowCount();
    }

    public static function getColumnsNames($table)
    {
        $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = :table";
        return self::query($sql, ['table' => $table]);
    }

    public static function ejecutar($sql)
    {
        $dbh = self::connection();
        try {
            $dbh->beginTransaction();
            $statement = $dbh->prepare($sql);
            $statement->execute();
            $dbh->commit();
            return ['state' => true, 'notification' => 'Operación exitosa'];
        } catch (PDOException $e) {
            $dbh->rollBack();
            return ['state' => false, 'notification' => $e->errorInfo[2] ?? 'Error desconocido'];
        }
    }

    // ✅ Devuelve directamente la excepción original para manejar errores específicos como clave foránea
    private static function prepareAndExecute($sql, $params = [])
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params); // sin try-catch para permitir manejo personalizado
        return $statement;
    }
}
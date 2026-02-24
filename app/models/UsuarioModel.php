<?php
namespace app\models;

use \DataBase;

class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DataBase();
    }

    public function guardar($nombre, $apellido, $email, $password, $dni, $rol)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, apellido, email, password, dni, rol)
                VALUES (?, ?, ?, ?, ?, ?)";

        return $this->db->query($sql, [
            $nombre,
            $apellido,
            $email,
            $passwordHash,
            $dni,
            $rol
        ]);
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM usuarios ORDER BY id DESC";
        return $this->db->query($sql, [], true); // Devuelve como array asociativo
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $resultados = DataBase::query($sql, [$id]);
        return $resultados[0] ?? null;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        return DataBase::execute($sql, [$id]);
    }

    public function actualizar($id, $nombre, $apellido, $email, $password, $dni, $rol)
    {
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, password = ?, dni = ?, rol = ? WHERE id = ?";
            $params = [$nombre, $apellido, $email, $passwordHash, $dni, $rol, $id];
        } else {
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, dni = ?, rol = ? WHERE id = ?";
            $params = [$nombre, $apellido, $email, $dni, $rol, $id];
        }

        return DataBase::execute($sql, $params);
    }

    // ✅ Nuevo método para login
    public function buscarPorEmail($email)
    {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $resultado = DataBase::query($sql, [$email], true);
        return $resultado[0] ?? null;
    }
}
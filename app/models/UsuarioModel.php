<?php
namespace app\models;

use \DataBase;

class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getInstance()->getConnection();
    }

    public function guardar($nombre, $apellido, $email, $password, $dni, $rol)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, apellido, email, password, dni, rol)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $apellido, $email, $passwordHash, $dni, $rol]);
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM usuarios ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
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

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function buscarPorEmail($email)
    {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
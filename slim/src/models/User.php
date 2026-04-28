<?php

namespace App\models;

use App\config\DB;
use PDO;

class User {
    public $id;
    public $name;
    public $email;
    public $balance;

    // Obtener todos los usuarios para el endpoint GET /users
    public static function getAll() {
        $db = DB::getConnection();
        //Retornamos NOMBRE y VALOR del portfolio
        $stmt = $db->query("SELECT id, name, balance FROM users"); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para validar el password
    public static function validarPassword($password) {
        // Mínimo 8 caracteres, una mayúscula, una minúscula, un número y un especial
        $regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        return preg_match($regex, $password);
    }
}

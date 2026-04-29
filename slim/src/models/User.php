<?php

namespace App\models;

use App\config\DB;
use PDO;

class User {
    private $id;
    private $name;
    private $email;
    private $password;
    private $balance;
    private $is_admin;
    private $token;
    private $token_expired_at;
    private $created_at;

    // Obtener todos los usuarios para el endpoint GET /users
    public static function getAll() {
        $db = DB::getConnection(); //Establece conexión con la base de datos, crea la "tubería"
        //Retornamos NOMBRE y VALOR del portfolio
        $stmt = $db->query("SELECT id, name, balance FROM users"); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para el POST /users
    public static function save($name, $email, $password) {
        $db = DB::getConnection();
    	$stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    	return $stmt->execute([$name, $email, $password]);
    }

    // Para el PUT /users/{id}
    public static function update($id, $name, $email) {
        $db = DB::getConnection();
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    	return $stmt->execute([$name, $email, $id]);
    }

    // Para cumplir con la aclaración de "no borrar si se está usando"
    public static function hasAssets($id) {
        $db = DB::getConnection();
    	// Chequeamos si tiene algo en su portfolio
    	$stmt = $db->prepare("SELECT COUNT(*) FROM portfolio WHERE user_id = ?");
    	$stmt->execute([$id]);
    	return $stmt->fetchColumn() > 0;
   }

    // Para el DELETE /users/{id} 
    public static function delete($id) {
        $db = DB::getConnection();
    	$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    	return $stmt->execute([$id]);
   }

    // Método para validar el password
    public static function validarPassword($password) {
        // Mínimo 8 caracteres, una mayúscula, una minúscula, un número y un especial
        $regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        return preg_match($regex, $password); //preg_match compara un patron contra un texto
    }
}

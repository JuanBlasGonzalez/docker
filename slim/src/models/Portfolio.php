<?php

namespace App\models;

use App\config\DB;
use PDO;

class Portfolio {
    public $id;
    public $user_id;
    public $asset_id;
    public $quantity;
   
    public static function getByUser($user_id) {
        $db = DB::getConnection();
        $stmt = $db->prepare("SELECT p.*, a.name, a.current_price 
                              FROM portfolio p 
                              JOIN assets a ON p.asset_id = a.id 
                              WHERE p.user_id = ?"); 
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar o insertar cantidad de un activo para un usuario
    public static function updateStock($user_id, $asset_id, $quantity) {
        $db = DB::getConnection();
        // Esta consulta es útil: si no existe el registro lo crea, si existe lo actualiza
        $stmt = $db->prepare("INSERT INTO portfolio (user_id, asset_id, quantity) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        return $stmt->execute([$user_id, $asset_id, $quantity, $quantity]);
    }

}

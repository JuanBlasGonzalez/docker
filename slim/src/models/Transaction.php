<?php

namespace App\models;

use App\config\DB;
use PDO;

class Transaction {
    // Para el endpoint GET /transactions 
    public static function getByUser($user_id) {
        $db = DB::getConnection();
        // Usamos prepare porque filtramos por el ID del usuario logueado 
        $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
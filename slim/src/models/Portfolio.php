<?php

namespace App\models;

use App\config\DB;
use PDO;

class Portfolio {
    public static function getByUser($user_id) {
        $db = DB::getConnection();
        $stmt = $db->prepare("SELECT p.*, a.name, a.current_price 
                              FROM portfolio p 
                              JOIN assets a ON p.asset_id = a.id 
                              WHERE p.user_id = ?"); 
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

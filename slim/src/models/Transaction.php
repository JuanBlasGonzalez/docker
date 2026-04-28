<?php

namespace App\models;

use App\config\DB;
use PDO;

class Transaction {
    public $id;
    public $user_id;
    public $asset_id;
    public $transaction_type; // 'BUY' o 'SELL'
    public $quantity;
    public $price_per_unit;
    public $total_amount;
    public $transaction_date;

    // Para el endpoint GET /transactions 
    public static function getByUser($user_id) {
        $db = DB::getConnection();
        // Usamos prepare porque filtramos por el ID del usuario logueado 
        $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
    // Registrar una compra o venta
    public static function create($user_id, $asset_id, $type, $quantity, $price) {
        $db = DB::getConnection();
        $stmt = $db->prepare("INSERT INTO transactions (user_id, asset_id, transaction_type, quantity, price_at_transaction) 
                              VALUES (?, ?, ?, ?, ?)");
        // 'type' sería 'BUY' o 'SELL' 
        return $stmt->execute([$user_id, $asset_id, $type, $quantity, $price]);
    }

}
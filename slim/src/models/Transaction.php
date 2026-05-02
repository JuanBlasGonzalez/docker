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
	
    // Para el endpoint GET /assets/{asset_id}/history/{quantity}
    public static function getHistoryForAsset($asset_id, $limit) {
        $db = DB::getConnection();
        // Seleccionamos solo los datos no sensibles que pide el TP, ordenados por fecha más reciente.
        $stmt = $db->prepare("SELECT transaction_date, transaction_type, quantity, price_per_unit 
                              FROM transactions 
                              WHERE asset_id = ? 
                              ORDER BY transaction_date DESC 
                              LIMIT ?");
        // Es importante especificar el tipo de dato para el LIMIT en sentencias preparadas.
        $stmt->bindValue(1, $asset_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar una compra o venta
    public static function create($user_id, $asset_id, $type, $quantity, $price) {
        $db = DB::getConnection();
        $total_amount = $quantity * $price;
        $stmt = $db->prepare("INSERT INTO transactions (user_id, asset_id, transaction_type, quantity, price_per_unit, total_amount) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        // 'type' sería 'BUY' o 'SELL' 
        return $stmt->execute([$user_id, $asset_id, $type, $quantity, $price, $total_amount]);
    }

}
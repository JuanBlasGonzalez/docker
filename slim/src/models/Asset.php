<?php

namespace App\models;

use App\config\DB;
use PDO;

class Asset {
    public $id;
    public $name;       
    public $current_price;
    public $last_update;

    public static function getAll() {
        $db = DB::getConnection();
        $stmt = $db->query("SELECT * FROM assets"); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Actualizar el precio en la DB tras calcular la variación
    public static function updatePrice($id, $newPrice) {
        $db = DB::getConnection();
        $stmt = $db->prepare("UPDATE assets SET current_price = ?, last_update = ? WHERE id = ?");
        // Usamos time() para actualizar el timestamp de la última variación
        return $stmt->execute([$newPrice, time(), $id]);
    }

    public static function variarPrecioPorTiempo($precioActual, $timestampUltimaVez, $volatilidadPorSegundo = 0.05) {
        // 1. Calcular cuántos segundos han pasado
        $tiempoPasado = time() - $timestampUltimaVez; 
        // Si no ha pasado tiempo, el precio no cambia
        if ($tiempoPasado <= 0) return $precioActual;
        // 2. Generar un cambio aleatorio (puede ser positivo o negativo)
        // mt_rand(-100, 100) / 100 nos da un número entre -1.0 y 1.0
        $direccion = mt_rand(-100, 100) / 100;
        // 3. El cambio total depende del tiempo que pasó
        $delta = $direccion * $volatilidadPorSegundo * $tiempoPasado;
        return $precioActual + $delta;
    }
}

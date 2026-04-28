<?php

namespace App\models;

use App\config\DB;
use PDO;

class Asset {

    public static function getAll() {
        $db = DB::getConnection();
        $stmt = $db->query("SELECT * FROM assets"); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

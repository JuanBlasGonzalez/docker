<?php
namespace App\Config; // <-- Importante: define su ubicación para el Autoload

use PDO;
use PDOException;

class DB {
    private static $connection;

    public static function getConnection() {
        if (!self::$connection) {
            // Datos del contenedor de base de datos de tu Docker
            $host = 'db'; 
            $dbname = 'seminariophp';
            $user = 'seminariophp';
            $pass = 'seminariophp';

            try {
                // Creamos la conexión PDO
                self::$connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Si falla, mostramos el error en formato JSON
                die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
            }
        }

        return self::$connection;
    }
}
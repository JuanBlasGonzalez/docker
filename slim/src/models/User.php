<?php

namespace App\models;

use App\config\DB;

class User {
    // Get all users from the database
    public static function getAll()
    {
        $db = DB::getConnection();
        $stmt = $db->query("SELECT * FROM usuario");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

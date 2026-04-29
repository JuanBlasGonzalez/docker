<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\Transaction;

class TransactionController {

    // Handle GET /transactions
    public static function getTransactionsByUser(Request $request, Response $response) {
        // TODO: Obtener el user_id del usuario logueado (desde el middleware de autenticación).
        $user_id = 1; // Usando un ID de ejemplo por ahora.
        $transactions = Transaction::getByUser($user_id);
        $response->getBody()->write(json_encode($transactions));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

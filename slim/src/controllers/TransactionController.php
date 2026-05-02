<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\Transaction;

class TransactionController {

    // Handle GET /transactions
    public static function getTransactionsByUser(Request $request, Response $response) {
        // 1. Obtener el usuario logueado que fue añadido a la petición por el AuthMiddleware.
        $loggedInUser = $request->getAttribute('user');

        // 2. Usar el ID del usuario logueado para buscar su historial de transacciones.
        // TODO: Implementar filtros opcionales del TP: ?type=buy/sell y/o ?asset_id=x
        $transactions = Transaction::getByUser($loggedInUser['id']);

        // 3. Devolver las transacciones encontradas.
        $response->getBody()->write(json_encode($transactions));
        return $response->withStatus(200);
    }
}

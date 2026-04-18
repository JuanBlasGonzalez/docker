<?php

namespace App\controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\config\DB;
use App\models\Transaction;

class TransactionController {

    // Handle GET /transactions
    public static function getTransactions(Request $request, Response $response) {
        $transactions = Transaction::getAll();
        $response->getBody()->write(json_encode($transactions));
        return $response;
    }
}

<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\Portfolio;

class PortfolioController {

    // Handle GET /portfolio
    public static function getPortfolioForUser(Request $request, Response $response) {
        // TODO: Obtener el user_id del usuario logueado (desde el middleware de autenticación).
        $user_id = 1; // Usando un ID de ejemplo por ahora.
        $portfolio = Portfolio::getByUser($user_id);
        $response->getBody()->write(json_encode($portfolio));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

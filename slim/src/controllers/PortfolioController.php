<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\Portfolio;

class PortfolioController {

    // Handle GET /portfolio
    public static function getPortfolioForUser(Request $request, Response $response) {
        // 1. Obtener el usuario logueado que fue añadido a la petición por el AuthMiddleware.
        $loggedInUser = $request->getAttribute('user');

        // 2. Usar el ID del usuario logueado para buscar su portfolio.
        $portfolio = Portfolio::getByUser($loggedInUser['id']);

        // 3. Devolver el portfolio encontrado.
        $response->getBody()->write(json_encode($portfolio));
        return $response->withStatus(200);
    }
}

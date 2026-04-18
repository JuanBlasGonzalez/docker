<?php

namespace App\controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\config\DB;
use App\models\Portfolio;

class PortfolioController {

    // Handle GET /portfolios
    public static function getPortfolios(Request $request, Response $response) {
        $portfolios = Portfolio::getAll();
        $response->getBody()->write(json_encode($portfolios));
        return $response;
    }
}

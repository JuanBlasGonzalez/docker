<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\Asset;

class AssetController {

    // Handle GET /assets
    public static function getAssets(Request $request, Response $response) {
        // TODO: Implementar filtros opcionales del TP: ?type={name}, min_price, max_price
        $assets = Asset::getAll();
        $response->getBody()->write(json_encode($assets));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\Asset;

class AssetController {

    // Handle GET /assets
    public static function getAssets(Request $request, Response $response) {
        // 1. Obtener todos los parámetros de la query string (ej: ?type=Bitcoin&min_price=50) como un array asociativo.
        $filters = $request->getQueryParams();

        // 2. Llamar a un nuevo método en el modelo, pasándole los filtros.
        $assets = Asset::getFiltered($filters);

        $response->getBody()->write(json_encode($assets));
        return $response->withStatus(200);
    }

    // Handle GET /assets/{asset_id}/history/{quantity}
    public static function getAssetHistory(Request $request, Response $response, array $args) {
        // 1. Obtener el ID del activo y la cantidad de registros a mostrar desde la URL.
        $asset_id = $args['asset_id'];
        $quantity = $args['quantity'];

        // 2. Validar la cantidad. El TP especifica un máximo de 5.
        // Usamos min() para asegurarnos de que no se pidan más de 5.
        // (int) convierte el string de la URL a un número.
        $limit = min((int)$quantity, 5);

        // 3. Si se pide 0 o un número negativo, no tiene sentido, así que lo ajustamos a 5 por defecto.
        if ($limit <= 0) {
            $limit = 5;
        }

        // 4. Llamar al modelo de Transacciones para obtener el historial del activo.
        $history = Transaction::getHistoryForAsset($asset_id, $limit);

        // 5. Devolver la respuesta.
        $response->getBody()->write(json_encode($history));
        return $response->withStatus(200);
    }
}

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
        // Se obtienen los posibles filtros de la URL (ej: ?type=buy)
        $filters = $request->getQueryParams();

        // 2. Usar el ID del usuario logueado para buscar su historial de transacciones.
        $transactions = Transaction::getByUser($loggedInUser['id'], $filters);

        // 3. Devolver las transacciones encontradas.
        $response->getBody()->write(json_encode($transactions));
        return $response->withStatus(200);
    }

    public static function buyAsset(Request $request, Response $response) {
        // 1. Obtener el usuario autenticado
        $loggedInUser = $request->getAttribute('user');
        $user_id = $loggedInUser['id'];

        // 2. Obtener los datos del cuerpo de la petición
        $data = $request->getParsedBody();
        $asset_id = $data['asset_id'] ?? null;
        $quantity = $data['quantity'] ?? null;

        // 3. Validar los datos de entrada
        if (!$asset_id || !$quantity) {
            $response->getBody()->write(json_encode(['error' => 'El asset_id y la quantity son requeridos.']));
            return $response->withStatus(400);
        }
        if (!is_int($quantity) || $quantity <= 0) {
            $response->getBody()->write(json_encode(['error' => 'La cantidad debe ser un numero entero mayor que cero.']));
            return $response->withStatus(400);
        }


        //preguntar si esta bien hacer la conexion a la base de datos en el controlador o si es mejor hacerla en el modelo.
        // Obtenemos la conexión a la BD para manejar la transacción manualmente
        $db = \App\config\DB::getConnection();

        try {
            // 4. Iniciar la transacción
            $db->beginTransaction();

            // 5. Obtener el activo y su precio actual (bloqueando la fila para evitar cambios)
            $stmt = $db->prepare("SELECT * FROM assets WHERE id = ? FOR UPDATE");
            $stmt->execute([$asset_id]);
            $asset = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$asset) {
                $db->rollBack();
                $response->getBody()->write(json_encode(['error' => 'El activo especificado no existe.']));
                return $response->withStatus(404);
            }

            $price_per_unit = $asset['current_price'];
            $total_cost = $quantity * $price_per_unit;

            // 6. Obtener el saldo del usuario (bloqueando la fila para evitar inconsistencias)
            $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$user_id]);
            $user_balance = $stmt->fetchColumn();

            // Comprobación defensiva: ¿se encontró el saldo del usuario?
            if ($user_balance === false) {
                $db->rollBack();
                $response->getBody()->write(json_encode(['error' => 'No se pudo encontrar el usuario para la transaccion.']));
                return $response->withStatus(404);
            }
            // 7. Verificar si el usuario tiene saldo suficiente
            if ((float)$user_balance < $total_cost) {
                $db->rollBack();
                $response->getBody()->write(json_encode(['error' => 'Saldo insuficiente para realizar la compra.']));
                return $response->withStatus(400);
            }

            // 8. Ejecutar las operaciones de la compra
            // 8a. Restar el costo del saldo del usuario
            $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$total_cost, $user_id]);

            // 8b. Actualizar el portfolio del usuario
            \App\models\Portfolio::updateStock($user_id, $asset_id, $quantity);

            // 8c. Registrar la transacción en el historial
            \App\models\Transaction::create($user_id, $asset_id, 'buy', $quantity, $price_per_unit);

            // 9. Si todo fue exitoso, confirmar la transacción
            $db->commit();

            // 10. Devolver una respuesta de éxito
            $response->getBody()->write(json_encode(['message' => 'Compra realizada con exito.']));
            return $response->withStatus(200);

        } catch (\Exception $e) {
            // Si ocurre cualquier error, deshacer la transacción
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            // Devolver un error de servidor

            // Para depuración, es útil registrar el error real.
            // En un entorno de producción, esto iría a un archivo de log.
            error_log('Error en la compra de activo: ' . $e->getMessage());

            // Devolver un error de servidor genérico al cliente
            $response->getBody()->write(json_encode(['error' => 'Ocurrio un error al procesar la compra.']));
            return $response->withStatus(500);
        }
    }

    public static function sellAsset(Request $request, Response $response) {
        // 1. Obtener el usuario autenticado
        $loggedInUser = $request->getAttribute('user');
        $user_id = $loggedInUser['id'];

        // 2. Obtener los datos del cuerpo de la petición
        $data = $request->getParsedBody();
        $asset_id = $data['asset_id'] ?? null;
        $quantity = $data['quantity'] ?? null;

        // 3. Validar los datos de entrada
        if (!$asset_id || !$quantity) {
            $response->getBody()->write(json_encode(['error' => 'El asset_id y la quantity son requeridos.']));
            return $response->withStatus(400);
        }
        if (!is_int($quantity) || $quantity <= 0) {
            $response->getBody()->write(json_encode(['error' => 'La cantidad debe ser un numero entero mayor que cero.']));
            return $response->withStatus(400);
        }

        $db = \App\config\DB::getConnection();

        try {
            $db->beginTransaction();

            // 4. Obtener el activo y su precio actual (bloqueando la fila para evitar cambios de precio durante la venta).
            //    Este paso también valida que el activo exista ANTES de cualquier otra cosa.
            $stmt = $db->prepare("SELECT * FROM assets WHERE id = ? FOR UPDATE");
            $stmt->execute([$asset_id]);
            $asset = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$asset) {
                $db->rollBack();
                $response->getBody()->write(json_encode(['error' => 'El activo especificado no existe.']));
                return $response->withStatus(404);
            }

            $price_per_unit = $asset['current_price'];
            $total_value = $quantity * $price_per_unit;

            // 5. Ahora que sabemos que el activo existe, verificamos que el usuario posea suficientes para vender.
            $user_asset_quantity = \App\models\Portfolio::getAssetQuantityForUser($user_id, $asset_id);

            if ($user_asset_quantity < $quantity) {
                $db->rollBack();
                $response->getBody()->write(json_encode(['error' => 'No tienes suficientes activos para vender. Cantidad poseida: ' . $user_asset_quantity]));
                return $response->withStatus(400);
            }

            // 6. Ejecutar las operaciones de la venta
            // 6a. Añadir el valor de la venta al saldo del usuario
            $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$total_value, $user_id]);

            // 6b. Restar el activo del portfolio del usuario. Usamos una cantidad negativa.
            \App\models\Portfolio::updateStock($user_id, $asset_id, -$quantity);

            // 6c. Registrar la transacción en el historial
            \App\models\Transaction::create($user_id, $asset_id, 'sell', $quantity, $price_per_unit);

            // 7. Si todo fue exitoso, confirmar la transacción
            $db->commit();

            $response->getBody()->write(json_encode(['message' => 'Venta realizada con exito.']));
            return $response->withStatus(200);

        } catch (\Exception $e) {
            if ($db->inTransaction()) { $db->rollBack(); }

            // Para depuración, es útil registrar el error real.
            error_log('Error en la venta de activo: ' . $e->getMessage());

            $response->getBody()->write(json_encode(['error' => 'Ocurrio un error al procesar la venta.']));
            return $response->withStatus(500);
        }
    }
}

<?php
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
// Importo los controladores
use App\controllers\UserController;
use App\controllers\AssetController;
use App\controllers\TransactionController;
use App\controllers\PortfolioController;

// Importas la base de datos (si la necesitas en el index)
use App\config\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add( function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json')
    ;
});

// ACÁ VAN LOS ENDPOINTS

//probando el endpoint raíz para verificar que el servidor funciona correctamente
$app->get('/', function (Request $request, Response $response, $args) {
        $response->getBody()->write("Hello world! Funcionando en Docker");
        return $response;
});

// --- Autenticación ---
$app->post('/login', AuthController::login);
$app->post('/logout', AuthController::logout);

// --- Usuarios ---
$app->post('/users', UserController::create);
$app->get('/users/{user_id}', UserController::getUserById);
$app->put('/users/{user_id}', UserController::update);
$app->get('/users', UserController::getUsers);

// --- Activos (El Mercado) ---
$app->get('/assets', AssetController::getAssets);
$app->put('/assets', AssetController::updateAssets);
$app->get('/assets/{asset_id}/history/{quantity}', AssetController::getAssetHistory);

// --- Operaciones ---
$app->post('/trade/buy', TransactionController::buyAsset);
$app->post('/trade/sell', TransactionController::sellAsset);

// --- Portfolio e Historial ---
$app->get('/portfolio', PortfolioController::getPortfolioForUser);
$app->delete('/portfolio/{asset_id}', PortfolioController::deletePortfolio);
$app->get('/transactions', TransactionController::getTransactionsByUser);


$app->run();

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
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world! Funcionando en Docker");
    return $response;
});

$app->run();

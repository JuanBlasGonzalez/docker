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

// GET: Retrieve users using controller logic
$app->get('/users', \UserController::class . '::getUsers ');

// GET: Retrieve all users
$app->get('/users', function (Request $request, Response $response) {
    $db = DB::getConnection();
    $stmt = $db->query("SELECT * FROM usuario");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response;
});

// POST: Create a new user
$app->post('/users', function (Request $request, Response $response) {
    try {
        $db = DB::getConnection();
        $data = $request->getParsedBody();

        $stmt = $db->prepare("INSERT INTO usuario (nombre, usuario, password) VALUES (:nombre, :usuario, :password)");
        $success = $stmt->execute([
            ':nombre' => $data['nombre'] ?? '',
            ':usuario' => $data['usuario'] ?? '',
            ':password' => $data['password'] ?? ''
        ]);

        if ($success) {
            $response->getBody()->write(json_encode(['status' => 'User created']));
        } else {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'User could not be created']));
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500);
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    }

    return $response;
});

// PUT: Update an existing user
$app->put('/users/{id}', function (Request $request, Response $response, array $args) {
    try {
        $db = DB::getConnection();
        $id = $args['id'];
        $data = $request->getParsedBody();

        $stmt = $db->prepare("UPDATE usuario SET nombre = :nombre, usuario = :usuario, password = :password WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'] ?? '',
            ':usuario' => $data['usuario'] ?? '',
            ':password' => $data['password'] ?? ''
        ]);

        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(['status' => 'User updated']));
        } else {
            $response = $response->withStatus(404);
            $response->getBody()->write(json_encode(['error' => 'User not found or no changes made']));
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500);
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    }

    return $response;
});

// DELETE: Remove a user by ID
$app->delete('/users/{id}', function (Request $request, Response $response, array $args) {
    try {
        $db = DB::getConnection();
        $id = $args['id'];

        $stmt = $db->prepare("DELETE FROM usuario WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(['status' => 'User deleted']));
        } else {
            $response = $response->withStatus(404);
            $response->getBody()->write(json_encode(['error' => 'User not found']));
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500);
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    }

    return $response;
});

$app->run();

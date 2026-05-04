<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use App\middleware\AuthMiddleware;
use App\models\User;
use \DateTime;

class AuthController {

    public static function login(Request $request, Response $response) {
        // 1. Obtiene los datos enviados en el cuerpo de la petición (ej: el JSON con email y password).
        $data = $request->getParsedBody();
        
        // 2. Extrae el email y la contraseña. El '?? null' es una seguridad para evitar errores si no vienen.
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        
        // 3. Valida que ambos campos hayan sido enviados. Si no, devuelve un error 400 (Bad Request).
        if (!$email || !$password) {
            $response->getBody()->write(json_encode(['error' => 'Email y contraseña son requeridos.']));
            return $response->withStatus(400);
        }
        
        // 4. Usa el modelo User para buscar en la base de datos un usuario con ese email.
        $user = User::findByEmail($email);
        
        // 5. Verifica las credenciales. Hay dos posibilidades de fallo:
        //    a) El usuario no existe (`!$user`).
        //    b) La contraseña enviada no coincide con la guardada en la DB (`!password_verify`). $user['password'] devuelve la contraseña hasheada 
        //    En ambos casos, se devuelve un error 401 (Unauthorized) con un mensaje genérico para no dar pistas a atacantes.
        if (!$user || !password_verify($password, $user['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales inválidas.']));
            return $response->withStatus(401);
        }

 	    //El token se arma con el id de usuario y la fecha de expiración
	    //le fijo una duración de 5min
        $expire = (new DateTime("now"))->modify("+5 minutes")->format("Y-m-d H:i:s");
        $token = JWT::encode(["usuario"=> $user['id'], "expired_at" => $expire], AuthMiddleware::$secret, 'HS256');

        //Guardo el token en la base de datos para luego poder invalidarlo en el logout
        if (User::updateToken($user['id'], $token, $expire)){
	        //Devuelvo el token como respuesta en el header
	        $response = $response->withHeader('token', $token);
	        //Envío en el Body un mensaje
	        $response->getBody()->write(json_encode(['mensaje' => 'Usuario loggeado!']));
	        $response = $response->withStatus(200);
	        return $response;         
        }      
        //Si hubo un error al guardar en la DB, devuelve un error 500 (Internal Server Error).
        $response->getBody()->write(json_encode(['error' => 'No se pudo guardar el token.']));
        return $response->withStatus(500);
    }

    public static function logout(Request $request, Response $response) {
        //Implementar el logout para token jwt. 
        //Guardo el token en la bd y lo borro? Contradice el caracter stateless de jwt.
    }
}

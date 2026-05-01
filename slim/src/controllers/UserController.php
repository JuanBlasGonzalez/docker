<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\models\User;

class UserController {

    // Handle GET /users
    public static function getUsers(Request $request, Response $response) {
        // TODO: Implementar lógica de autorización (solo admin).
        // TODO: Modificar la consulta para que devuelva nombre y valor total del portfolio.
        $users = User::getAll();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Handle POST /users
    public static function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Validaciones del TP
        if (empty($name) || !preg_match('/^[a-zA-Z\s]+$/', $name)) {
            $response->getBody()->write(json_encode(['error' => 'El nombre es inválido. No puede ser vacío y solo debe contener letras y espacios.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->getBody()->write(json_encode(['error' => 'El formato del email es inválido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        if (!User::validarPassword($password)) {
            $response->getBody()->write(json_encode(['error' => 'La contraseña no cumple los requisitos: mínimo 8 caracteres, una mayúscula, una minúscula, un número y un caracter especial.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            User::save($name, $email, $hashedPassword);
            $response->getBody()->write(json_encode(['message' => 'Usuario creado con éxito. Recibió un bono de 1000 USD.']));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Error de entrada duplicada (email único)
                 $response->getBody()->write(json_encode(['error' => 'El email ya está registrado.']));
                 return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }
            $response->getBody()->write(json_encode(['error' => 'Error en la base de datos al crear el usuario.']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
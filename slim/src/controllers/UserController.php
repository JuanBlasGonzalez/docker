<?php

namespace App\controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\config\DB;
use App\models\User;

class UserController {

    // Handle GET /users
    public static function getUsers(Request $request, Response $response) {
        $users = User::getAll();
        $response->getBody()->write(json_encode($users));
        return $response;
    }
}

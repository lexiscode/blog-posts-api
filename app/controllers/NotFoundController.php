<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class NotFoundController
{

    // This handles url routes that are non-existent
    public function notFound(Request $request, Response $response)
    {
      
        $errorResponse = array(
            "status" => 404,
            "message" => "Page Not Found."
        );

        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
}


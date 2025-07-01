<?php

use Slim\Psr7\Response;
use App\Controllers\NotFoundController;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

// This below handles all 404 routes error response
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class, 
    function (ServerRequestInterface $request) {
        $response = new Response(); 
        $controller = new NotFoundController(); 
        return $controller->notFound($request, $response);
    }
);

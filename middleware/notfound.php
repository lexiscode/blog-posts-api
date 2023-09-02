<?php

// This below handles all 404 routes error response
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpNotFoundException::class, 
    function (Psr\Http\Message\ServerRequestInterface $request) {
        $response = new \Slim\Psr7\Response(); // Create a concrete Response object
        $controller = new \App\Controllers\NotFoundController(); // instantiate the controller class
        return $controller->notFound($request, $response);
    }
);

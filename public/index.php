<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Tuupola\Middleware\JwtAuthentication;
use Firebase\JWT\JWT;

use Dotenv\Dotenv;


require __DIR__ . '/../vendor/autoload.php';


// DotENV configuration
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

// Load the configuration settings from settings.php
$settings = require __DIR__ . '/../config/settings.php';

// Create a new Slim app instance
$app = AppFactory::create();


// Include User Authentication Routes
require __DIR__ . '/../routes/authenticate.php';


// Define a route for the root URL
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello there, this is my Blog Post API project!");
    return $response;
});


// Define a route for API documentation
$app->get('/openapi', function (Request $request, Response $response) {
    // Include the code to generate and return the OpenAPI documentation here
    require '/openapi/index.php';
});


// Include Additional Routes for Posts and Categories
require __DIR__ . '/../routes/posts.php';
require __DIR__ . '/../routes/categories.php';


/* JWT Authentication Middleware */
require __DIR__ . '/../middleware/jwt_proxy.php';


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


// Run the Slim App
$app->run();


<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use DI\ContainerBuilder;

use Tuupola\Middleware\JwtAuthentication;
use Firebase\JWT\JWT;

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';



// DotENV configuration
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

// Load the configuration settings from settings.php
$settings = require __DIR__ . '/../config/settings.php';


// Create a new Slim app instance, below was used without the logger being considered
// $app = AppFactory::create();

/* Set up Laminas Logger to log to a file*/
$logger = new Logger();
$logger->addWriter(new Stream('../log/app.log')); // Define your log file path here

// Create a container builder
$containerBuilder = new ContainerBuilder();

// Add definitions to the container, including the logger
$containerBuilder->addDefinitions([
    Logger::class => function () use ($logger) {
        return $logger;
    },
]);

// Build the container
$container = $containerBuilder->build();

// Create a another new Slim app instance with the container
$app = AppFactory::createFromContainer($container);



// Define a route for API documentation
$app->get('/openapi', function (Request $request, Response $response) {
    // Include the code to generate and return the OpenAPI documentation here
    require '/openapi/index.php';
});


// Include User Authentication Routes
require __DIR__ . '/../routes/authenticate.php';

/* Include Additional Routes for Posts and Categories */
require __DIR__ . '/../routes/posts.php';
require __DIR__ . '/../routes/categories.php';


/* JWT Authentication Middleware */
require __DIR__ . '/../middleware/jwt_proxy.php';


/* This below handles all 404 routes error response */
require __DIR__ . '/../middleware/notfound.php';


// Run the Slim App
$app->run();


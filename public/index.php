<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Tuupola\Middleware\JwtAuthentication;
use Firebase\JWT\JWT;

use Dotenv\Dotenv;


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../database/db.php';
require __DIR__ . '/../functions/resourceExists.php';

// DotENV configuration
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

// Load the configuration settings from settings.php
$settings = require __DIR__ . '/../config/settings.php';

// Create a new Slim app instance
$app = AppFactory::create();


// Include User Authentication Routes
require __DIR__ . '/../routes/user_auth.php';


// Define a route for the root URL
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello there, this is a blog post API project!");
    return $response;
});


// Include Additional Routes for Posts and Categories
require __DIR__ . '/../routes/posts.php';
require __DIR__ . '/../routes/categories.php';


/* JWT Authentication Middleware
require __DIR__ . '/../middleware/jwt_proxy.php';
*/

// Run the Slim App
$app->run();


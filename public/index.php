<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../functions/resourceExists.php';

// Create a new Slim app instance
$app = AppFactory::create();

// Define a route for the root URL
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello there, this is a blog post API project!");
    return $response;
});


// Include the routes defined in posts.php
require __DIR__ . '/../routes/posts.php';

// Include the routes defined in categories.php
require __DIR__ . '/../routes/categories.php';

$app->run();



<?php

use App\Controllers\AuthController;
use App\Models\Auth;
use App\Models\ResourceExists;
use App\Models\Database\DbConnect;


// Create a PDO instance for database connection
$db = (new DbConnect())->getConn();

// Retrieve the ContainerInterface from the Slim app container
$container = $app->getContainer();

// Inject the PDO instance into the BlogPost model
$blogAuthModel = new Auth($db, $container);

// Inject the PDO instance into the ResourceExists model
$resourceExistsModel = new ResourceExists($db);

// Inject the BlogPost model into the controller
$blogAuthController = new AuthController($blogAuthModel, $resourceExistsModel, $container);



// Login and generate a JWT Token
$app->post('/login', [$blogAuthController, 'authLogin']);

// Create an account in order to generate a JWT Token
$app->post('/register', [$blogAuthController, 'authRegister']);


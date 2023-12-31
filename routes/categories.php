<?php

use App\Controllers\BlogCategoryController;
use App\Models\BlogCategory;
use App\Models\ResourceExists;
use App\Models\Database\DbConnect;


// Create a PDO instance for database, and gets the connection
$db = (new DbConnect())->getConn();

// Retrieve the ContainerInterface from the Slim app container
$container = $app->getContainer();

// Inject the PDO instance into the BlogPost model
$blogCategoryModel = new BlogCategory($db, $container);
 
// Inject the PDO instance into the ResourceExists model
$resourceExistsModel = new ResourceExists($db);
 
// Inject the BlogPost model into the controller
$blogCategoryController = new BlogCategoryController($blogCategoryModel, $resourceExistsModel, $container);
 

// Get all our categories from the database
$app->get('/categories', [$blogCategoryController, 'getAllCategories']);

// Get all a specific category from the database
$app->get('/categories/{id:\d+}', [$blogCategoryController, 'getCategoryById']);

// Create categories from the api to the database
$app->post('/categories', [$blogCategoryController, 'createCategory']);

// Edit/Update a categories from the api to the database, using PUT
$app->put('/categories/edit/{id:\d+}', [$blogCategoryController, 'putCategory']);

// Edit/Update a categories from the api to the database, using PATCH
$app->patch('/categories/edit/{id:\d+}', [$blogCategoryController, 'patchCategory']);

// Delete a categories from the api to the database
$app->delete('/categories/{id:\d+}', [$blogCategoryController, 'deleteCategory']);

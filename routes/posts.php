<?php

use App\Controllers\BlogPostController;
use App\Models\BlogPost;
use App\Models\ResourceExists;
use App\Models\Database\DbConnect;


// Create a PDO instance for database connection
$db = (new DbConnect())->getConn();

// Retrieve the ContainerInterface from the Slim app container
$container = $app->getContainer();

// Inject the PDO instance into the BlogPost model
$blogPostModel = new BlogPost($db, $container);

// Inject the PDO instance into the ResourceExists model
$resourceExistsModel = new ResourceExists($db);


// Inject the BlogPost model into the controller
$blogPostController = new BlogPostController($blogPostModel, $resourceExistsModel, $container);


/**
 * Get all our posts from the database
 */
$app->get('/posts', [$blogPostController, 'getAllPosts']);

/**
 * Get a specific post from the database by ID or slug
 */
$app->get('/posts/{id:\d+}', [$blogPostController, 'getPostById']);

/**
 * Get a specific post from the database by their slug
 */
$app->get('/posts/slug/{slug}', [$blogPostController, 'getBySlug']);

/**
 * Create post from the api to the database
 */
$app->post('/posts/create', [$blogPostController, 'createPost']);

/**
 * Edit/Update a post from the api to the database, using PATCH
 */
$app->patch('/posts/edit/{id:\d+}', [$blogPostController, 'patchPost']);

/**
 * Delete a post from the api to the database
 */
$app->delete('/posts/{id:\d+}', [$blogPostController, 'deletePost']);


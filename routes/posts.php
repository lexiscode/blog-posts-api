<?php

use App\Controllers\BlogPostController;
use App\Models\BlogPost;
use App\Models\ResourceExists;
use App\Models\Database\DbConnect;
// Function to check if a resource with a given ID exists is available globally in index.php


 // Create a PDO instance for database connection
$db = (new DbConnect())->getConn();

// Inject the PDO instance into the BlogPost model
$blogPostModel = new BlogPost($db);

// Inject the PDO instance into the ResourceExists model
$resourceExistsModel = new ResourceExists($db);

// Inject the BlogPost model into the controller
$blogPostController = new BlogPostController($blogPostModel, $resourceExistsModel);

/**
 * Get all our posts from the database
 */
$app->get('/posts', [$blogPostController, 'getAllPosts']);

/**
 * Get a specific post from the database by ID or slug
 */
$app->get('/posts/{id}', [$blogPostController, 'getPostById']);


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
$app->patch('/posts/edit/{id}', [$blogPostController, 'updatePost']);


/**
 * Delete a post from the api to the database
 */
$app->delete('/posts/delete/{id}', [$blogPostController, 'deletePost']);




<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;


// Function to check if a resource with a given ID exists is available globally in index.php

/**
 * Get all our posts from the database
 */
$app->get('/posts', function (Request $request, Response $response) {

    $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
                   c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc 
            ON p.id = pc.post_id
            LEFT JOIN categories c 
            ON pc.category_id = c.id";

    try {

        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db = null;

        $posts = [];
        foreach ($rows as $row) {
            $postId = $row['id'];
            if (!isset($posts[$postId])) {
                $post = [
                    'id' => $postId,
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'content' => $row['content'],
                    'thumbnail' => $row['thumbnail'],
                    'author' => $row['author'],
                    'posted_at' => $row['posted_at'],
                    'categories' => [],
                ];
                $posts[$postId] = $post;
            }
            if (!is_null($row['category_id'])) {
                $category = [
                    'id' => $row['category_id'],
                    'name' => $row['category_name'],
                    'description' => $row['category_description'],
                ];
                $posts[$postId]['categories'][] = $category;
            }
        }

        $response->getBody()->write(json_encode(array_values($posts)));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $error = array("error-message" => $e->getMessage());
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



/**
 * Get a specific post from the database by ID or slug
 */
$app->get('/posts/{identifier}', function (Request $request, Response $response, array $args) {
    $identifier = $args['identifier'];

    // Check if the resource exists for posts by ID or slug
    if (!resourceExistsPost($identifier) && !resourceExistsBySlug($identifier)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID or slug.",
            "resource-identifier" => $identifier
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
               c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.id = :id";

    $sqlSlug = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
                   c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.slug = :slug";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmtSlug = $conn->prepare($sqlSlug);

        $stmt->bindValue(':id', $identifier, PDO::PARAM_INT);
        $stmtSlug->bindValue(':slug', $identifier, PDO::PARAM_STR);

        $stmt->execute();
        $stmtSlug->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultSlug = $stmtSlug->fetchAll(PDO::FETCH_ASSOC);

        $db = null;

        if (!empty($result)) {
            // Handle the case of fetching by ID
            $post = formatPostData($result);
        } elseif (!empty($resultSlug)) {
            // Handle the case of fetching by slug
            $post = formatPostData($resultSlug);
        } else {
            // Handle the case of no matching post
            $errorResponse = array(
                "error-message" => "Resource not found with this ID or slug.",
                "resource-identifier" => $identifier
            );
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($post));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});




/**
 * Create post from the api to the database
 */
$app->post('/posts/create', function (Request $request, Response $response, array $args) {

    // Get the JSON content from the request body
    $jsonBody = $request->getBody();
    $data = json_decode($jsonBody, true);
    
    // Check if JSON decoding was successful
    if ($data === null) {
        // Invalid JSON data
        $errorResponse = array("error-message" => "Invalid JSON data");
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    
    // Get the values from the decoded JSON data and sanitize
    $title = filter_var($data['title'], FILTER_SANITIZE_STRING);
    $slug = htmlspecialchars($data['slug']);
    $content = filter_var($data['content'], FILTER_SANITIZE_STRING);
    $thumbnailBase64 = htmlspecialchars($data['thumbnail']);
    $author = filter_var($data['author'], FILTER_SANITIZE_STRING);
    // Extract categories from JSON data and sanitize too
    $categories = $data['categories'];

    // Decode Base64 image data
    $thumbnailData = base64_decode($thumbnailBase64);
    // Generate a unique filename
    $thumbnailFilename = uniqid() . '.png'; // You can change the file extension
    // Define the path to store the images
    $thumbnailPath = 'thumbnails/' . $thumbnailFilename;

    // Ensure the directory exists, create it if not
    $thumbnailDirectory = dirname($thumbnailPath);
    if (!is_dir($thumbnailDirectory)) {
        mkdir($thumbnailDirectory, 0755, true);
    }

    // Save the decoded image data to the specified path
    file_put_contents($thumbnailPath, $thumbnailData);
    // Prepare the thumbnail URL
    $thumbnailUrl = 'http://localhost:200/' . $thumbnailPath;


    $sqlInsertPost = "INSERT INTO posts (title, slug, content, thumbnail, author) 
                      VALUES (:title, :slug, :content, :thumbnail, :author)";
    
    $sqlInsertCategoryRelation = "INSERT INTO posts_categories (post_id, category_id) 
                                  VALUES (:post_id, :category_id)";

    try {
        $db = new Db();
        $conn = $db->connect();
        $conn->beginTransaction();

        // Insert post information
        $stmtInsertPost = $conn->prepare($sqlInsertPost);
        $stmtInsertPost->bindParam(':title', $title, PDO::PARAM_STR);
        $stmtInsertPost->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmtInsertPost->bindParam(':content', $content, PDO::PARAM_STR);
        $stmtInsertPost->bindParam(':thumbnail', $thumbnailUrl, PDO::PARAM_STR);
        $stmtInsertPost->bindParam(':author', $author, PDO::PARAM_STR);
        $isPostInserted = $stmtInsertPost->execute();

        if (!$isPostInserted) {
            $conn->rollback();
            // Prepare the response data
            $responseData = array(
                "success" => false,
                "message" => "Failed to insert post data."
            );
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        // Fetch the last inserted post ID
        $postId = $conn->lastInsertId();

        // Insert category relationships
        $stmtInsertCategoryRelation = $conn->prepare($sqlInsertCategoryRelation);
        foreach ($categories as $categoryId) {
            $stmtInsertCategoryRelation->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmtInsertCategoryRelation->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            $stmtInsertCategoryRelation->execute();
        }

        $conn->commit();
        $db = null;

        // Prepare the response data
        $responseData = array(
            "success" => true,
            "message" => "Post and categories inserted successfully.",
            "thumbnail" => $thumbnailUrl
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        $conn->rollback();
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



/**
 * Handling error message in a case whereby the user mistakenly set method as PUT, 
 * rather than using PATCH as the method.
 */
$app->put('/posts/edit/{id}', function (Request $request, Response $response, array $args) {
    $id = htmlspecialchars($args['id']);

    // Check if the resource exists for posts
    if (!resourceExistsPost($id) || resourceExistsPost($id)) {
        $errorResponse = array(
            "error-message" => "Method not allowed. Must be one of: PATCH.",
            "resource-method" => "PUT"
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

});



/**
 * Edit/Update a post from the api to the database, using PATCH
 */
$app->patch('/posts/edit/{id}', function (Request $request, Response $response, array $args) {
    $id = htmlspecialchars($args['id']);

    // Get the JSON content from the request body
    $jsonBody = $request->getBody();
    $data = json_decode($jsonBody, true);
    
    // Check if JSON decoding was successful
    if ($data === null) {
        // Invalid JSON data
        $errorResponse = array("error-message" => "Invalid JSON data");
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Check if the resource ID exists for posts
    if (!resourceExistsPost($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "UPDATE posts SET ";
    $params = array();

    // Build the SET clause and parameter bindings for the update
    foreach ($data as $field => $value) {
        if ($field === 'title') {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        } elseif ($field === 'slug') {
            $value = htmlspecialchars($value);
        } elseif ($field === 'content') {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        } elseif ($field === 'thumbnail') {
            // Update the thumbnail if provided
            $thumbnailBase64 = $data['thumbnail'];
            $thumbnailData = base64_decode($thumbnailBase64);
            $thumbnailFilename = uniqid() . '.png'; // You can change the file extension
            $thumbnailPath = 'thumbnails/' . $thumbnailFilename;
            $thumbnailDirectory = dirname($thumbnailPath);
            
            if (!is_dir($thumbnailDirectory)) {
                mkdir($thumbnailDirectory, 0755, true);
            }
            
            file_put_contents($thumbnailPath, $thumbnailData);
            $thumbnailUrl = 'http://localhost:200/' . $thumbnailPath;
            
            // Bind the URL directly to the parameter
            $value = $thumbnailUrl;

        } elseif ($field === 'author') {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        } elseif ($field === 'categories') {
            // Skip categories field for now
            continue;
        }

        $sql .= "$field = :$field, ";
        $params[$field] = $value;
    }

    // Remove the trailing comma and space
    $sql = rtrim($sql, ", ");

    // Add the WHERE condition
    $sql .= " WHERE id = :id";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        // Bind parameter values dynamically, depending on the available fields to be edited
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        foreach ($params as $field => &$value) {
            $stmt->bindParam(":$field", $value);
        }

        $isDataUpdated = $stmt->execute();

        // Update the post's categories
        if (isset($data['categories']) && is_array($data['categories'])) {
            // Delete existing category relations for the post
            $deleteCategoriesSql = "DELETE FROM posts_categories WHERE post_id = :post_id";
            $deleteCategoriesStmt = $conn->prepare($deleteCategoriesSql);
            $deleteCategoriesStmt->bindParam(':post_id', $id, PDO::PARAM_INT);
            $deleteCategoriesStmt->execute();

            // Insert new category relations for the post
            $insertCategorySql = "INSERT INTO posts_categories (post_id, category_id) VALUES (:post_id, :category_id)";
            $insertCategoryStmt = $conn->prepare($insertCategorySql);
            $insertCategoryStmt->bindParam(':post_id', $id, PDO::PARAM_INT);
            foreach ($data['categories'] as $categoryId) {
                $insertCategoryStmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
                $insertCategoryStmt->execute();
            }
        }

        $db = null;

        // Prepare the response data
        $responseData = array(
            "success" => $isDataUpdated,
            "message" => $isDataUpdated ? "Data updated successfully." : "Failed to update data."
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($isDataUpdated ? 200 : 500);

    } catch (PDOException $e) {
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



// Delete a post from the api to the database
$app->delete('/posts/delete/{id}', function (Request $request, Response $response, array $args) {

    $id = htmlspecialchars($args['id']);

    // Check if the resource exists for posts
    if (!resourceExistsPost($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "DELETE FROM posts WHERE id= :id";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $isDataInserted = $stmt->execute();

        $db = null;

        // Prepare the response data
        $responseData = array(
            "success" => $isDataInserted,
            "message" => $isDataInserted ? "Data deleted successfully." : "Failed to delete data."
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($isDataInserted ? 200 : 500);
            
    } catch (PDOException $e) {
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});






/**
 * Get a specific post from the database by their ID
 */
/*
$app->get('/posts/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    // Check if the resource exists for posts
    if (!resourceExistsPost($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    
    $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
                   c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.id = :id";

    try{
        $db = new Db();
        $conn = $db->connect();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $post = array(
            "id" => $result[0]["id"],
            "title" => $result[0]["title"],
            "slug" => $result[0]["slug"],
            "content" => $result[0]["content"],
            "thumbnail" => $result[0]["thumbnail"],
            "author" => $result[0]["author"],
            "posted_at" => $result[0]["posted_at"],
            "categories" => array()
        );

        foreach ($result as $row) {
            if (!is_null($row["category_id"])) {
                $category = array(
                    "id" => $row["category_id"],
                    "name" => $row["category_name"],
                    "description" => $row["category_description"]
                );
                $post["categories"][] = $category;
            }
        }

        $db = null;
        $response->getBody()->write(json_encode($post));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



/**
 * Get a specific post from the database by their slug
 */
/*
$app->get('/posts/{slug}', function (Request $request, Response $response, array $args) {
    $slug = $args['slug'];

    // Check if the resource exists for posts
    if (!resourceExistsBySlug($slug)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this slug.",
            "resource-slug" => $slug
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    
    $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
                   c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.slug = :slug";

    try{
        $db = new Db();
        $conn = $db->connect();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $post = array(
            "id" => $result[0]["id"],
            "title" => $result[0]["title"],
            "slug" => $result[0]["slug"],
            "content" => $result[0]["content"],
            "thumbnail" => $result[0]["thumbnail"],
            "author" => $result[0]["author"],
            "posted_at" => $result[0]["posted_at"],
            "categories" => array()
        );

        foreach ($result as $row) {
            if (!is_null($row["category_id"])) {
                $category = array(
                    "id" => $row["category_id"],
                    "name" => $row["category_name"],
                    "description" => $row["category_description"]
                );
                $post["categories"][] = $category;
            }
        }

        $db = null;
        $response->getBody()->write(json_encode($post));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});
*/
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;


// Function to check if a resource with a given ID exists is available globally in index.php

/**
 * Get all our categories from the database
 */
$app->get('/categories', function (Request $request, Response $response) {

    $sql = "SELECT * FROM categories";

    try{

        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->query($sql);
        $friends = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $response->getBody()->write(json_encode($friends));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }catch (PDOException $e){
        $error = array("error-message" => $e->getMessage());
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



/**
 * Get all a specific category from the database
 */
$app->get('/categories/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    // Check if the resource exists for categories
    if (!resourceExistsCat($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "SELECT * 
            FROM categories 
            WHERE id = :id";

    try{
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db = null;
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }catch (PDOException $e){
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



/**
 * Create categories from the api to the database
 */
$app->post('/categories/create', function (Request $request, Response $response, array $args) {

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
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($data['description'], FILTER_SANITIZE_STRING);

    $sql = "INSERT INTO categories (name, description) 
            VALUES (:name, :description)";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);

        $isDataInserted = $stmt->execute();

        $db = null;

        // Prepare the response data
        $responseData = array(
            "success" => $isDataInserted,
            "message" => $isDataInserted ? "Data inserted successfully." : "Failed to insert data."
        );

        $response->getBody()->write(json_encode($responseData));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($isDataInserted ? 200 : 500);

    } catch (PDOException $e) {
        $error = array(
            "error-message" => "An error occurred while processing your request.",
            "details" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});



/**
 * Edit/Update a categories from the api to the database, using PUT
 */
$app->put('/categories/edit/{id}', function (Request $request, Response $response, array $args) {

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

    // Get the values from the decoded JSON data and sanitize
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($data['description'], FILTER_SANITIZE_STRING);

    // Check if the resource exists
    if (!resourceExistsCat($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "UPDATE categories 
            SET name = :name, 
                description = :description
            WHERE id = :id";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);

        $isDataUpdated = $stmt->execute();

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



/**
 * Edit/Update a categories from the api to the database, using PATCH
 */
$app->patch('/categories/edit/{id}', function (Request $request, Response $response, array $args) {
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

    // Check if the resource ID exists
    if (!resourceExistsCat($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "UPDATE categories SET ";
    $params = array();

    // Build the SET clause and parameter bindings for the update
    foreach ($data as $field => $value) {
        if ($field === 'name') {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        } elseif ($field === 'description') {
            $value = htmlspecialchars($value);
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

        // Bind parameter values dynamically, depending of the available fields to be edited
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        foreach ($params as $field => &$value) {
            $stmt->bindParam(":$field", $value);
        }

        $isDataUpdated = $stmt->execute();

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




// Delete a categories from the api to the database
$app->delete('/categories/delete/{id}', function (Request $request, Response $response, array $args) {

    $id = htmlspecialchars($args['id']);

    // Check if the resource exists
    if (!resourceExistsCat($id)) {
        $errorResponse = array(
            "error-message" => "Resource not found with this ID.",
            "resource-id" => $id
        );
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $sql = "DELETE FROM categories WHERE id= :id";

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

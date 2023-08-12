<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Firebase\JWT\JWT;

use Respect\Validation\Validator as v;
use App\Validation\Validator;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;



/**
 * Login and generate a JWT Token
 */
$app->post("/login", function (Request $request, Response $response) {

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

// $data = $request->getParsedBody();
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = filter_var($data['password'], FILTER_SANITIZE_STRING);


/************************************************************************************************ */

// Lets instantiate Validator and CustomResponse classes
$validator = new Validator();
$customResponse = new CustomResponse();

// It starts by validating the input data using the $validator.
$validator->validate($request,[
    "email"=>v::notEmpty()->email(),
    "password"=>v::notEmpty()
]);

// If validation fails, the method returns a 400 response with the validation errors using the $customResponse.
if($validator->failed())
{
    $responseMessage = $validator->errors;
    return $customResponse->is400Response($response,$responseMessage);
}

/*********************************************************************************************** */


// Retrieve user data from the database
$sql = "SELECT id, password FROM users WHERE email = :email";

try{

    $db = new Db();
    $conn = $db->connect();

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
    
        $responseData = array(
            "success" => false,
            "message" => "Your login credentials are invalid."
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Generate JWT token
    $secret_key = "SpslTAT3s09W9LjOgt9LQ7VTpSYsZoGD5Zcg0oK3x5U="; 
    $payload = [
        "email" => $email,
        "exp" => time() + 3600, // Token expiration time (1 hour)
    ];
    $token = JWT::encode($payload, $secret_key);

    // return $response->withJson(["token" => $token]);
    $db = null;

    // Prepare the response data
    $responseData = array(
        "success" => true,
        "message" => "You've logged in successfully.",
        "token" => $token
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
 * Create an account in order to generate a JWT Token
 */
$app->post("/register", function (Request $request, Response $response) {

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

// $data = $request->getParsedBody();
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = filter_var($data['password'], FILTER_SANITIZE_STRING);
$password = password_hash($password, PASSWORD_DEFAULT);


/******************************************************************************************************* */
// Lets instantiate the Validator and CustomResponse classes
$validator = new Validator();
$customResponse = new CustomResponse();

// It starts by validating the input data using the $validator.
$validator->validate($request,[
    "email"=>v::notEmpty()->email(),
    "password"=>v::notEmpty()
]);

// If validation fails, the method returns a 400 response with the validation errors using the $customResponse.
if($validator->failed())
{
    $responseMessage = $validator->errors;
    return $customResponse->is400Response($response,$responseMessage);
}

// Check if the provided email already exists in the database. 
if ($email){

    $db = new Db();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // prepare the response data
        $responseData = array(
            "success" => false,
            "message" => "Email already registered"
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);

        $db = null;
        die();
    } 
}
/************************************************************************************************** */


// Insert user data into the database
$sql = "INSERT INTO users (email, password) VALUES (:email, :password)";

try{

    $db = new Db();
    $conn = $db->connect();

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);

    $isUserRegistered = $stmt->execute();


    $db = null;
    // Prepare the response data
    $responseData = array(
        "success" => $isUserRegistered,
        "message" => $isUserRegistered ? "User registeration successful." : "Failed to register user.",
    );

    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($isUserRegistered ? 200 : 500);

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
<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Auth;
use App\Response\CustomResponse;
use App\Models\ResourceExists;

use Respect\Validation\Validator as v;
use App\Validation\Validator;


class AuthController
{

    protected $auth_user;
    protected $resource_exists;

    public function __construct(Auth $auth_user, ResourceExists $resource_exists)
    {
        $this->auth_user = $auth_user;
        $this->resource_exists = $resource_exists;
    }


    public function authLogin(Request $request, Response $response): Response
    {
        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            $errorResponse = array("error-message" => "Invalid JSON data");
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }

        // $data = $request->getParsedBody();
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = filter_var($data['password'], FILTER_SANITIZE_STRING);

        // Lets instantiate Validator and CustomResponse classes
        $validator = new Validator();
        $customResponse = new CustomResponse();

        // It starts by validating the input data using the $validator.
        $validator->validate($request,[
            "email"=>v::notEmpty()->email(),
            "password"=>v::notEmpty()
        ]);

        // If validation fails, the method returns a 400 error response .
        if($validator->failed())
        {
            $responseMessage = $validator->errors;
            return $customResponse->is400Response($response,$responseMessage);
        }

        // Call the model's Auth() method
        $isLoginValid = $this->auth_user->loginMethod($email, $password);

        // error check
        if (isset($isLoginValid['credentials_error'])) {

            $errorResponse = $isLoginValid['credentials_error']; // gets the error message
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        // error check
        if (isset($isLoginValid['error'])) {

            $errorResponse = array(
                "message" => "An error occurred while processing your request.",
                "details" => $isLoginValid['error'] // gets the error details
            );
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        if (isset($isLoginValid['success'])) {

            $successResponse = $isLoginValid['success']; // gets the success message
            $response->getBody()->write(json_encode($successResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

    }

    public function authRegister(Request $request, Response $response): Response
    {

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

        // If a specific email address has been used already, i.e. true, then do this.
        if ($this->resource_exists->resourceExistsUserEmail($email)) {
            $errorResponse = array(
                "success" => false,
                "message" => "Email already registered"
            );
            return CustomResponse::respondWithError($response, $errorResponse, 400);
            die();
        }

        // register user using the Auth model's method
        $isRegistered = $this->auth_user->registerMethod($email, $password);

        // Prepare the response data
        $responseData = array(
            "success" => $isRegistered,
            "message" => $isRegistered ? "User registeration successful." : "Failed to register user."
        );

        return CustomResponse::respondWithData($response, $responseData, $isRegistered ? 200 : 500);
    }

}
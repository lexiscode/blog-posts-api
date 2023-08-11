<?php

namespace  App\Response;


/**
 * This PHP code defines a custom response class named CustomResponse, which provides methods for 
 * formatting and returning different types of API responses with specific HTTP status codes. This class
 * is responsible for creating JSON responses with consistent structures for success and error cases.
 */
class CustomResponse
{

    // This method is used to create a response for client errors with an HTTP status code of 400 (Bad Request).
    public function is400Response($response,$responseMessage)
    {
        $responseMessage = json_encode(["success"=>false,"response"=>$responseMessage]);
        $response->getBody()->write($responseMessage);
        return $response->withHeader("Content-Type","application/json")->withStatus(400);
    }

}
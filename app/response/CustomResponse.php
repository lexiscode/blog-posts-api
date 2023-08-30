<?php

namespace  App\Response;

use Psr\Http\Message\ResponseInterface as Response;

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

    public static function respondWithError(Response $response, $data, $statusCode): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

    public static function respondWithData(Response $response, $data, $statusCode): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

}
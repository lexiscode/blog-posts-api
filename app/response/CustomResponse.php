<?php

namespace  App\Response;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * 
 * Notice the first method code style is different from the two methods after, first one is static while
 * the others are dynamic/flexible depending on error codes assigned.
 * 
 * All of these methods are made this way for personal future reference
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

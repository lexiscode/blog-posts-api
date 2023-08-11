<?php

namespace App\Requests;


/**
 * The purpose of this class is to provide a method for retrieving parameters from various parts of
 * an HTTP request, including query parameters (GET), form parameters (POST), and JSON request bodies.
 */
class CustomRequestHandler
{

    /**
     * @$request: This parameter represents the incoming HTTP request object.
     * @$key: This is the name of the parameter/key that you want to retrieve from the request.
     * @$default: This parameter is optional and represents the default value to return if the requested key is not found in the request.
     */
    public static function getParam($request,$key,$default=null)
    {
        // whenever we are dealing with post request, we use getParsedBody() method
        $postParams = $request->getParsedBody();
        
        // whenever we are dealing with get request, we use getQueryParams() method
        $getParams = $request->getQueryParams();

        $getBody = json_decode($request->getBody(),true);

        $result = $default;

        // First, it checks if the parameter exists in the parsed body (POST parameters).
        if(is_array($postParams) && isset($postParams[$key]))
        {
            $result = $postParams[$key];

        }
        // If not found in the parsed body, it checks if the parameter exists in the JSON request body (if it's a JSON request).
        else if(is_object($postParams) && property_exists($postParams, $key))
        {
            $result = $postParams->$key;
        }
        // If still not found, it checks if the parameter exists in the query parameters (GET parameters).
        else if(is_array($getBody) && isset($getBody[$key]))
        {
            $result = $getBody[$key];

        }
        // For query parameters (GET), the method directly retrieves the value from the $getParams array, which holds the query parameters.
        else if(isset($getParams[$key]))
        {
            $result = $getParams[$key];
        }

        return $result;
    }

}


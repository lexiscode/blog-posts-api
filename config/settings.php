<?php

return [

    'jwt' => [
        'secret' => 'SpslTAT3s09W9LjOgt9LQ7VTpSYsZoGD5Zcg0oK3x5U=',
        "attribute" => "jwt",
        'algorithm' => 'HS256',
        'secure' => false, // only for localhost for prod and test env set true
        'error' => function ($response, $arguments) {
            $data['status'] = 401;
            $data['error'] = 'Unauthorized/'. $arguments['message'];
            return $response
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->getBody()->write(json_encode(
                    $data,
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ));
        }
    ],
];
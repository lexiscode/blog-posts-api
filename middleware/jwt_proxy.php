<?php

use Tuupola\Middleware\JwtAuthentication;


// JWT Authentication Middleware
$app->add(new JwtAuthentication([
    "path" => ["/"],  // Exclude this middleware from the root URL
    "ignore" => ["/register", "/login"],  // Exclude these routes from JWT authentication
    "secret" => $settings['jwt']['secret'],
    "attribute" => $settings['jwt']['attribute'],
    "algorithm" => $settings['jwt']['algorithm'],
    "secure" => $settings['jwt']['secure'],
    "error" => $settings['jwt']['error']
]));


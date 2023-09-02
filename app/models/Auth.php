<?php

namespace App\Models;	

use PDO;
use PDOException;

// necessary imports for the logging functionality
use Psr\Container\ContainerInterface; 
use Laminas\Log\Logger;

use Firebase\JWT\JWT;

class Auth
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    public function loginMethod($email, $password)
    {
        // Retrieve user data from the database
        $sql = "SELECT id, password FROM users WHERE email = :email";

        try{
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
            
                $responseData = array(
                    "success" => false,
                    "message" => "Your login credentials are invalid."
                );

                return ['credentials_error' => $responseData];

            }

            // Generate JWT token
            $secret_key = "SpslTAT3s09W9LjOgt9LQ7VTpSYsZoGD5Zcg0oK3x5U="; 
            $payload = [
                "email" => $email,
                "exp" => time() + 3600, // Token expiration time (1 hour)
            ];
            $token = JWT::encode($payload, $secret_key);

            //$db = null;

            // Prepare the response data
            $responseData = array(
                "success" => true,
                "message" => "You've logged in successfully.",
                "token" => $token
            );

            return ['success' => $responseData];

        } catch (PDOException $e) {

            $this->db->rollback();
            return ['error' => $e->getMessage()];
        }
    }


    public function registerMethod($email, $password)
    {

        $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";

        try{

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password, PDO::PARAM_STR);

            $isUserRegistered = $stmt->execute();

            //$db = null;
            return $isUserRegistered;

        } catch (PDOException $e) {
            
            $this->db->rollback();
            return ['error' => $e->getMessage()];
        }
    }

}
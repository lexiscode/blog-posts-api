<?php

namespace App\Models\Database;

use PDO;
use PDOException;


/**
 * DbConnect
 * A connection to the database 
 */
class DbConnect
{
    /**
     * Get the database connection
     * 
     * @return PDO object Connection to the database server
     */
    public function getConn()
    {
        $db_host = $_ENV['DB_HOST'];
        $db_name = $_ENV['DB_NAME'];
        $db_user = $_ENV['DB_USER'];
        $db_passwd = $_ENV['DB_PASSWORD'];

        $dsn = 'mysql:host='.$db_host. ';dbname='.$db_name.';charset=utf8';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $conn = new PDO($dsn, $db_user, $db_passwd, $options);
            return $conn;
        } catch (PDOException $e) {
            // Handle the exception here, log or throw as needed
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }
}

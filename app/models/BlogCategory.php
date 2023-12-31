<?php

namespace App\Models;	

use PDO;
use PDOException;

// necessary imports for the logging functionality
use Psr\Container\ContainerInterface; 
use Laminas\Log\Logger;

class BlogCategory
{
    protected $db;

    protected $container;
    protected $logger;

    public function __construct(PDO $db, ContainerInterface $container)
    {
        $this->db = $db;

        $this->container = $container;
        $this->logger = $container->get(Logger::class);
    }

    /**
     * Gets all categories from the database
     */
    public function getAll()
    {
        $sql = "SELECT * FROM categories";

        try{
            $stmt = $this->db->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_OBJ);

            // $this->db = null;
            return $categories;

        } catch (PDOException $e) {
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }

        
    }


    /**
     * Gets specific post by id from the database
     */
    public function getById($id)
    {
        $sql = "SELECT * 
                FROM categories 
                WHERE id = :id";

        try{
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //$db = null;
            if (empty($result)) {
                // Handle the case of no matching post
                $errorResponse = array(
                    "status" => 404,
                    "message" => "Resource not found with this ID.",
                    "resource-id" => $id
                );
                return $errorResponse; // Return the error response directly
            }

            return $result;
       
        }catch (PDOException $e){
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * Create a new category to the database
     */
    public function addData($name, $description)
    {
        $sql = "INSERT INTO categories (name, description) 
                VALUES (:name, :description)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);

            $isDataInserted = $stmt->execute();

            //$db = null;
            if (empty($isDataInserted)) {
                // Handle the case of no matching post
                $errorResponse = array(
                    "success" => $isDataInserted,
                    "message" => 'Failed to insert new category.'
                );
                return $errorResponse; // Return the error response directly
            }

            return $isDataInserted;

        } catch (PDOException $e) {
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }



    public function patchData($id, $data)
    {
        $sql = "UPDATE categories SET ";
        $params = array();

        // Build the SET clause and parameter bindings for the update
        foreach ($data as $field => $value) {
            if ($field === 'name') {
                $value = htmlspecialchars($value);
            } elseif ($field === 'description') {
                $value = htmlspecialchars($value);
            } 

            $sql .= "$field = :$field, ";
            $params[$field] = $value;
        }

        // Remove the trailing comma and space
        $sql = rtrim($sql, ", ");

        // Add the WHERE condition
        $sql .= " WHERE id = :id";

        try {
    
            $stmt = $this->db->prepare($sql);

            // Bind parameter values dynamically, depending of the available fields to be edited
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            foreach ($params as $field => &$value) {
                $stmt->bindParam(":$field", $value);
            }

            $isDataUpdated = $stmt->execute();

            if (empty($isDataUpdated)) {
                // Handle the case of no matching post
                $errorResponse = array(
                    "success" => $isDataUpdated,
                    "message" => 'Failed to update category.'
                );
                return $errorResponse; 
            }

            return $isDataUpdated;
       
        } catch (PDOException $e) {
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    public function putData($id, $name, $description)
    {
        $sql = "UPDATE categories 
            SET name = :name, 
                description = :description
            WHERE id = :id";

        try {

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);

            $isDataUpdated = $stmt->execute();

            return $isDataUpdated;

        } catch (PDOException $e) {
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    public function deleteData($id)
    {
        $sql = "DELETE FROM categories WHERE id= :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $isDataDeleted = $stmt->execute();

            return $isDataDeleted;
                
        } catch (PDOException $e) {
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

}



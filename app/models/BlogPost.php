<?php

namespace App\Models;

use PDO;
use PDOException;

// necessary imports for the logging functionality
use Psr\Container\ContainerInterface; 
use Laminas\Log\Logger;


class BlogPost
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
     * Gets all posts from the database
     */
    public function getAll()
    {
        $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
                   c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc 
            ON p.id = pc.post_id
            LEFT JOIN categories c 
            ON pc.category_id = c.id";

        try {
            $stmt = $this->db->query($sql); 
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $posts = [];
            foreach ($rows as $row) {
                $postId = $row['id'];
                if (!isset($posts[$postId])) {
                    $post = [
                        'id' => $postId,
                        'title' => $row['title'],
                        'slug' => $row['slug'],
                        'content' => $row['content'],
                        'thumbnail' => $row['thumbnail'],
                        'author' => $row['author'],
                        'posted_at' => $row['posted_at'],
                        'categories' => [],
                    ];
                    $posts[$postId] = $post;
                }
                if (!is_null($row['category_id'])) {
                    $category = [
                        'id' => $row['category_id'],
                        'name' => $row['category_name'],
                        'description' => $row['category_description'],
                    ];
                    $posts[$postId]['categories'][] = $category;
                }
            }

            return array_values($posts);
        } catch (PDOException $e) {
            // Handle the exception here, log or throw as needed
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * Gets specific post by id from the database
     */
    public function getById($id)
    {
        $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
               c.id AS category_id, c.name AS category_name, c.description AS category_description
            FROM posts p
            LEFT JOIN posts_categories pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.id = :id";

        try {
          
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          

            if (!empty($result)) {

                $post = array(
                    "id" => $result[0]["id"],
                    "title" => $result[0]["title"],
                    "slug" => $result[0]["slug"],
                    "content" => $result[0]["content"],
                    "thumbnail" => $result[0]["thumbnail"],
                    "author" => $result[0]["author"],
                    "posted_at" => $result[0]["posted_at"],
                    "categories" => array()
                );
        
                foreach ($result as $row) {
                    if (!is_null($row["category_id"])) {
                        $category = array(
                            "id" => $row["category_id"],
                            "name" => $row["category_name"],
                            "description" => $row["category_description"]
                        );
                        $post["categories"][] = $category;
                    }
                }
          
            } else {
                // Handle the case of no matching post
                $errorResponse = array(
                    "status" => 404,
                    "message" => "Resource not found with this ID.",
                    "resource-id" => $id
                );
                return $errorResponse; // Return the error response directly
            }

            return $post;

        } catch (PDOException $e) {
            // Handle the exception here, log or throw as needed
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * Search or Get a post by slug 
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT p.id, p.title, p.slug, p.content, p.thumbnail, p.author, p.posted_at,
                    c.id AS category_id, c.name AS category_name, c.description AS category_description
                FROM posts p
                LEFT JOIN posts_categories pc ON p.id = pc.post_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.slug = :slug";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                // Handle the case of no matching post
                $errorResponse = array(
                    "status" => 404,
                    "message" => "Resource not found with this slug.",
                    "resource-id" => $slug
                );
                return $errorResponse; // Return the error response directly
            }

            $post = array(
                "id" => $result[0]["id"],
                "title" => $result[0]["title"],
                "slug" => $result[0]["slug"],
                "content" => $result[0]["content"],
                "thumbnail" => $result[0]["thumbnail"],
                "author" => $result[0]["author"],
                "posted_at" => $result[0]["posted_at"],
                "categories" => array()
            );

            foreach ($result as $row) {
                if (!is_null($row["category_id"])) {
                    $category = array(
                        "id" => $row["category_id"],
                        "name" => $row["category_name"],
                        "description" => $row["category_description"]
                    );
                    $post["categories"][] = $category;
                }
            }

            return $post;

        } catch (PDOException $e) {
            // Handle the exception here, log or throw as needed
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    

    /**
     * Create a new post to the database
     */
    public function addData($title, $slug, $content, $thumbnailUrl, $author, $categories)
    {
        $sqlInsertPost = "INSERT INTO posts (title, slug, content, thumbnail, author) 
                          VALUES (:title, :slug, :content, :thumbnail, :author)";
        
        $sqlInsertCategoryRelation = "INSERT INTO posts_categories (post_id, category_id) 
                                      VALUES (:post_id, :category_id)";
        
        try {
           
            $this->db->beginTransaction();

            // Insert post information
            $stmtInsertPost = $this->db->prepare($sqlInsertPost);
            $stmtInsertPost->bindParam(':title', $title, PDO::PARAM_STR);
            $stmtInsertPost->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmtInsertPost->bindParam(':content', $content, PDO::PARAM_STR);
            $stmtInsertPost->bindParam(':thumbnail', $thumbnailUrl, PDO::PARAM_STR);
            $stmtInsertPost->bindParam(':author', $author, PDO::PARAM_STR);
            $isPostInserted = $stmtInsertPost->execute();

            if (!$isPostInserted) {
                $this->db->rollback();
                
            }

            // Fetch the last inserted post ID
            $postId = $this->db->lastInsertId();

            // Insert category relationships
            $stmtInsertCategoryRelation = $this->db->prepare($sqlInsertCategoryRelation);
            foreach ($categories as $categoryId) {
                $stmtInsertCategoryRelation->bindValue(':post_id', $postId, PDO::PARAM_INT);
                $stmtInsertCategoryRelation->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
                $stmtInsertCategoryRelation->execute();
            }

            $this->db->commit();

            return true; // Return true if the data was added successfully

        } catch (PDOException $e) {
            $this->db->rollback();

            // Handle the exception here, log or throw as needed
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }



    public function updateData($id, $data)
    {
        // Define the SQL update template
        $sql = "UPDATE posts SET ";
        $params = array();

        // Build the SET clause and parameter bindings for the update
        foreach ($data as $field => $value) {
            if ($field === 'title') {
                $value = htmlspecialchars($value);
            } elseif ($field === 'slug') {
                $value = htmlspecialchars($value);
            } elseif ($field === 'content') {
                $value = htmlspecialchars($value);
            } elseif ($field === 'thumbnail') {
                // ... handle thumbnail update logic ...
            } elseif ($field === 'author') {
                $value = htmlspecialchars($value);
            } elseif ($field === 'categories') {
                // Skip categories field for now
                continue;
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

            // Bind parameter values dynamically, depending on the available fields to be edited
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            foreach ($params as $field => &$value) {
                $stmt->bindParam(":$field", $value);
            }

            // Execute the update query and return whether the update was successful
            return $stmt->execute();
        } catch (PDOException $e) {
            // Return false to indicate failure
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return false;
        }
    }


    public function deleteData($id)
    {
        $sql = "DELETE FROM posts WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $isDataDeleted = $stmt->execute();

            return $isDataDeleted;
        } catch (PDOException $e) {
            // Handle the exception here, log or throw as needed
            $this->logger->error('Error retrieving all posts: ' . $e->getMessage());
            return false;
        }
    }

}



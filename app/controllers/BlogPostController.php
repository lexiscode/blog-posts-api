<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BlogPost;
use App\Response\CustomResponse;
use App\Models\ResourceExists;


class BlogPostController
{

    protected $blog_post;
    protected $resource_exists;


    public function __construct(BlogPost $blog_post, ResourceExists $resource_exists)
    {
        $this->blog_post = $blog_post;
        $this->resource_exists = $resource_exists;
    }

    /**
     * Get all blog posts
     */
    public function getAllPosts(Request $request, Response $response): Response
    {

        $all_data = $this->blog_post->getAll();

        if (isset($all_data['error'])) {
            $error = ['error' => $all_data['error']];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode($all_data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    /**
     * Get a blog post by its id
     */
    public function getPostById(Request $request, Response $response): Response
    {
        // Get the id from the URL parameters
        // This format is used if we choose not to include "array $args" as part of our argument above
        $id = $request->getAttribute('id');
        // $id = htmlspecialchars($args['id']);

        $single_data = $this->blog_post->getById($id); 

        if (isset($single_data['error'])) {
            $error = ['error' => $single_data['error']];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode($single_data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    /**
     * Search or Get a post by slug 
     */
    public function getBySlug(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];

        // Check if the resource exists for posts using the model method
        $post = $this->blog_post->getBySlug($slug);

        if (isset($post['error'])) {
            $errorResponse = array(
                "error-message" => "An error occurred while processing your request.",
                "details" => $post['error']
            );
            return CustomResponse::respondWithError($response, $errorResponse, 500);
        }

        if (empty($post)) {
            $errorResponse = array(
                "error-message" => "Resource not found with this slug.",
                "resource-slug" => $slug
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        $response->getBody()->write(json_encode($post));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    /**
     * Create a new blog post
     */
    public function createPost(Request $request, Response $response): Response
    {
        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            // Invalid JSON data
            $errorResponse = array("error-message" => "Invalid JSON data");
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Get the values from the decoded JSON data and sanitize
        $title = filter_var($data['title'], FILTER_SANITIZE_STRING);
        $slug = htmlspecialchars($data['slug']);
        $content = filter_var($data['content'], FILTER_SANITIZE_STRING);
        $thumbnailBase64 = htmlspecialchars($data['thumbnail']);
        $author = filter_var($data['author'], FILTER_SANITIZE_STRING);
        // Extract categories from JSON data and sanitize too
        $categories = $data['categories'];

        // Decode Base64 image data
        $thumbnailData = base64_decode($thumbnailBase64);
        // Generate a unique filename
        $thumbnailFilename = uniqid() . '.png'; // You can change the file extension
        // Define the path to store the images
        $thumbnailPath = 'thumbnails/' . $thumbnailFilename;

        // Ensure the directory exists, create it if not
        $thumbnailDirectory = dirname($thumbnailPath);
        if (!is_dir($thumbnailDirectory)) {
            mkdir($thumbnailDirectory, 0755, true);
        }

        // Save the decoded image data to the specified path
        file_put_contents($thumbnailPath, $thumbnailData);
        // Prepare the thumbnail URL
        $thumbnailUrl = 'http://localhost:200/' . $thumbnailPath;

        // Call the model's addData() method to create the post
        $isPostAdded = $this->blog_post->addData(
            $title, $slug, $content, $thumbnailUrl, $author, $categories
        );

        if ($isPostAdded) {
            // Prepare the response data
            $responseData = array(
                "success" => true,
                "message" => "Post and categories inserted successfully.",
                "thumbnail" => $thumbnailUrl
            );

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $errorResponse = array(
                "error-message" => "An error occurred while processing your request."
            );
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
        
    }


    /**
     * Update a specific blog post by its ID
     */
    public function updatePost(Request $request, Response $response, array $args): Response
    {
        // Get the id from the URL parameters
        // This format is used if we choose to include "array $args" as part of our argument above
        $id = htmlspecialchars($args['id']);
        // $id = $request->getAttribute('id');

        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            $errorResponse = array("error-message" => "Invalid JSON data");
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }
       
        // Check if the resource ID exists for posts
        if (!$this->resource_exists->resourceExistsPost($id)) {
            $errorResponse = array(
                "error-message" => "Resource not found with this ID.",
                "resource-id" => $id
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        // Update the post data using the model method
        $isDataUpdated = $this->blog_post->updateData($id, $data);

        // Prepare the response data
        $responseData = array(
            "success" => $isDataUpdated,
            "message" => $isDataUpdated ? "Data updated successfully." : "Failed to update data."
        );

        return CustomResponse::respondWithData($response, $responseData, $isDataUpdated ? 200 : 500);
    }


    /**
     * Update a specific blog post by its ID
     */
    public function deletePost(Request $request, Response $response, array $args): Response
    {
        $id = htmlspecialchars($args['id']);

        // Check if the resource exists for posts using the model method
        if (!$this->resource_exists->resourceExistsPost($id)) {
            $errorResponse = array(
                "error-message" => "Resource not found with this ID.",
                "resource-id" => $id
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        // Delete the post using the model method
        $isDataDeleted = $this->blog_post->deleteData($id);

        // Prepare the response data
        $responseData = array(
            "success" => $isDataDeleted,
            "message" => $isDataDeleted ? "Data deleted successfully." : "Failed to delete data."
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($isDataDeleted ? 200 : 500);
    }

}



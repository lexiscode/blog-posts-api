<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BlogCategory;
use App\Response\CustomResponse;
use App\Models\ResourceExists;


class BlogCategoryController
{

    protected $blog_category;
    protected $resource_exists;

    public function __construct(BlogCategory $blog_category, ResourceExists $resource_exists)
    {
        $this->blog_category = $blog_category;
        $this->resource_exists = $resource_exists;
    }

    /**
     * Get all blog posts
     */
    public function getAllCategories(Request $request, Response $response): Response
    {
        $all_data = $this->blog_category->getAll();

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
    public function getCategoryById(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        $single_data = $this->blog_category->getById($id); 

        if (isset($single_data['error'])) {
            $error = ['error' => $single_data['error']];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode($single_data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    }


    /**
     * Create a new blog category
     */
    public function createCategory(Request $request, Response $response): Response
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
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $description = filter_var($data['description'], FILTER_SANITIZE_STRING);

        // Call the model's addData() method to create the post
        $isCategoryAdded = $this->blog_category->addData($name, $description);

        if (isset($isCategoryAdded['error'])) {

            $errorResponse = array(
                "error-message" => "An error occurred while processing your request.",
                "details" => $isCategoryAdded['error']
            );
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
      
        // Prepare the response data
        $responseData = array(
            "success" => true,
            "message" => "New category inserted successfully.",
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } 
    


    /**
     * Update all or a part of a specific blog post by its ID
     */
    public function patchCategory(Request $request, Response $response, array $args): Response
    {
        // Get the id from the URL parameters
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
        if (!$this->resource_exists->resourceExistsCat($id)) {
            $errorResponse = array(
                "error-message" => "Resource not found with this ID.",
                "resource-id" => $id
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        // Update the post data using the model method
        $isDataUpdated = $this->blog_category->patchData($id, $data);

        // Prepare the response data
        $responseData = array(
            "success" => $isDataUpdated,
            "message" => $isDataUpdated ? "Data updated successfully." : "Failed to update data."
        );

        return CustomResponse::respondWithData($response, $responseData, $isDataUpdated ? 200 : 500);
    }


    /**
     * Update all data of a specific blog post by its ID
     */
    public function putCategory(Request $request, Response $response, array $args): Response
    {
        // Get the id from the URL parameters
        //$id = htmlspecialchars($args['id']);
        $id = $request->getAttribute('id');

        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            $errorResponse = array("error-message" => "Invalid JSON data");
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }

        // Get the values from the decoded JSON data and sanitize
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $description = filter_var($data['description'], FILTER_SANITIZE_STRING);
       
        // Check if the resource ID exists for posts
        if (!$this->resource_exists->resourceExistsCat($id)) {
            $errorResponse = array(
                "error-message" => "Resource not found with this ID.",
                "resource-id" => $id
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        // Update the post data using the model method
        $isDataUpdated = $this->blog_category->putData($id, $name, $description);

        // Prepare the response data
        $responseData = array(
            "success" => $isDataUpdated,
            "message" => $isDataUpdated ? "Data updated successfully." : "Failed to update category."
        );

        return CustomResponse::respondWithData($response, $responseData, $isDataUpdated ? 200 : 500);
    }


    /**
     * Delete a specific blog post by its ID
     */
    public function deleteCategory(Request $request, Response $response, array $args): Response
    {
        $id = htmlspecialchars($args['id']);

        // Check if the resource exists for posts using the model method
        if (!$this->resource_exists->resourceExistsCat($id)) {
            $errorResponse = array(
                "error-message" => "Resource not found with this ID.",
                "resource-id" => $id
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        // Delete the post using the model method
        $isDataDeleted = $this->blog_category->deleteData($id);

        // Prepare the response data
        $responseData = array(
            "success" => $isDataDeleted,
            "message" => $isDataDeleted ? "Data deleted successfully." : "Failed to delete data."
        );

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($isDataDeleted ? 200 : 500);
    }

}

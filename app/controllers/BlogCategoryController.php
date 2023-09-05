<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BlogCategory;
use App\Response\CustomResponse;
use App\Models\ResourceExists;

use Respect\Validation\Validator as v;
use App\Validation\Validator;

use OpenApi\Annotations as OA;

// necessary imports for the logging functionality
use Psr\Container\ContainerInterface; 
use Laminas\Log\Logger;


class BlogCategoryController
{

    protected $blog_category;
    protected $resource_exists;

    protected $container;
    protected $logger;

    public function __construct(BlogCategory $blog_category, ResourceExists $resource_exists, ContainerInterface $container)
    {
        $this->blog_category = $blog_category;
        $this->resource_exists = $resource_exists;

        $this->container = $container;
        $this->logger = $container->get(Logger::class);
    }

    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Get all blog categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing all blog categories",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
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
     * @OA\Get(
     *     path="/categories/{id}",
     *     summary="Get a blog category by its ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the blog category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing the blog category",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function getCategoryById(Request $request, Response $response, array $args): Response
    {
        $id = htmlspecialchars($args['id']);

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
     * @OA\Post(
     *     path="/categories",
     *     summary="Create a new blog category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Category Name"),
     *                 @OA\Property(property="description", type="string", example="Category Description"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with category creation status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
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

            $this->logger->info('Status 400: Invalid JSON data (Bad request).');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Get the values from the decoded JSON data and sanitize
        $name = htmlspecialchars($data['name']);
        $description = htmlspecialchars($data['description']);

        // Lets instantiate Validator and CustomResponse classes
        $validator = new Validator();
        $customResponse = new CustomResponse();

        // It starts by validating the input data using the $validator.
        $validator->validate($request,[
            "name"=>v::notEmpty(),
            "description"=>v::notEmpty()
        ]);

        // If validation fails, the method returns a 400 error response .
        if($validator->failed())
        {
            $responseMessage = $validator->errors;

            $this->logger->info('Status 400: Failed validation (Bad request).');
            return $customResponse->is400Response($response,$responseMessage);
        }

        // Call the model's addData() method to create the post
        $isCategoryAdded = $this->blog_category->addData($name, $description);

        if (isset($isCategoryAdded['error'])) {

            $errorResponse = array(
                "status" => 500,
                "message" => "An internal server error occurred while processing your request.",
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
     * @OA\Patch(
     *     path="/categories/edit/{id}",
     *     summary="Update all or a part of a specific blog category by its ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Category Name"),
     *                 @OA\Property(property="description", type="string", example="Updated Category Description"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with category update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
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

            $this->logger->info('Status 400: Invalid JSON data (Bad request).');
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }
       
        // Check if the resource ID exists for posts
        if (!$this->resource_exists->resourceExistsCat($id)) {
            $errorResponse = array(
                "status" => 404,
                "message" => "Resource not found with this ID.",
                "resource-id" => $id
            );

            $this->logger->info('Status 404: Resource not found with this ID.');
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
     * @OA\Put(
     *     path="/categories/edit/{id}",
     *     summary="Update all data of a specific blog category by its ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Category Name"),
     *                 @OA\Property(property="description", type="string", example="Updated Category Description"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with category update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function putCategory(Request $request, Response $response, array $args): Response
    {
        // Get the id from the URL parameters
        $id = htmlspecialchars($args['id']);

        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            $errorResponse = array("error-message" => "Invalid JSON data");
            $this->logger->info('Status 400: Invalid JSON data (Bad request).');
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }

        // Get the values from the decoded JSON data and sanitize
        $name = htmlspecialchars($data['name']);
        $description = htmlspecialchars($data['description']);

        // Lets instantiate Validator and CustomResponse classes
        $validator = new Validator();
        $customResponse = new CustomResponse();

        // It starts by validating the input data using the $validator.
        $validator->validate($request,[
            "name"=>v::notEmpty(),
            "description"=>v::notEmpty()
        ]);

        // If validation fails, the method returns a 400 error response .
        if($validator->failed())
        {
            $responseMessage = $validator->errors;
            $this->logger->info('Status 400: Failed validation (Bad request).');
            return $customResponse->is400Response($response,$responseMessage);
        }
       
        // Check if the resource ID exists for posts
        if (!$this->resource_exists->resourceExistsCat($id)) {
            $errorResponse = array(
                "status" => 404,
                "message" => "Resource not found with this ID.",
                "resource-id" => $id
            );

            $this->logger->info('Status 404: Resource not found with this ID.');
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
     * @OA\Delete(
     *     path="/categories/{id}",
     *     summary="Delete a specific blog category by its ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the blog category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with delete status",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function deleteCategory(Request $request, Response $response, array $args): Response
    {
        $id = htmlspecialchars($args['id']);

        // Check if the resource exists for posts using the model method
        if (!$this->resource_exists->resourceExistsCat($id)) {
            $errorResponse = array(
                "status" => 404,
                "message" => "Resource not found with this ID.",
                "resource-id" => $id
            );

            $this->logger->info('Status 404: Resource not found with this ID.');
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


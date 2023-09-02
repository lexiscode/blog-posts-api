<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BlogPost;
use App\Response\CustomResponse;
use App\Models\ResourceExists;

use Respect\Validation\Validator as v;
use App\Validation\Validator;

use OpenApi\Annotations as OA;

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
     * @OA\Get(
     *     path="/posts",
     *     summary="Get all blog posts",
     *     tags={"Posts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing all blog posts",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
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
     * @OA\Get(
     *     path="/posts/{id}",
     *     summary="Get a blog post by its ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the blog post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing the blog post",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function getPostById(Request $request, Response $response): Response
    {
        // Get the id from the URL parameters
        // This format is used if we choose not to include "array $args" as part of our argument above
        $id = htmlspecialchars($request->getAttribute('id'));
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
     * @OA\Get(
     *     path="/posts/slug/{slug}",
     *     summary="Search or get a blog post by slug",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of the blog post",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing the blog post",
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
    public function getBySlug(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];

        // Check if the resource exists for posts using the model method
        $post = $this->blog_post->getBySlug($slug);

        if (isset($post['error'])) {
            $errorResponse = array(
                "status" => 500,
                "message" => "An internal server error occurred while processing your request.",
                "details" => $post['error']
            );
            return CustomResponse::respondWithError($response, $errorResponse, 500);
        }

        if (empty($post)) {
            $errorResponse = array(
                "status" => 404,
                "message" => "Resource not found with this slug.",
                "resource-slug" => $slug
            );
            return CustomResponse::respondWithError($response, $errorResponse, 404);
        }

        $response->getBody()->write(json_encode($post));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    /**
     * @OA\Post(
     *     path="/posts",
     *     summary="Create a new blog post",
     *     tags={"Posts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="title", type="string", example="New Blog Post"),
     *                 @OA\Property(property="slug", type="string", example="new-blog-post"),
     *                 @OA\Property(property="content", type="string", example="Content of the blog post..."),
     *                 @OA\Property(property="thumbnail", type="string", format="base64", example="Base64-encoded image data..."),
     *                 @OA\Property(property="author", type="string", example="Author Name"),
     *                 @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example="[1, 2]"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with post creation status",
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
        $title = htmlspecialchars($data['title']);
        $slug = htmlspecialchars($data['slug']);
        $content = htmlspecialchars($data['content']);
        $thumbnailBase64 = htmlspecialchars($data['thumbnail']);
        $author = htmlspecialchars($data['author']);
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


        // Lets instantiate Validator and CustomResponse classes
        $validator = new Validator();
        $customResponse = new CustomResponse();

        // It starts by validating the input data using the $validator.
        $validator->validate($request,[
            "title"=>v::notEmpty(),
            "slug"=>v::notEmpty(),
            "content"=>v::notEmpty(),
            "author"=>v::notEmpty(),
            "categories"=>v::notEmpty()
        ]);

        // If validation fails, the method returns a 400 error response .
        if($validator->failed())
        {
            $responseMessage = $validator->errors;
            return $customResponse->is400Response($response,$responseMessage);
        }


        // Call the model's addData() method to create the post
        $isPostAdded = $this->blog_post->addData($title, $slug, $content, $thumbnailUrl, $author, $categories);

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
     * @OA\Put(
     *     path="/posts/{id}",
     *     summary="Update a specific blog post by its ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="title", type="string", example="Updated Blog Post"),
     *                 @OA\Property(property="slug", type="string", example="updated-blog-post"),
     *                 @OA\Property(property="content", type="string", example="Updated content of the blog post..."),
     *                 @OA\Property(property="thumbnail", type="string", format="base64", example="Updated Base64-encoded image data..."),
     *                 @OA\Property(property="author", type="string", example="Updated Author Name"),
     *                 @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example="[1, 2]"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with post update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
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
                "status" => 404,
                "message" => "Resource not found with this ID.",
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
     * @OA\Delete(
     *     path="/posts/{id}",
     *     summary="Delete a specific blog post by its ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the blog post",
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
    public function deletePost(Request $request, Response $response, array $args): Response
    {
        $id = htmlspecialchars($args['id']);

        // Check if the resource exists for posts using the model method
        if (!$this->resource_exists->resourceExistsPost($id)) {
            $errorResponse = array(
                "status" => 404,
                "message" => "Resource not found with this ID.",
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



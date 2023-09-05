# Blog Posts API

## Overview
The Blog Posts API is a robust RESTful API designed to handle various requests associated with managing blog posts and categories. It allows users to create, read, update, and delete blog posts, as well as manage post categories. This API has been developed as the backend component of a blogging platform. The front-end will be built separately, and this API serves as the bridge between the two, providing the necessary endpoints and functionality.

### API Implementation
As part of the project requirements, the following functionalities have been implemented:

- **Post Management**: Users can effortlessly create, read, update, and delete posts using dedicated API routes.
- **Thumbnail Integration**: The API allows users to include thumbnail images when creating new posts, enhancing the visual appeal of each post.
- **Category Control**: Users can manage post categories effectively with routes for creating, reading, updating, and deleting categories.
- **Slug-based Retrieval**: The API also supports retrieving posts by their unique slugs, ensuring accurate content delivery.
- **Category Association**: Multiple categories can be added to a single post, catering to the scenario where one post may belong to multiple categories.
- **JWT Authentication**: Users are required to register and log in to generate JWT tokens, ensuring secure access to protected routes.
- **Registration and Login Validations**: Input data for registration and login is validated to ensure data integrity and security.
- **API Documentation**: Comprehensive API documentation has been generated using Swagger, providing insights into available endpoints and their usage.

This backend API has been meticulously designed to fulfill these requirements and serve as a reliable foundation for the blog's frontend development. By adhering to REST principles, the API ensures seamless communication and data exchange between the frontend and backend components.

**Note:** The frontend development is not covered in this repository. The routes and functionalities developed here will be integrated into the frontend to deliver a complete and interactive blogging experience.

## Table of Contents
- **Getting Started**
  - Prerequisites
  - Installation
- **Usage**
  - Creating a Post
  - Reading Posts
  - Updating a Post
  - Deleting a Post
  - Managing Categories
- **Thumbnail Handling**
- **API Documentation**
- **Contributing**

## Getting Started

### Prerequisites
- PHP (>= 7.4)
- Composer (for dependency management)
- MySQL or compatible database

### Installation

1. Make sure you have PHP installed on your system.
2. Start the phpMyAdmin Apache and MySQL server 
3. Clone the repository to your local machine, inside your xampp/htdocs directory:
   ```
   git clone https://github.com/lexiscode/blog-posts-api.git
   cd blog-posts-api
   ```
4. Open the repository with your VS Code IDE (or any other IDEs).
5. Open a browser and go to URL http://localhost/phpmyadmin/
6. Then inside your phpMyAdmin, click on the "Databases" tab.
7. Create a database naming “restful-api-slim” and then click on the "Import" tab.
8. Click on "browse file" and select the “restful-api-slim.sql” file which is inside this project repository, specifically inside the "db" directory.
9. Click on "Go". 
10. Return back to your IDE and inside your terminal install dependencies by running this command:
   ```
   composer install
   ```

## Usage

### Starting the Local Server

To start the local server, navigate to the `public` directory and run the following command:
```
cd public
php -S localhost:200
```
NB: Please use this exact default recommended local server URL provided above, for simplicity sake of this my README content.

### API Testing with Postman

The API can be tested using Postman. But note that you will need to first create a JWT authentication token (by creating an account) in order to gain access to the blog resources. Once you've registered, then logged in, and a JWT Bearer Token will be given to you. 

Use the following endpoint to create a user account and also login in order to generate an authorization "Bearer Token":
```
POST /register
POST /login
```

Sample JSON request body for both the registeration and login, only email and password fields:
```json
{
  "email": "email@example.com",
  "password": "password",
}

```

You logged in? If yes! Go to the Authentication tab, and select type "Bearer Token", then copy and paste the bearer token given to you (from inside the response output) to the input field you will see at the right side (still within the Authentication tab of Postman). You have a limit of 2 hours maximum, to gain access to the blog resources via this token.

NB: You have to remain in this one particular tab to test all requests, the token grants access to only a single Postman tab, so don't open multiple tabs.

### API Testing with Swagger

The API can be tested using Swagger UI also. In your browser, by default you can visit this url which I believe is still your current running local server and exact port number: http://localhost:200/docs/

NB: For now to gain access to the blog resource and to bypass authentication in Swagger, first go inside my public/index.php file and "comment" code line 69 (i.e. where I wrote this: require __DIR__ . '/../middleware/jwt_proxy.php';). In this note, for now, don't bother testing the Authentication section (i.e. the register and login sections) in the Swagger UI, focus on the Posts and Categories sections.


### Creating a Base64 image file

I've made for you a base64 image content located in this repository, named "base64Sample.txt". But if you would like to create your own base64 image file content yourself, follow these steps below:

```
<?php

$imagePath = '/path/to/image.jpg';
$imageData = file_get_contents($imagePath);
$base64EncodedImage = base64_encode($imageData);

echo $base64EncodedImage;
```
NB: Use the above code snippet and do this in a new separate php index file (preferrably creating a separate PHP repository for this). Run a local server also so it can echo (or print) out from within your browser, then double/triple tap on it so you can copy the long encoded texts properly. (Pls do not copy it encoded texts via your terminal, it would break the texts)

### Creating a Post
Use the following endpoint to create a new blog post:
```
POST /posts/create
```

Sample JSON request body:
```json
{
  "title": "Sample Title",
  "slug": "sample-title",
  "content": "Sample content.",
  "thumbnail": "base64-long-encoded-image-data",
  "author": "John Doe",
  "categories": [1, 2]
}

```

### Reading Posts
- Retrieve all posts:
  ```
  GET /posts
  ```

- Retrieve a post by ID:
  ```
  GET /posts/{id}
  ```

Sample JSON pretty-read output:

```json
{
  "id": "1",
  "title": "All the Easter Eggs in PHP",
  "slug": "php-easter-eggs",
  "content": "PHP used to pack quite a few Easter Eggs back in the day. Until PHP 5.5, calling a URL with a special string returned various bits of PHP information and images such as the PHP logo, credits, Zend Engine logo, and a quirky PHP Easter Egg logo.",
  "thumbnail": "http://localhost:200/thumbnails/855a0350.png",
  "author": "A PHP Developer",
  "posted_at": "2023-02-01 17:30:01",
  "categories": [
    {
      "id": "1",
      "name": "php"
    },
    {
      "id": "2",
      "name": "curiosity"
    }
  ]
}

NB: You can view your newly created thumbnail image from within public/thumbnails directory. Or you can as well view the image by running this in your browser http://localhost:200/thumbnails/image-name.png

```

- Retrieve a post by slug:
  ```
  GET /posts/slug/{slug}
  ```

### Create new Post
Use the following endpoint to update a blog post:
```
POST /posts
```

### Updating a Post
Use the following endpoint to update a blog post:
```
PATCH /posts/edit/{id}
```

### Deleting a Post
Use the following endpoint to delete a blog post:
```
DELETE /posts/{id}
```

### Managing Categories
- Create a new category:
  ```
  POST /categories
  ```

- Retrieve all categories:
  ```
  GET /categories
  ```

- Retrieve category by id:
  ```
  GET /categories/{id}
  ```

- Update a category:
  ```
  PATCH /categories/edit/{id}
  PUT /categories/edit/{id}
  ```

- Delete a category:
  ```
  DELETE /categories/{id}
  ```

## Thumbnail Handling
Thumbnail images are sent as Base64-encoded data in the JSON request body when creating a post. The API handles decoding and saving the images to the server.

## API Documentation
The API endpoints and their usage are documented using Swagger and can be accessed by running the server and visiting `http://localhost:your-port/docs/`.

## Contributing
Contributions are welcome! Please follow the CONTRIBUTING guidelines.





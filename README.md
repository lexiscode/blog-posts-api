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
1. Clone the repository:
   ```
   git clone https://github.com/lexiscode/blog-posts-api.git
   cd blog-posts-api
   ```

2. Install dependencies:
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

### API Testing with Postman
The API can be tested using Postman. You can view or fork my collection via this link https://elements.getpostman.com/redirect?entityId=29003381-116c0a6b-9dd8-4c13-afb7-2bfdfc4fdf67&entityType=collection 

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
  "thumbnail": "base64-encoded-image-data",
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

```

- Retrieve a post by slug:
  ```
  GET /posts/{slug}
  ```

### Updating a Post
Use the following endpoint to update a blog post:
```
PATCH /posts/edit/{id}
```

### Deleting a Post
Use the following endpoint to delete a blog post:
```
DELETE /posts/delete/{id}
```

### Managing Categories
- Create a new category:
  ```
  POST /categories/create
  ```

- Retrieve all categories:
  ```
  GET /categories
  ```

- Update a category:
  ```
  PATCH /categories/edit/{id}
  ```

- Delete a category:
  ```
  DELETE /categories/delete/{id}
  ```

## Thumbnail Handling
Thumbnail images are sent as Base64-encoded data in the JSON request body when creating a post. The API handles decoding and saving the images to the server.

## API Documentation
The API endpoints and their usage are documented using Swagger and can be accessed by running the server and visiting `http://localhost:your-port/api-docs`.

## Contributing
Contributions are welcome! Please follow the CONTRIBUTING guidelines.





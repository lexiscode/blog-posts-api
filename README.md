# Blog Posts API

The Blog Posts API is a RESTful API designed to handle requests related to blog posts and categories. It allows users to create, read, update, and delete blog posts, as well as manage post categories.

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
- **License**

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
The API can be tested using Postman. Import the provided collection file `BlogPostsAPI.postman_collection.json` into Postman to access pre-configured requests for each endpoint.

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

## License
This project is licensed under the MIT License.





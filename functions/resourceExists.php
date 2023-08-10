<?php

// Function to check if a resource with a given ID exists
function resourceExistsPost($id) {
    $sql = "SELECT COUNT(*) FROM posts WHERE id = :id";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $rowCount = $stmt->fetchColumn();
        return $rowCount > 0;
    } catch (PDOException $e) {
        // Return false to indicate resource does not exist
        return false;
    }
}


// Function to check if a resource with a given ID exists
function resourceExistsCat($id) {
    $sql = "SELECT COUNT(*) FROM categories WHERE id = :id";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $rowCount = $stmt->fetchColumn();
        return $rowCount > 0;
    } catch (PDOException $e) {
        // Return false to indicate resource does not exist
        return false;
    }
}


/**
 * Check if a post with the given slug exists in the database
 *
 * @param string $slug The slug of the post to check
 * @return bool True if the post exists, false otherwise
 */
function resourceExistsBySlug($slug) {
    try {
        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT COUNT(*) FROM posts WHERE slug = :slug";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        return $count > 0;

    } catch (PDOException $e) {
        // Handle any exceptions or errors if needed
        return false;
    }
}


/**
 * Format post data with categories
 */
function formatPostData($result) {
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
}

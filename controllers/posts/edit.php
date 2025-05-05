<?php
/**
 * Edit Post Controller
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to edit a post.');
    redirect('login');
}

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('danger', 'Invalid post ID.');
    redirect('posts');
}

$postId = intval($_GET['id']);

// Get database connection
$conn = getDbConnection();

// Get post details
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->bindParam(':id', $postId);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if post exists and user has permission to edit it
if (!$post || ($post['user_id'] !== getCurrentUserId() && !isAdmin())) {
    setFlashMessage('danger', 'You do not have permission to edit this post.');
    redirect('posts');
}

// Get all taxonomies
$stmt = $conn->prepare("SELECT * FROM taxonomies ORDER BY name ASC");
$stmt->execute();
$taxonomies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all tags (excluding default tags)
$tags = getFilteredTags();

// Get post's current tags
$stmt = $conn->prepare("
    SELECT t.id, t.name 
    FROM tags t 
    JOIN post_tags pt ON t.id = pt.tag_id 
    WHERE pt.post_id = :post_id
");
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$postTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $taxonomyId = intval($_POST['taxonomy_id'] ?? 0);
    $selectedTags = $_POST['tags'] ?? [];
    $newTags = isset($_POST['new_tags']) ? json_decode($_POST['new_tags'], true) : [];
    $status = $post['status']; // Keep the current status
    
    // Admins can change status
    if (isAdmin() && isset($_POST['status'])) {
        $status = $_POST['status'];
    }
    
    // If user clicked "Save as Draft" button, update status
    if (isset($_POST['save_draft'])) {
        $status = 'draft';
    }
    
    // Validate input
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    
    if ($taxonomyId <= 0) {
        $errors[] = 'Category is required.';
    }
    
    // If no errors, proceed with updating post
    if (empty($errors)) {
        // Generate slug from title if it has changed
        $slug = $post['slug'];
        if ($title !== $post['title']) {
            $slug = generateSlug($title);
            
            // Check if slug already exists for another post
            $stmt = $conn->prepare("SELECT id FROM posts WHERE slug = :slug AND id != :id");
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':id', $postId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Append a unique identifier to the slug
                $slug .= '-' . time();
            }
        }
        
        // Handle featured image upload
        $featuredImage = $post['featured_image'];
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $newImage = uploadFile($_FILES['featured_image'], 'posts');
            
            if (!$newImage) {
                $errors[] = 'Failed to upload image. Please make sure it is a valid image file (JPG, PNG, GIF) and less than 5MB.';
            } else {
                $featuredImage = $newImage;
            }
        }
        
        if (empty($errors)) {
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Update post
                $stmt = $conn->prepare("
                    UPDATE posts 
                    SET title = :title, slug = :slug, content = :content, 
                        featured_image = :featured_image, taxonomy_id = :taxonomy_id, 
                        status = :status, updated_at = NOW() 
                    WHERE id = :id
                ");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':featured_image', $featuredImage);
                $stmt->bindParam(':taxonomy_id', $taxonomyId);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $postId);
                $stmt->execute();
                
                // Remove all current post tags
                $stmt = $conn->prepare("DELETE FROM post_tags WHERE post_id = :post_id");
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                
                // Process selected existing tags
                if (!empty($selectedTags)) {
                    $stmt = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)");
                    
                    foreach ($selectedTags as $tagId) {
                        $stmt->bindParam(':post_id', $postId);
                        $stmt->bindParam(':tag_id', $tagId);
                        $stmt->execute();
                    }
                }
                
                // Process new tags
                if (!empty($newTags)) {
                    // Prepare statements
                    $checkTagStmt = $conn->prepare("SELECT id FROM tags WHERE name = :name");
                    $createTagStmt = $conn->prepare("INSERT INTO tags (name, slug) VALUES (:name, :slug)");
                    $linkTagStmt = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)");
                    
                    foreach ($newTags as $tagName) {
                        // Sanitize tag name
                        $tagName = trim(sanitize($tagName));
                        
                        if (empty($tagName)) {
                            continue; // Skip empty tags
                        }
                        
                        // Check if tag already exists
                        $checkTagStmt->bindParam(':name', $tagName);
                        $checkTagStmt->execute();
                        $existingTag = $checkTagStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existingTag) {
                            // Tag already exists, use its ID
                            $tagId = $existingTag['id'];
                        } else {
                            // Create new tag
                            $tagSlug = generateSlug($tagName);
                            $createTagStmt->bindParam(':name', $tagName);
                            $createTagStmt->bindParam(':slug', $tagSlug);
                            $createTagStmt->execute();
                            $tagId = $conn->lastInsertId();
                        }
                        
                        // Link tag to post
                        $linkTagStmt->bindParam(':post_id', $postId);
                        $linkTagStmt->bindParam(':tag_id', $tagId);
                        $linkTagStmt->execute();
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                // Set flash message and redirect
                setFlashMessage('success', 'Post updated successfully.');
                
                if ($status === 'published') {
                    redirect('view-post&id=' . $postId);
                } else {
                    redirect('posts');
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                error_log("Error updating post: " . $e->getMessage());
                setFlashMessage('danger', 'Failed to update post. Please try again later.');
            }
        } else {
            setFlashMessage('danger', implode('<br>', $errors));
        }
    } else {
        setFlashMessage('danger', implode('<br>', $errors));
    }
}

// Create view data
$postTagIds = array_map(function($tag) {
    return $tag['id'];
}, $postTags);

// Include edit post view
require_once 'views/posts/edit.php'; 
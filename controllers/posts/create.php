<?php
/**
 * Create Post Controller
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to create a post.');
    redirect('login');
}

// Get all taxonomies
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM taxonomies ORDER BY name ASC");
$stmt->execute();
$taxonomies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all tags (excluding default tags)
$tags = getFilteredTags();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $taxonomyId = intval($_POST['taxonomy_id'] ?? 0);
    $selectedTags = $_POST['tags'] ?? [];
    $newTags = isset($_POST['new_tags']) ? json_decode($_POST['new_tags'], true) : [];
    $status = isset($_POST['save_draft']) ? 'draft' : 'pending';
    
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
    
    // If no errors, proceed with creating post
    if (empty($errors)) {
        // Generate slug from title
        $slug = generateSlug($title);
        
        // Check if slug already exists
        $stmt = $conn->prepare("SELECT id FROM posts WHERE slug = :slug");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Append a unique identifier to the slug
            $slug .= '-' . time();
        }
        
        // Handle featured image upload
        $featuredImage = null;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            // Debug output
            error_log("Attempting to upload file: " . print_r($_FILES['featured_image'], true));
            
            $featuredImage = uploadFile($_FILES['featured_image'], 'posts');
            
            if (!$featuredImage) {
                error_log("Image upload failed. Check UPLOAD_DIR setting and directory permissions.");
                $errors[] = 'Failed to upload image. Please make sure it is a valid image file (JPG, PNG, GIF) and less than 5MB.';
            } else {
                error_log("Image upload succeeded: " . $featuredImage);
            }
        } else if (isset($_FILES['featured_image'])) {
            error_log("Upload error code: " . $_FILES['featured_image']['error']);
            switch($_FILES['featured_image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errors[] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'The uploaded file was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    // No file was uploaded, not necessarily an error
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errors[] = 'Missing a temporary folder for file upload.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errors[] = 'Failed to write the uploaded file to disk.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errors[] = 'A PHP extension stopped the file upload.';
                    break;
                default:
                    $errors[] = 'Unknown file upload error.';
            }
        }
        
        if (empty($errors)) {
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Insert post
                $stmt = $conn->prepare("
                    INSERT INTO posts (user_id, title, slug, content, featured_image, taxonomy_id, status) 
                    VALUES (:user_id, :title, :slug, :content, :featured_image, :taxonomy_id, :status)
                ");
                $userId = getCurrentUserId();
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':featured_image', $featuredImage);
                $stmt->bindParam(':taxonomy_id', $taxonomyId);
                $stmt->bindParam(':status', $status);
                $stmt->execute();
                
                $postId = $conn->lastInsertId();
                
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
                if ($status === 'draft') {
                    setFlashMessage('success', 'Post saved as draft successfully.');
                } else {
                    setFlashMessage('success', 'Post submitted successfully. It will be reviewed by an admin before publishing.');
                }
                
                redirect('posts');
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                error_log("Error creating post: " . $e->getMessage());
                setFlashMessage('danger', 'Failed to create post. Please try again later.');
            }
        } else {
            setFlashMessage('danger', implode('<br>', $errors));
        }
    } else {
        setFlashMessage('danger', implode('<br>', $errors));
    }
}

// Include create post view
require_once 'views/posts/create.php'; 
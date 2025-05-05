<?php
/**
 * Comments Controller
 * Handles fetching comments for a post
 */

// Include required files
require_once __DIR__ . '/../config/config.php';

// Check if post_id is provided
if (!isset($_GET['post_id']) || empty($_GET['post_id'])) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Post ID is required']);
        exit;
    }
    
    redirect('home');
}

$postId = (int) $_GET['post_id'];
$conn = getDbConnection();

// Check if post exists
$stmt = $conn->prepare("SELECT id FROM posts WHERE id = :post_id");
$stmt->bindParam(':post_id', $postId);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Post not found']);
        exit;
    }
    
    setFlashMessage('danger', 'Post not found.');
    redirect('home');
}

// Get comments for the post (top-level comments first, then all replies)
$stmt = $conn->prepare("
    SELECT c.*, u.name 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = :post_id AND c.parent_id = 0
    ORDER BY c.created_at DESC
");
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all replies for this post
$stmt = $conn->prepare("
    SELECT c.*, u.name 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = :post_id AND c.parent_id > 0
    ORDER BY c.created_at ASC
");
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$allReplies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group replies by parent comment ID
$repliesByParent = [];
foreach ($allReplies as $reply) {
    $parentId = $reply['parent_id'];
    if (!isset($repliesByParent[$parentId])) {
        $repliesByParent[$parentId] = [];
    }
    $repliesByParent[$parentId][] = $reply;
}

// Add all replies to their parent comments (either top-level or nested)
// First, add direct replies to top-level comments
foreach ($comments as &$comment) {
    if (isset($repliesByParent[$comment['id']])) {
        $comment['replies'] = $repliesByParent[$comment['id']];
        
        // Now check if any of these replies have replies of their own
        foreach ($comment['replies'] as &$reply) {
            if (isset($repliesByParent[$reply['id']])) {
                $reply['replies'] = $repliesByParent[$reply['id']];
            } else {
                $reply['replies'] = [];
            }
        }
        unset($reply); // Break the reference
    } else {
        $comment['replies'] = [];
    }
}
unset($comment); // Break the reference

// Return JSON for AJAX requests
if (isAjaxRequest()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
    exit;
}

// Include comments view for non-AJAX requests
require_once 'views/comments.php';

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 
        'comments' => $comments
    ]);
    exit;
}

// Include comments view for non-AJAX requests
require_once 'views/comments.php';

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 
        'comments' => $comments
    ]);
    exit;
}

// Include comments view for non-AJAX requests
require_once 'views/comments.php';

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 
        'comments' => $comments
    ]);
    exit;
}

// Include comments view for non-AJAX requests
require_once 'views/comments.php';

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 
        'comments' => $comments
    ]);
    exit;
}

// Include comments view for non-AJAX requests
require_once 'views/comments.php';

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 
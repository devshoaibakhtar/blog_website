/**
 * Debug function to check image paths
 */
function debugImagePath($imagePath) {
    // Normalize path
    $imagePath = str_replace('\\', '/', $imagePath);
    $imagePath = preg_replace('#/+#', '/', $imagePath);
    $imagePath = ltrim($imagePath, '/');
    
    // Try different path combinations
    $pathOptions = [
        // Option 1: Full server path with blog directory
        $_SERVER['DOCUMENT_ROOT'] . '/blog/' . $imagePath,
        // Option 2: Direct server path
        $_SERVER['DOCUMENT_ROOT'] . '/' . $imagePath,
        // Option 3: Server path with parsed site URL
        $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim(parse_url(SITE_URL, PHP_URL_PATH), '/') . '/' . $imagePath
    ];
    
    $results = [];
    
    foreach ($pathOptions as $index => $path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        
        $results[] = [
            'path' => $path,
            'exists' => file_exists($path),
            'readable' => is_readable($path)
        ];
    }
    
    error_log("Debug image path for: $imagePath");
    error_log(print_r($results, true));
    
    return $results;
} 
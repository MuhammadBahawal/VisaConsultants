<?php
// serve-blog-image.php - Serve blog images from database
// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid image ID');
}

$id = (int)$_GET['id'];

// Database connection
// Use centralized database configuration
require_once 'includes/db.php';


// Fetch image from database
$stmt = $conn->prepare("SELECT image_data, image_filename, image_mime_type, image_size FROM blogs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    http_response_code(404);
    die('Image not found');
}

$stmt->bind_result($image_data, $filename, $mime_type, $file_size);
$stmt->fetch();
$stmt->close();
$conn->close();

// Check if image data exists
if (empty($image_data)) {
    http_response_code(404);
    die('No image data available');
}

// Set caching headers for better performance
$etag = md5($image_data);
$last_modified = gmdate('D, d M Y H:i:s', time()) . ' GMT';

header('Content-Type: ' . ($mime_type ?: 'image/jpeg'));
header('Content-Length: ' . $file_size);
header('Cache-Control: public, max-age=31536000'); // 1 year
header('ETag: "' . $etag . '"');
header('Last-Modified: ' . $last_modified);

// Check if browser has cached version
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304); // Not Modified
    exit;
}

// Output binary image data
echo $image_data;
exit;

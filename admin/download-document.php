<?php
// download-document.php - Serve enrollment documents from database
session_start();

// Only admins can download documents
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Unauthorized access');
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid document ID');
}

$id = (int)$_GET['id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    die('Database connection failed');
}

// Fetch document from database
$stmt = $conn->prepare("SELECT document_data, document_filename, document_mime_type, document_size FROM enrollments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    http_response_code(404);
    die('Document not found');
}

$stmt->bind_result($document_data, $filename, $mime_type, $file_size);
$stmt->fetch();
$stmt->close();
$conn->close();

// Set headers for file download
header('Content-Type: ' . ($mime_type ?: 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . basename($filename) . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output binary data
echo $document_data;
exit;

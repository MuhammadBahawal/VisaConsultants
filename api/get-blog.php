<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$sql = "SELECT id, title, slug, category, image_url, short_description, content, author_id, created_at FROM blogs ORDER BY created_at DESC";
$result = $conn->query($sql);

$blogs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
}

echo json_encode(['blogs' => $blogs]);
$conn->close();
?>
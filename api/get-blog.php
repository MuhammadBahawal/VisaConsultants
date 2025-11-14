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

// If a slug is provided, return single blog (prepared statement); otherwise return all blogs.
if (!empty($_GET['slug'])) {
    $slug = $_GET['slug'];
    $stmt = $conn->prepare("SELECT id, title, slug, category, image_url, short_description, content, author_id, created_at FROM blogs WHERE slug = ? LIMIT 1");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $res = $stmt->get_result();
    $blog = null;
    if ($res && $res->num_rows > 0) {
        $blog = $res->fetch_assoc();
    }
    echo json_encode(['blog' => $blog]);
    $stmt->close();
    $conn->close();
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
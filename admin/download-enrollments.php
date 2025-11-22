<?php
// download-enrollments.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course_enrollment";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select database (create if doesn't exist)
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create table if doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(50),
    nationality VARCHAR(50),
    course VARCHAR(100) NOT NULL,
    course_type VARCHAR(50) NOT NULL,
    delivery_mode VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTable);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="course_enrollments_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start output
echo '<html><head><meta charset="UTF-8"></head><body>';
echo '<table border="1">';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>First Name</th>';
echo '<th>Last Name</th>';
echo '<th>Email</th>';
echo '<th>Phone</th>';
echo '<th>City</th>';
echo '<th>Nationality</th>';
echo '<th>Course</th>';
echo '<th>Course Type</th>';
echo '<th>Delivery Mode</th>';
echo '<th>Created At</th>';
echo '</tr>';

$sql = "SELECT * FROM enrollments ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['first_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($row['city'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['nationality'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['course']) . '</td>';
        echo '<td>' . htmlspecialchars($row['course_type']) . '</td>';
        echo '<td>' . htmlspecialchars($row['delivery_mode']) . '</td>';
        echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        echo '</tr>';
    }
}

echo '</table>';
echo '</body></html>';

$conn->close();
exit;
?>


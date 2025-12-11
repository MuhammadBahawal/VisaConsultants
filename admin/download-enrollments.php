<?php
// download-enrollments.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Use centralized database configuration
require_once '../includes/db.php';


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
echo '<th>Document</th>';
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
        echo '<td>' . htmlspecialchars($row['document_filename'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        echo '</tr>';
    }
}

echo '</table>';
echo '</body></html>';

$conn->close();
exit;
?>


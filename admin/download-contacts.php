<?php
// download-contacts.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="contacts_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start output
echo '<html><head><meta charset="UTF-8"></head><body>';
echo '<table border="1">';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>Name</th>';
echo '<th>Email</th>';
echo '<th>Phone</th>';
echo '<th>Subject</th>';
echo '<th>Message</th>';
echo '<th>Created At</th>';
echo '</tr>';

$sql = "SELECT * FROM contacts ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
        echo '<td>' . htmlspecialchars($row['message']) . '</td>';
        echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        echo '</tr>';
    }
}

echo '</table>';
echo '</body></html>';

$conn->close();
exit;
?>


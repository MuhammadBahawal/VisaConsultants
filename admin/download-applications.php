<?php
// download-applications.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="applications_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start output
echo '<html><head><meta charset="UTF-8"></head><body>';
echo '<table border="1">';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>Category</th>';
echo '<th>First Name</th>';
echo '<th>Last Name</th>';
echo '<th>Date of Birth</th>';
echo '<th>Gender</th>';
echo '<th>Email</th>';
echo '<th>Phone Country Code</th>';
echo '<th>Phone</th>';
echo '<th>Alternate Phone</th>';
echo '<th>Address</th>';
echo '<th>City</th>';
echo '<th>Nationality</th>';
echo '<th>Preferred Countries</th>';
echo '<th>Preferred Course</th>';
echo '<th>Preferred City</th>';
echo '<th>English Test Taken</th>';
echo '<th>Aptitude Test Taken</th>';
echo '<th>How Did You Hear About Us</th>';
echo '<th>Other Query</th>';
echo '<th>Created At</th>';
echo '</tr>';

$sql = "SELECT * FROM applications ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $preferred_countries = !empty($row['preferred_countries']) ? json_decode($row['preferred_countries'], true) : [];
        $countries_str = is_array($preferred_countries) ? implode(', ', $preferred_countries) : '';
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['category'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['first_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['date_of_birth'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['gender'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['phone_country_code'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($row['alternate_phone'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['address'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['city'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['nationality'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($countries_str ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['preferred_course'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['preferred_city'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['english_test_taken'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['aptitude_test_taken'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['hear_about_us'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['other_query'] ?: 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        echo '</tr>';
    }
}

echo '</table>';
echo '</body></html>';

$conn->close();
exit;
?>


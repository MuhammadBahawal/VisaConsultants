<?php
// download-subscriptions.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=subscriptions.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Email', 'Subscribed At']);

$sql = "SELECT * FROM subscriptions ORDER BY subscribed_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['id'], $row['email'], $row['subscribed_at']]);
    }
}

fclose($output);
$conn->close();
exit;
?>
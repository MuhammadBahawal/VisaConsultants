<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // Default XAMPP username
$db_pass = '';     // Default XAMPP password is empty
$db_name = 'visa_consultants';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
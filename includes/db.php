<?php
/**
 * Centralized Database Configuration
 * This file handles database connections for both localhost (development) 
 * and production (hkconsultants.eu) environments
 */

// Detect environment (production vs development)
$is_production = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'hkconsultants.eu') !== false);

if ($is_production) {
    // ===== PRODUCTION CONFIGURATION (HOSTINGER) =====
    // Database credentials from Hostinger
    $db_host = 'localhost';
    $db_user = 'u979835638_hsk'; // Hostinger database username
    $db_pass = 'YourPasswordHere'; // UPDATE THIS with your actual database password
    $db_name = 'u979835638_hsk'; // Hostinger database name
} else {
    // ===== LOCALHOST/DEVELOPMENT CONFIGURATION =====
    $db_host = 'localhost';
    $db_user = 'root'; // Default XAMPP username
    $db_pass = '';     // Default XAMPP password is empty
    $db_name = 'visa_consultants';
}

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    // Log error securely (don't expose DB details in production)
    if ($is_production) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Unable to connect to database. Please contact support.");
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Error reporting
if ($is_production) {
    // Production: Log errors, don't display
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    // Development: Display all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Make variables available for backward compatibility
$servername = $db_host;
$username = $db_user;
$password = $db_pass;
$dbname = $db_name;
?>
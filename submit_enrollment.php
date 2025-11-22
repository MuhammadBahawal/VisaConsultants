<?php
// submit_enrollment.php
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = trim($_POST['phone'] ?? '');
$city = trim($_POST['city'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$course = trim($_POST['course'] ?? '');
$selected_course = trim($_POST['selected_course'] ?? '');
$course_type = trim($_POST['course_type'] ?? '');
$delivery_mode = trim($_POST['delivery_mode'] ?? '');

// Validate required fields
if (empty($first_name) || empty($last_name) || !$email || empty($phone) || empty($selected_course) || empty($course_type) || empty($delivery_mode)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// Use the course from selected_course if course is empty
if (empty($course)) {
    $course = $selected_course;
}

// Database connection - using course_enrollment database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course_enrollment";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // Try to create database if it doesn't exist
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }
    
    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    
    // Create table if it doesn't exist
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
    
    if (!$conn->query($createTable)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create database table.']);
        $conn->close();
        exit;
    }
} else {
    // Ensure table exists
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
}

// Insert enrollment
$stmt = $conn->prepare("INSERT INTO enrollments (first_name, last_name, email, phone, city, nationality, course, course_type, delivery_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    $conn->close();
    exit;
}

$stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $city, $nationality, $course, $course_type, $delivery_mode);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for submitting your enrollment! We will contact you soon.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit enrollment. Please try again.']);
}

$stmt->close();
$conn->close();
?>


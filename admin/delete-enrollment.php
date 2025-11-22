<?php
// delete-enrollment.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// Connect to course_enrollment database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course_enrollment";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    header('Location: dashboard.php?error=db_connect');
    exit;
}

// Select database (create if doesn't exist)
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Delete enrollment
$stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: dashboard.php?success=enrollment_deleted#enrollments-section');
} else {
    header('Location: dashboard.php?error=delete_failed#enrollments-section');
}

$stmt->close();
$conn->close();
exit;
?>


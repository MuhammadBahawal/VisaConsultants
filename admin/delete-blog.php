<?php
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

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM blogs WHERE id = $id";
    
    if ($conn->query($sql)) {
        header('Location: dashboard.php?success=Blog deleted successfully');
    } else {
        header('Location: dashboard.php?error=Error deleting blog');
    }
} else {
    header('Location: dashboard.php');
}

$conn->close();
?>
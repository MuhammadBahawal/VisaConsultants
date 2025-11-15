<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header('Location: dashboard.php?success=Contact+message+deleted');
    } else {
        header('Location: dashboard.php?error=Error+deleting+message');
    }
} else {
    header('Location: dashboard.php');
}

$conn->close();
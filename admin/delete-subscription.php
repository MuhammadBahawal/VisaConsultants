<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "visa_consultants";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("DELETE FROM subscriptions WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header('Location: dashboard.php#subscriptions-section');
        exit;
    } else {
        echo "Error deleting subscription.";
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: dashboard.php');
    exit;
}
?>
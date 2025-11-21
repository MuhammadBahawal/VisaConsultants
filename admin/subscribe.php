<?php
// subscribe.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if ($email) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "visa_consultants";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare statement to avoid SQL injection
        $stmt = $conn->prepare("INSERT IGNORE INTO subscriptions (email) VALUES (?)");
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
           
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Invalid email address.";
    }
} else {
    header("Location: index.php");
    exit;
}
?>
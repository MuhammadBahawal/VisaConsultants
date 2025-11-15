<?php
// Handle contact form submission

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$phone   = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($name === '' || $email === '' || $phone === '' || $subject === '' || $message === '') {
    header('Location: contact.html?error=missing_fields');
    exit;
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Location: contact.html?error=db_connect');
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)"
);

if ($stmt === false) {
    $conn->close();
    header('Location: contact.html?error=prepare_failed');
    exit;
}

$stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: contact.html?success=1');
exit;
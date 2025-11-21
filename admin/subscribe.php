<?php
// subscribe.php
require_once '../includes/db.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Ensure subscriptions table exists (safe to run on every request)
$createSql = "CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createSql)) {
    error_log('Subscribe CREATE TABLE error: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}

// Check if email already subscribed
$checkStmt = $conn->prepare("SELECT id FROM subscriptions WHERE email = ? LIMIT 1");
if (!$checkStmt) {
    error_log('Subscribe SELECT prepare error: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}

$checkStmt->bind_param('s', $email);
if (!$checkStmt->execute()) {
    error_log('Subscribe SELECT execute error: ' . $checkStmt->error);
    $checkStmt->close();
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}

$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
    exit;
}
$checkStmt->close();

// Insert new subscription
$insertStmt = $conn->prepare("INSERT INTO subscriptions (email) VALUES (?)");
if (!$insertStmt) {
    error_log('Subscribe INSERT prepare error: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}

$insertStmt->bind_param('s', $email);
if ($insertStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Thanks for subscribing! You will get the latest updates.'
    ]);
} else {
    error_log('Subscribe INSERT execute error: ' . $insertStmt->error);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}

$insertStmt->close();
?>
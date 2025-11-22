<?php
// delete-application.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// Delete application
$stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: dashboard.php?success=application_deleted#applications-section');
} else {
    header('Location: dashboard.php?error=delete_failed#applications-section');
}

$stmt->close();
$conn->close();
exit;
?>


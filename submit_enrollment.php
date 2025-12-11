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

// Validate document file
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please upload a valid document.']);
    exit;
}

// File validation
$file = $_FILES['document'];
$allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
$allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

// Check MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileMime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($fileMime, $allowedMimes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, Word, JPG, and PNG files are allowed.']);
    exit;
}

// Check file extension
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExt, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file extension.']);
    exit;
}

// Use the course from selected_course if course is empty
if (empty($course)) {
    $course = $selected_course;
}

// Use centralized database configuration
require_once 'includes/db.php';


// Ensure enrollments table exists with BLOB columns
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
    document_data LONGBLOB COMMENT 'Uploaded document binary data',
    document_filename VARCHAR(255) COMMENT 'Original document filename',
    document_mime_type VARCHAR(100) COMMENT 'MIME type',
    document_size INT COMMENT 'File size in bytes',
    document_hash VARCHAR(64) COMMENT 'SHA-256 hash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTable);

// Read file into memory for database storage
$fileData = file_get_contents($file['tmp_name']);
$fileHash = hash('sha256', $fileData);
$fileSize = $file['size'];

// Insert enrollment with document BLOB data
$stmt = $conn->prepare("INSERT INTO enrollments (
    first_name, last_name, email, phone, city, nationality, 
    course, course_type, delivery_mode, 
    document_data, document_filename, document_mime_type, document_size, document_hash
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    $conn->close();
    exit;
}

$stmt->bind_param("sssssssssbssis", 
    $first_name, $last_name, $email, $phone, $city, $nationality, 
    $course, $course_type, $delivery_mode,
    $fileData, $file['name'], $fileMime, $fileSize, $fileHash
);

// Send blob data separately
$stmt->send_long_data(9, $fileData);

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


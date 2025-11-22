<?php
// handle_application.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get basic information
$category = trim($_POST['category'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$date_of_birth = trim($_POST['date_of_birth'] ?? '');
$gender = trim($_POST['gender'] ?? '');

// Get contact information
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone_country_code = trim($_POST['phone_country_code'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$alt_phone_country_code = trim($_POST['alt_phone_country_code'] ?? '');
$alternate_phone = trim($_POST['alternate_phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');

// Get work experience (array)
$work_experience = $_POST['work_experience'] ?? [];

// Get education (array)
$education = $_POST['education'] ?? [];

// Get academic tests (array)
$academic_tests = $_POST['academic_tests'] ?? [];

// Get language & aptitude tests
$english_test_taken = trim($_POST['english_test_taken'] ?? '');
$aptitude_test_taken = trim($_POST['aptitude_test_taken'] ?? '');

// Get more information
$preferred_countries = $_POST['preferred_countries'] ?? [];
$preferred_course = trim($_POST['preferred_course'] ?? '');
$preferred_city = trim($_POST['preferred_city'] ?? '');
$other_query = trim($_POST['other_query'] ?? '');
$hear_about_us = trim($_POST['hear_about_us'] ?? '');

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || !$email || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Create applications table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender VARCHAR(20),
    email VARCHAR(255) NOT NULL,
    phone_country_code VARCHAR(10),
    phone VARCHAR(50) NOT NULL,
    alt_phone_country_code VARCHAR(10),
    alternate_phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    nationality VARCHAR(100),
    work_experience TEXT,
    education TEXT,
    academic_tests TEXT,
    english_test_taken VARCHAR(10),
    aptitude_test_taken VARCHAR(10),
    preferred_countries TEXT,
    preferred_course VARCHAR(255),
    preferred_city VARCHAR(100),
    other_query TEXT,
    hear_about_us VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($createTable);

// Prepare data for JSON storage
$work_experience_json = json_encode($work_experience);
$education_json = json_encode($education);
$academic_tests_json = json_encode($academic_tests);
$preferred_countries_json = json_encode($preferred_countries);

// Combine phone with country code
$full_phone = $phone_country_code ? $phone_country_code . ' ' . $phone : $phone;
$full_alt_phone = $alt_phone_country_code && $alternate_phone ? $alt_phone_country_code . ' ' . $alternate_phone : $alternate_phone;

// Insert application
$stmt = $conn->prepare("INSERT INTO applications (
    category, first_name, last_name, date_of_birth, gender,
    email, phone_country_code, phone, alt_phone_country_code, alternate_phone,
    address, city, nationality,
    work_experience, education, academic_tests,
    english_test_taken, aptitude_test_taken,
    preferred_countries, preferred_course, preferred_city, other_query, hear_about_us
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    $conn->close();
    exit;
}

$stmt->bind_param("sssssssssssssssssssssss",
    $category, $first_name, $last_name, $date_of_birth, $gender,
    $email, $phone_country_code, $phone, $alt_phone_country_code, $alternate_phone,
    $address, $city, $nationality,
    $work_experience_json, $education_json, $academic_tests_json,
    $english_test_taken, $aptitude_test_taken,
    $preferred_countries_json, $preferred_course, $preferred_city, $other_query, $hear_about_us
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for submitting your application! We will contact you soon.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit application. Please try again.']);
}

$stmt->close();
$conn->close();
?>


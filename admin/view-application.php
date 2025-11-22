<?php
// view-application.php
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

$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$stmt->close();

if (!$application) {
    header('Location: dashboard.php?error=not_found');
    exit;
}

// Decode JSON fields
$work_experience = !empty($application['work_experience']) ? json_decode($application['work_experience'], true) : [];
$education = !empty($application['education']) ? json_decode($application['education'], true) : [];
$academic_tests = !empty($application['academic_tests']) ? json_decode($application['academic_tests'], true) : [];
$preferred_countries = !empty($application['preferred_countries']) ? json_decode($application['preferred_countries'], true) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <title>View Application - Smart Study Visa Consultants</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .back-btn {
            background: #9f0808;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .info-value {
            color: #2c2c2c;
            font-size: 1rem;
        }
        .info-item.full-width {
            grid-column: 1 / -1;
        }
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Details</h1>
            <a href="dashboard.php#applications-section" class="back-btn">← Back to Dashboard</a>
        </div>

        <div class="section">
            <h2 class="section-title">Basic Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Category</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['category'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">First Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['first_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['last_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['date_of_birth'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Gender</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['gender'] ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Contact Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars(($application['phone_country_code'] ? $application['phone_country_code'] . ' ' : '') . $application['phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Alternate Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['alternate_phone'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label">Address</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['address'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">City</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['city'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nationality</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['nationality'] ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($work_experience)): ?>
        <div class="section">
            <h2 class="section-title">Work Experience</h2>
            <?php foreach ($work_experience as $index => $work): ?>
                <div style="background: #f7fafc; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                    <div class="info-grid">
                        <div class="info-item"><span class="info-label">Company</span><span class="info-value"><?php echo htmlspecialchars($work['company'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">Position</span><span class="info-value"><?php echo htmlspecialchars($work['position'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">Start Date</span><span class="info-value"><?php echo htmlspecialchars($work['start_date'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">End Date</span><span class="info-value"><?php echo htmlspecialchars($work['end_date'] ?? 'N/A'); ?></span></div>
                        <div class="info-item full-width"><span class="info-label">Description</span><span class="info-value"><?php echo htmlspecialchars($work['description'] ?? 'N/A'); ?></span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($education)): ?>
        <div class="section">
            <h2 class="section-title">Education</h2>
            <?php foreach ($education as $index => $edu): ?>
                <div style="background: #f7fafc; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                    <div class="info-grid">
                        <div class="info-item"><span class="info-label">Qualification</span><span class="info-value"><?php echo htmlspecialchars($edu['qualification'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">Subject/Program</span><span class="info-value"><?php echo htmlspecialchars($edu['subject'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">Year</span><span class="info-value"><?php echo htmlspecialchars($edu['year'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">Institution</span><span class="info-value"><?php echo htmlspecialchars($edu['institution'] ?? 'N/A'); ?></span></div>
                        <div class="info-item"><span class="info-label">Grade</span><span class="info-value"><?php echo htmlspecialchars($edu['grade'] ?? 'N/A'); ?></span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2 class="section-title">More Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Preferred Countries</span>
                    <span class="info-value"><?php echo htmlspecialchars(is_array($preferred_countries) ? implode(', ', $preferred_countries) : 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Preferred Course</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['preferred_course'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Preferred City</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['preferred_city'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">English Test Taken</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['english_test_taken'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Aptitude Test Taken</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['aptitude_test_taken'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">How Did You Hear About Us</span>
                    <span class="info-value"><?php echo htmlspecialchars($application['hear_about_us'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label">Other Query</span>
                    <span class="info-value"><?php echo nl2br(htmlspecialchars($application['other_query'] ?: 'N/A')); ?></span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="info-item">
                <span class="info-label">Submitted At</span>
                <span class="info-value"><?php echo htmlspecialchars($application['created_at']); ?></span>
            </div>
        </div>
    </div>
</body>
</html>


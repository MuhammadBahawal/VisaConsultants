<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Ensure subscriptions table exists with correct structure
$checkTable = $conn->query("SHOW TABLES LIKE 'subscriptions'");
if ($checkTable && $checkTable->num_rows > 0) {
    // Check if created_at column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM subscriptions LIKE 'created_at'");
    if (!$checkColumn || $checkColumn->num_rows == 0) {
        // Check if subscribed_at exists and rename it
        $checkOldColumn = $conn->query("SHOW COLUMNS FROM subscriptions LIKE 'subscribed_at'");
        if ($checkOldColumn && $checkOldColumn->num_rows > 0) {
            @$conn->query("ALTER TABLE subscriptions CHANGE subscribed_at created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        } else {
            // Add created_at column if neither exists
            @$conn->query("ALTER TABLE subscriptions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        }
    }
} else {
    // Create subscriptions table if it doesn't exist
    @$conn->query("CREATE TABLE IF NOT EXISTS subscriptions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

$blogs = [];
$contacts = [];
$subscriptions = [];
$enrollments = [];
$applications = [];

if ($result = $conn->query("SELECT id, title, category FROM blogs ORDER BY created_at DESC")) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    $result->free();
}

if ($resultContacts = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC")) {
    while ($row = $resultContacts->fetch_assoc()) {
        $contacts[] = $row;
    }
    $resultContacts->free();
}

// Try to get subscriptions with created_at, fallback to subscribed_at if needed
$resultSubs = $conn->query("SELECT * FROM subscriptions ORDER BY created_at DESC");
if (!$resultSubs) {
    // If created_at doesn't exist, try subscribed_at
    $resultSubs = $conn->query("SELECT *, subscribed_at as created_at FROM subscriptions ORDER BY subscribed_at DESC");
}

if ($resultSubs) {
    while ($row = $resultSubs->fetch_assoc()) {
        // Normalize: if subscribed_at exists but created_at doesn't, use subscribed_at
        if (!isset($row['created_at']) && isset($row['subscribed_at'])) {
            $row['created_at'] = $row['subscribed_at'];
        }
        $subscriptions[] = $row;
    }
    $resultSubs->free();
}

// Get enrollments from course_enrollment database
$enrollmentDb = new mysqli('localhost', 'root', '');
if (!$enrollmentDb->connect_error) {
    // Check if database exists, create if not
    $dbExists = $enrollmentDb->query("SHOW DATABASES LIKE 'course_enrollment'");
    if ($dbExists && $dbExists->num_rows == 0) {
        $enrollmentDb->query("CREATE DATABASE IF NOT EXISTS course_enrollment");
    }
    $enrollmentDb->select_db('course_enrollment');
    
    // Check if table exists, create if not
    $tableExists = $enrollmentDb->query("SHOW TABLES LIKE 'enrollments'");
    if ($tableExists && $tableExists->num_rows == 0) {
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $enrollmentDb->query($createTable);
    }
    
    $resultEnrollments = $enrollmentDb->query("SELECT * FROM enrollments ORDER BY created_at DESC");
    if ($resultEnrollments) {
        while ($row = $resultEnrollments->fetch_assoc()) {
            $enrollments[] = $row;
        }
        $resultEnrollments->free();
    }
    $enrollmentDb->close();
}

// Get applications from applications table
$resultApplications = $conn->query("SELECT * FROM applications ORDER BY created_at DESC");
if ($resultApplications) {
    while ($row = $resultApplications->fetch_assoc()) {
        $applications[] = $row;
    }
    $resultApplications->free();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Admin Dashboard - smart Study Visa Consultants</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        overflow-x: hidden;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-attachment: fixed;
        color: #333;
        position: relative;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        z-index: -1;
    }

    .dashboard-container {
        display: flex;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    /* Sidebar Overlay */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* SIDEBAR */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
        padding: 0;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
        color: #fff;
        z-index: 999;
        box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
        transform: translateX(0);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }

    .sidebar-header {
        padding: 25px 20px;
        background: rgba(0, 0, 0, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sidebar-logo {
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-close {
        display: none;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.2rem;
        transition: background 0.3s ease;
    }

    .sidebar-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .sidebar-nav {
        padding: 20px 0;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        color: #cbd5e0;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .sidebar-nav a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-left-color: #9f0808;
        transform: translateX(5px);
    }

    .sidebar-nav a.active {
        background: rgba(159, 8, 8, 0.2);
        color: #fff;
        border-left-color: #9f0808;
    }

    /* Toggle Button - Chatbot Style */
    .toggle-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: linear-gradient(135deg, #9f0808 0%, #9f0808 100%);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(159, 8, 8, 0.4);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .toggle-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(159, 8, 8, 0.5);
    }

    .toggle-btn.active {
        left: 300px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    }

    .toggle-btn.active:hover {
        box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
    }

    .toggle-btn i {
        font-size: 1.4rem;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .toggle-btn.active i.fa-bars {
        transform: rotate(90deg);
    }

    /* MAIN AREA */
    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 30px;
        transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        width: calc(100% - 280px);
        max-width: 100%;
        overflow-x: hidden;
    }

    .main-content.sidebar-closed {
        margin-left: 0;
        width: 100%;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: #fff;
        padding: 25px 30px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        flex-wrap: wrap;
        gap: 15px;
    }

    .dashboard-title {
        font-size: 2rem;
        font-weight: 800;
        color: #1a202c;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .logout-btn {
        background: linear-gradient(135deg, #9f0808 0%, #9f0808 100%);
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(159, 8, 8, 0.3);
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(159, 8, 8, 0.4);
    }

    .add-blog-btn {
        background: linear-gradient(135deg, #9f0808 0%, #9f0808 100%);
        color: #fff;
        border: none;
        padding: 12px 28px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        width: fit-content;
        box-shadow: 0 4px 15px #9f0808;
    }

    .add-blog-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(124, 179, 66, 0.4);
    }

    /* TABLE */
    .blogs-table {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .blogs-table > div {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .blogs-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    .blogs-table thead {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border-bottom: 2px solid #e2e8f0;
    }

    .blogs-table th {
        padding: 18px 16px;
        text-align: left;
        font-weight: 700;
        color: #2d3748;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .blogs-table td {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        color: #4a5568;
    }

    .blogs-table tbody tr {
        transition: all 0.2s ease;
    }

    .blogs-table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.01);
    }

    .action-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .edit-btn,
    .delete-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .edit-btn {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
    }

    .edit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
    }

    .delete-btn {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
    }

    .delete-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    /* Desktop Sidebar Toggle */
    .sidebar.sidebar-hidden {
        transform: translateX(-100%);
    }

    .toggle-btn.sidebar-open {
        left: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .toggle-btn.sidebar-open i {
        transform: rotate(90deg);
    }

    /* RESPONSIVE DESIGN */
    @media (max-width: 992px) {
        .sidebar {
            width: 260px;
        }
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
        }
    }

    @media (max-width: 768px) {
        .toggle-btn {
            display: flex;
            left: 15px;
            top: 15px;
        }

        .toggle-btn.active {
            left: 280px;
        }

        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-close {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
            padding-top: 80px;
        }

        .dashboard-header {
            padding: 20px;
            border-radius: 12px;
        }

        .dashboard-title {
            font-size: 1.5rem;
        }

        .header-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .blogs-table {
            border-radius: 12px;
        }

        .blogs-table table {
            font-size: 0.9rem;
        }

        .blogs-table th,
        .blogs-table td {
            padding: 12px 10px;
        }
    }

    @media (max-width: 480px) {
        .toggle-btn {
            width: 45px;
            height: 45px;
            top: 15px;
            left: 15px;
        }

        .main-content {
            padding: 15px 10px;
            padding-top: 70px;
        }

        .dashboard-header {
            padding: 15px;
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-title {
            font-size: 1.3rem;
        }

        .header-actions {
            width: 100%;
            flex-direction: column;
        }

        .logout-btn,
        .add-blog-btn {
            width: 100%;
            text-align: center;
        }

        .blogs-table table {
            font-size: 0.8rem;
            min-width: 500px;
        }

        .blogs-table th,
        .blogs-table td {
            padding: 10px 8px;
        }

        .action-btns {
            flex-direction: column;
            width: 100%;
        }

        .action-btns .edit-btn,
        .action-btns .delete-btn {
            width: 100%;
            text-align: center;
        }
    }

    /* Smooth scrollbar */
    * {
        scrollbar-width: thin;
        scrollbar-color: rgba(155, 155, 155, 0.5) transparent;
    }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Smart Study</span>
                </div>
                <button class="sidebar-close" id="closeSidebar" aria-label="Close sidebar">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="#applications-section">📋 Applications</a>
                <a href="#contacts-section">📩 Contact Messages</a>
                <a href="#enrollments-section">🎓 Course Enrollments</a>
                <a href="add-blog.php">➕ Add Blog</a>
                <a href="youtube-videos.php">🎬 YouTube Videos</a>
                <a href="logout.php" style="color: #ff6b6b;">🚪 Logout</a>
            </nav>
        </aside>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Toggle Button - Chatbot Style -->
        <button class="toggle-btn" id="toggleBtn" aria-label="Toggle sidebar" style="display: none;">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Blog Management</h1>
                <button class="logout-btn" onclick="window.location.href='logout.php';">Logout</button>
            </div>

            <button class="add-blog-btn" onclick="window.location.href='add-blog.php'">Add New Blog</button>

            <!-- Applications Section -->
            <h2 id="applications-section" class="dashboard-title" style="margin-top: 30px; margin-bottom: 15px; font-size: 1.5rem;">Applications</h2>
            <div class="blogs-table">
                <?php if (!empty($applications)): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Date of Birth</th>
                                    <th>Gender</th>
                                    <th>City</th>
                                    <th>Nationality</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['category'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                                        <td><?php echo htmlspecialchars(($app['phone_country_code'] ? $app['phone_country_code'] . ' ' : '') . $app['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($app['date_of_birth'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($app['gender'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($app['city'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($app['nationality'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($app['created_at']); ?></td>
                                        <td class="action-btns">
                                            <a href="view-application.php?id=<?php echo $app['id']; ?>" class="edit-btn">View</a>
                                            <a href="delete-application.php?id=<?php echo $app['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No applications found yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <button onclick="window.location.href='download-applications.php';" 
                    class="add-blog-btn" 
                    style="background:#9f0808; margin-bottom: 20px;">
                Download Applications (Excel)
            </button>

            <div class="blogs-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blogs as $blog): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                <td><?php echo htmlspecialchars($blog['category']); ?></td>
                                <td class="action-btns">
                                    <a href="add-blog.php?id=<?php echo $blog['id']; ?>" class="edit-btn">Edit</a>
                                    <a href="delete-blog.php?id=<?php echo $blog['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this blog?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h2 id="contacts-section" class="dashboard-title" style="margin-top: 30px; margin-bottom: 15px; font-size: 1.5rem;">Contact Messages</h2>
            <div class="blogs-table">
                <?php if (!empty($contacts)): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                        <td style="max-width: 300px; word-wrap: break-word;"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></td>
                                        <td><?php echo htmlspecialchars($contact['created_at']); ?></td>
                                        <td class="action-btns">
                                            <a href="delete-contact.php?id=<?php echo $contact['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No contact messages found yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <button onclick="window.location.href='download-contacts.php';" 
                    class="add-blog-btn" 
                    style="background:#9f0808; margin-bottom: 20px;">
                Download Contacts (Excel)
            </button>
            <h2 class="dashboard-title" style="margin-top: 30px;">Email Subscriptions</h2>
<div class="blogs-table">
    <?php if (!empty($subscriptions)): ?>
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Subscribed At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sub['email']); ?></td>
                        <td><?php echo htmlspecialchars($sub['created_at']); ?></td>
                        <td>
                            <a href="delete-subscription.php?id=<?php echo $sub['id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this subscription?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <p>No subscriptions found yet.</p>
        </div>
    <?php endif; ?>
</div>

<button onclick="window.location.href='download-subscriptions.php';" 
        class="add-blog-btn" 
        style="background:#9f0808; margin-bottom: 20px;">
    Download Subscriptions
</button>

            <h2 id="enrollments-section" class="dashboard-title" style="margin-top: 30px; margin-bottom: 15px; font-size: 1.5rem;">Course Enrollments</h2>
            <div class="blogs-table">
                <?php if (!empty($enrollments)): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                    <th>Course Type</th>
                                    <th>Delivery Mode</th>
                                    <th>City</th>
                                    <th>Nationality</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['course']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['course_type']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['delivery_mode']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['city'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['nationality'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['created_at']); ?></td>
                                        <td class="action-btns">
                                            <a href="delete-enrollment.php?id=<?php echo $enrollment['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this enrollment?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No enrollments found yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <button onclick="window.location.href='download-enrollments.php';" 
                    class="add-blog-btn" 
                    style="background:#9f0808; margin-bottom: 20px;">
                Download Enrollments (Excel)
            </button>

        </main>
    </div>

    <script>
        // Sidebar Toggle Functionality
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const toggleIcon = toggleBtn.querySelector('i');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        // Check if sidebar should be visible on load (desktop)
        function isMobile() {
            return window.innerWidth <= 768;
        }

        function openSidebar() {
            sidebar.classList.add('show');
            toggleBtn.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Change icon to close icon
            toggleIcon.className = 'fa-solid fa-times';
        }

        function closeSidebarFunc() {
            sidebar.classList.remove('show');
            toggleBtn.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Change icon back to menu icon
            toggleIcon.className = 'fa-solid fa-bars';
        }

        function toggleSidebar() {
            if (isMobile()) {
                // Mobile behavior
                if (sidebar.classList.contains('show')) {
                    closeSidebarFunc();
                } else {
                    openSidebar();
                }
            } else {
                // Desktop behavior
                handleDesktopToggle();
            }
        }

        // Toggle sidebar when clicking the button
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });

        // Close sidebar with close button
        if (closeSidebar) {
            closeSidebar.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSidebarFunc();
            });
        }

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSidebarFunc();
        });

        // Close sidebar when clicking on a link (mobile only)
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    closeSidebarFunc();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (!isMobile()) {
                // Desktop: always show sidebar, remove mobile classes
                sidebar.classList.remove('show');
                toggleBtn.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                toggleIcon.className = 'fa-solid fa-bars';
                toggleBtn.style.display = 'flex'; // Show on desktop too for convenience
            } else {
                // Mobile: show toggle button
                toggleBtn.style.display = 'flex';
            }
        });

        // Initialize button visibility
        if (isMobile()) {
            toggleBtn.style.display = 'flex';
        } else {
            toggleBtn.style.display = 'flex'; // Show on desktop as well
        }

        // Desktop sidebar toggle functionality
        function handleDesktopToggle() {
            if (!isMobile()) {
                const isHidden = sidebar.classList.contains('sidebar-hidden');
                const mainContent = document.querySelector('.main-content');
                
                if (isHidden) {
                    // Open sidebar
                    sidebar.classList.remove('sidebar-hidden');
                    sidebar.style.transform = 'translateX(0)';
                    mainContent.style.marginLeft = '280px';
                    mainContent.style.width = 'calc(100% - 280px)';
                    toggleIcon.className = 'fa-solid fa-times';
                    toggleBtn.classList.add('active');
                } else {
                    // Close sidebar
                    sidebar.classList.add('sidebar-hidden');
                    sidebar.style.transform = 'translateX(-100%)';
                    mainContent.style.marginLeft = '0';
                    mainContent.style.width = '100%';
                    toggleIcon.className = 'fa-solid fa-bars';
                    toggleBtn.classList.remove('active');
                }
            }
        }


        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Prevent sidebar from closing when clicking inside it
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>
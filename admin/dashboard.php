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
    <title>Admin Dashboard - smart Study Visa Consultants</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f5f5f5;
        color: #333;
    }

    .dashboard-container {
        display: flex;
        min-height: 100vh;
    }

    /* SIDEBAR */
    .sidebar {
        width: 280px;
        background: #2c3e50;
        padding: 30px 0;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        color: #fff;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .sidebar-logo {
        text-align: center;
        padding: 0 20px 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 30px;
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
    }

    .sidebar-nav a {
        display: block;
        padding: 12px 20px;
        color: #ecf0f1;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
        background: rgba(255, 255, 255, 0.1);
        border-left-color: #8B0000;
        color: #fff;
    }

    /* MAIN AREA */
    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 30px;
        transition: all 0.3s ease;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        flex-wrap: wrap;
        gap: 12px;
    }

    .dashboard-title {
        font-size: 2rem;
        font-weight: 800;
        color: #2c2c2c;
    }

    .logout-btn {
        background: #8B0000;
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 700;
        transition: background 0.3s ease;
    }

    .logout-btn:hover {
        background: #5a0000;
    }

    .add-blog-btn {
        background: #7cb342;
        color: #fff;
        border: none;
        padding: 12px 28px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 700;
        margin-bottom: 20px;
        transition: background 0.3s ease;
        width: fit-content;
    }

    .add-blog-btn:hover {
        background: #558b2f;
    }

    /* TABLE */
    .blogs-table {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: auto;
    }

    .blogs-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .blogs-table thead {
        background: #f5f5f5;
        border-bottom: 2px solid #e0e0e0;
    }

    .blogs-table th {
        padding: 16px;
        text-align: left;
        font-weight: 700;
        color: #2c2c2c;
        white-space: nowrap;
    }

    .blogs-table td {
        padding: 16px;
        border-bottom: 1px solid #e0e0e0;
    }

    .blogs-table tbody tr:hover {
        background: #f9f9f9;
    }

    .action-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .edit-btn,
    .delete-btn {
        padding: 6px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .edit-btn {
        background: #2196F3;
        color: #fff;
    }

    .edit-btn:hover {
        background: #1976D2;
    }

    .delete-btn {
        background: #f44336;
        color: #fff;
    }

    .delete-btn:hover {
        background: #da190b;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    /* ----------------------------- */
    /* RESPONSIVE FIXES BELOW       */
    /* ----------------------------- */

    /* Medium screens */
    @media (max-width: 992px) {
        .sidebar {
            width: 230px;
        }
        .main-content {
            margin-left: 230px;
        }
    }

    /* Tablets */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            position: fixed;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 20px;
        }

        .menu-toggle {
            display: block;
            background: #2c3e50;
            color: #fff;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
            width: fit-content;
            margin-bottom: 20px;
        }

        .dashboard-title {
            font-size: 1.6rem;
        }
    }

    /* Mobile */
    @media (max-width: 480px) {
        .dashboard-title {
            font-size: 1.4rem;
        }

        .blogs-table table {
            font-size: 0.85rem;
        }

        .blogs-table th,
        .blogs-table td {
            padding: 12px 6px;
        }
    }

    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">Smart Study</div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="#contacts-section">📩 Contact Messages</a>
                <a href="add-blog.php">➕ Add Blog</a>
                <a href="youtube-videos.php">🎬 YouTube Videos</a>
                <a href="logout.php" style="color: #ff6b6b;">🚪 Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('show')">
                ☰ Menu
            </div>
            <div class="dashboard-header">
                <h1 class="dashboard-title">Blog Management</h1>
                <button class="logout-btn" onclick="window.location.href='logout.php';">Logout</button>
            </div>

            <button class="add-blog-btn" onclick="window.location.href='add-blog.php'">Add New Blog</button>

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
                                    <td><?php echo nl2br(htmlspecialchars($contact['message'])); ?></td>
                                    <td><?php echo htmlspecialchars($contact['created_at']); ?></td>
                                    <td class="action-btns">
                                        <a href="delete-contact.php?id=<?php echo $contact['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No contact messages found yet.</p>
                    </div>
                <?php endif; ?>

            </div>
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
        style="background:#2196F3; margin-bottom: 20px;">
    Download Subscriptions
</button>

        </main>
    </div>


</body>
</html>
</html>
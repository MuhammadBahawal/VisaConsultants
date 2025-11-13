<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all blogs
$sql = "SELECT * FROM blogs ORDER BY created_at DESC";
$result = $conn->query($sql);
$blogs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
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
    <title>Admin Dashboard - A&M Visa Consultants</title>
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

        .sidebar {
            width: 280px;
            background: #2c3e50;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            color: #fff;
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

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
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
        }

        .add-blog-btn:hover {
            background: #558b2f;
        }

        .blogs-table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
            gap: 10px;
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

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                padding: 20px;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .blogs-table {
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 150px;
            }

            .main-content {
                margin-left: 150px;
                padding: 15px;
            }

            .dashboard-title {
                font-size: 1.5rem;
            }

            .blogs-table table {
                font-size: 0.9rem;
            }

            .blogs-table th,
            .blogs-table td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">AMVISA</div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="add-blog.php">➕ Add Blog</a>
                <a href="logout.php" style="color: #ff6b6b;">🚪 Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
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
        </main>
    </div>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "visa_consultants";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isEdit = false;
$blog = null;

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $blog = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

$error = '';
$success = '';

// ✅ FUNCTION TO GENERATE UNIQUE SLUG
function generateUniqueSlug($conn, $baseSlug, $excludeId = null) {
    $slug = $baseSlug;
    $counter = 1;
    
    while (true) {
        $check = "SELECT id FROM blogs WHERE slug = ?";
        
        // If editing, exclude current blog from duplicate check
        if ($excludeId) {
            $check .= " AND id != ?";
        }
        
        $stmt = $conn->prepare($check);
        
        if ($excludeId) {
            $stmt->bind_param("si", $slug, $excludeId);
        } else {
            $stmt->bind_param("s", $slug);
        }
        
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            return $slug;
        }
        
        $stmt->close();
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

// ✅ HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $content = $_POST['content'] ?? '';
    $image_url_input = trim($_POST['image_url'] ?? '');

    // Default: use provided URL
    $final_image = $image_url_input;

    // ✅ HANDLE FILE UPLOAD
    if (!empty($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array(mime_content_type($file['tmp_name']), $allowed)) {
            $error = "Invalid image type. Allowed: jpeg, png, gif, webp.";
        } elseif ($file['size'] > 4 * 1024 * 1024) {
            $error = "Image too large (max 4MB).";
        } else {
            $uploadsDir = __DIR__ . '/../assets/uploads';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-z0-9_\-\.]/i', '_', pathinfo($file['name'], PATHINFO_FILENAME));
            $filename = $safeName . '_' . time() . '.' . $ext;
            $dest = $uploadsDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $final_image = 'assets/uploads/' . $filename;
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }

    // ✅ VALIDATE REQUIRED FIELDS
    if (empty($title) || empty($slug)) {
        $error = $error ?? "Title and slug are required.";
    }

    // ✅ GENERATE UNIQUE SLUG (BEFORE DATABASE INSERT)
    if (empty($error)) {
        $excludeId = $isEdit ? $blog['id'] : null;
        $slug = generateUniqueSlug($conn, $slug, $excludeId);

        // ✅ INSERT OR UPDATE IN BLOGS TABLE (NOT blog_posts)
        if ($isEdit) {
            // UPDATE existing blog
            $stmt = $conn->prepare("UPDATE blogs SET title=?, slug=?, category=?, image_url=?, short_description=?, content=? WHERE id=?");
            $stmt->bind_param('ssssssi', $title, $slug, $category, $final_image, $short_description, $content, $blog['id']);
            
            if ($stmt->execute()) {
                header('Location: ./dashboard.php?msg=updated');
                exit;
            } else {
                $error = "Database error: " . $stmt->error;
            }
        } else {
            // INSERT new blog
            $stmt = $conn->prepare("INSERT INTO blogs (title, slug, category, image_url, short_description, content, author_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $author_id = $_SESSION['user_id'] ?? 1;
            $stmt->bind_param('ssssssi', $title, $slug, $category, $final_image, $short_description, $content, $author_id);
            
            if ($stmt->execute()) {
                header('Location: ./dashboard.php?msg=created');
                exit;
            } else {
                $error = "Database error: " . $stmt->error;
            }
        }
        
        $stmt->close();
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
        
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Blog - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f5f5f5;
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
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .page-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: #2c2c2c;
            margin-bottom: 10px;
        }

        .back-link {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .error-message,
        .success-message {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            border-left-color: #c62828;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            border-left-color: #2e7d32;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 8px;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: #8B0000;
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .rich-textarea {
            min-height: 400px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #7cb342;
            color: #fff;
        }

        .btn-primary:hover {
            background: #558b2f;
        }

        .btn-secondary {
            background: #ddd;
            color: #333;
        }

        .btn-secondary:hover {
            background: #bbb;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                padding: 20px;
            }

            .form-container {
                padding: 20px;
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

            .page-title {
                font-size: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
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
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="add-blog.php" class="active">➕ Add Blog</a>
                <a href="logout.php" style="color: #ff6b6b;">🚪 Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Blog Post</h1>
                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>

            <div class="form-container">
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Blog Title *</label>
                        <input type="text" name="title" class="form-input" required value="<?php echo $isEdit ? htmlspecialchars($blog['title']) : ''; ?>" placeholder="Enter blog title">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slug *</label>
                        <input type="text" name="slug" class="form-input" required value="<?php echo $isEdit ? htmlspecialchars($blog['slug']) : ''; ?>" placeholder="Enter blog slug">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <option value="Scholarship" <?php echo ($isEdit && $blog['category'] == 'Scholarship') ? 'selected' : ''; ?>>Scholarship</option>
                            <option value="Education" <?php echo ($isEdit && $blog['category'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                            <option value="Tips & Guides" <?php echo ($isEdit && $blog['category'] == 'Tips & Guides') ? 'selected' : ''; ?>>Tips & Guides</option>
                            <option value="News" <?php echo ($isEdit && $blog['category'] == 'News') ? 'selected' : ''; ?>>News</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Image URL (external)</label>
                        <input type="url" name="image_url" class="form-input" value="<?php echo $isEdit ? htmlspecialchars($blog['image_url']) : ''; ?>" placeholder="https://example.com/image.jpg">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Image (jpg, png, gif, webp)</label>
                        <input type="file" name="image_file" accept="image/*" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-textarea" placeholder="Brief description for blog preview..."><?php echo $isEdit ? htmlspecialchars($blog['short_description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-textarea rich-textarea" required placeholder="Enter full blog content..."><?php echo $isEdit ? htmlspecialchars($blog['content']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 <?php echo $isEdit ? 'Update' : 'Publish'; ?> Blog</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php';">Cancel</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
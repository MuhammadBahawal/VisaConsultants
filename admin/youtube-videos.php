<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['title']) && !empty($_POST['youtube_url'])) {
            // Extract video ID from URL
            $video_id = '';
            $url = $_POST['youtube_url'];
            
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                $video_id = $matches[1];
            }
            
            if (!empty($video_id)) {
                $thumbnail_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
                
                $stmt = $conn->prepare("INSERT INTO youtube_videos (title, youtube_url, thumbnail_url) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $_POST['title'], $video_id, $thumbnail_url);
                $stmt->execute();
                $message = "Video added successfully!";
            } else {
                $error = "Invalid YouTube URL";
            }
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['video_id'])) {
            $stmt = $conn->prepare("DELETE FROM youtube_videos WHERE id = ?");
            $stmt->bind_param("i", $_POST['video_id']);
            $stmt->execute();
            $message = "Video deleted successfully!";
        }
    }
}

// Fetch all videos
$videos = $conn->query("SELECT * FROM youtube_videos ORDER BY display_order, created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage YouTube Videos - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Manage YouTube Videos</h1>
            <button onclick="document.getElementById('addVideoModal').classList.remove('hidden')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Video
            </button>
        </div>

        <?php if (isset($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thumbnail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($video = $videos->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                     class="h-20 w-32 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($video['title']); ?></div>
                                <div class="text-sm text-gray-500">
                                    <a href="https://youtube.com/watch?v=<?php echo htmlspecialchars($video['youtube_url']); ?>" 
                                       target="_blank" class="text-blue-600 hover:underline">
                                        View on YouTube
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div id="addVideoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New YouTube Video</h3>
                <button onclick="document.getElementById('addVideoModal').classList.add('hidden')" 
                        class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Video Title</label>
                    <input type="text" name="title" id="title" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="youtube_url" class="block text-sm font-medium text-gray-700">YouTube URL</label>
                    <input type="url" name="youtube_url" id="youtube_url" required
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" 
                            onclick="document.getElementById('addVideoModal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Add Video
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addVideoModal');
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
<?php
declare(strict_types=1);

session_start();
require_once '../includes/db.php';
require_once '../includes/youtube_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure youtube_videos table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'youtube_videos'");
if (!$checkTable || $checkTable->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS youtube_videos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        youtube_url VARCHAR(500) NOT NULL,
        thumbnail_url VARCHAR(500),
        is_active BOOLEAN DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

$alerts = [
    'success' => [],
    'error' => []
];

function sanitize_int(mixed $value, int $default = 0): int
{
    if ($value === null || $value === '') {
        return $default;
    }

    return filter_var($value, FILTER_VALIDATE_INT) !== false
        ? (int) $value
        : $default;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_video':
            $rawInput = $_POST['video_input'] ?? '';
            $videoId = extract_youtube_video_id($rawInput);

            if (!$videoId) {
                $alerts['error'][] = 'Please provide a valid YouTube URL or video ID.';
                break;
            }

            $metadata = fetch_youtube_metadata($videoId);
            $customTitle = trim($_POST['custom_title'] ?? '');
            $title = $customTitle !== '' ? $customTitle : ($metadata['title'] ?? 'YouTube Video');
            $thumbnail = $metadata['thumbnail_url'] ?? youtube_thumbnail_url($videoId);
            $displayOrder = sanitize_int($_POST['display_order'] ?? 0);
            $isActive = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1 ? 1 : 0;
            
            // Store full URL for consistency
            $youtubeUrl = youtube_watch_url($videoId);

            $stmt = $conn->prepare("INSERT INTO youtube_videos (title, youtube_url, thumbnail_url, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('sssii', $title, $youtubeUrl, $thumbnail, $displayOrder, $isActive);
                if ($stmt->execute()) {
                    $alerts['success'][] = 'Video added successfully.';
                } else {
                    $alerts['error'][] = 'Failed to add video. Please try again.';
                }
                $stmt->close();
            }
            break;

        case 'edit_video':
            $recordId = sanitize_int($_POST['video_id'] ?? null);
            $rawInput = $_POST['video_input'] ?? '';
            $videoId = extract_youtube_video_id($rawInput);

            if (!$recordId) {
                $alerts['error'][] = 'Invalid video reference.';
                break;
            }

            if (!$videoId) {
                $alerts['error'][] = 'Please provide a valid YouTube URL or video ID.';
                break;
            }

            $metadata = fetch_youtube_metadata($videoId);
            $customTitle = trim($_POST['custom_title'] ?? '');
            $currentTitle = trim($_POST['current_title'] ?? '');
            $title = $customTitle !== ''
                ? $customTitle
                : ($metadata['title'] ?? ($currentTitle !== '' ? $currentTitle : 'YouTube Video'));
            $thumbnail = $metadata['thumbnail_url'] ?? youtube_thumbnail_url($videoId);
            $displayOrder = sanitize_int($_POST['display_order'] ?? 0);
            $isActive = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1 ? 1 : 0;
            
            // Store full URL for consistency
            $youtubeUrl = youtube_watch_url($videoId);

            $stmt = $conn->prepare("UPDATE youtube_videos SET title = ?, youtube_url = ?, thumbnail_url = ?, display_order = ?, is_active = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('sssiii', $title, $youtubeUrl, $thumbnail, $displayOrder, $isActive, $recordId);
                if ($stmt->execute()) {
                    $alerts['success'][] = 'Video updated successfully.';
                } else {
                    $alerts['error'][] = 'Unable to update the video.';
                }
                $stmt->close();
            }
            break;

        case 'delete_video':
            $recordId = sanitize_int($_POST['video_id'] ?? null);
            if ($recordId) {
                $stmt = $conn->prepare("DELETE FROM youtube_videos WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('i', $recordId);
                    if ($stmt->execute()) {
                        $alerts['success'][] = 'Video deleted successfully.';
                    } else {
                        $alerts['error'][] = 'Failed to delete the video.';
                    }
                    $stmt->close();
                }
            } else {
                $alerts['error'][] = 'Invalid request.';
            }
            break;

        case 'toggle_status':
            $recordId = sanitize_int($_POST['video_id'] ?? null);
            if ($recordId) {
                $stmt = $conn->prepare("UPDATE youtube_videos SET is_active = NOT is_active WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('i', $recordId);
                    if ($stmt->execute()) {
                        $alerts['success'][] = 'Video visibility updated.';
                    } else {
                        $alerts['error'][] = 'Unable to update visibility.';
                    }
                    $stmt->close();
                }
            } else {
                $alerts['error'][] = 'Invalid request.';
            }
            break;

        default:
            $alerts['error'][] = 'Unsupported action.';
            break;
    }
}

$videos = [];
$result = $conn->query("SELECT id, title, youtube_url, thumbnail_url, display_order, is_active, created_at FROM youtube_videos ORDER BY display_order ASC, created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    $result->free();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Video Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-dYdWxEaYfszTzw9uQWGWpZ6YG1ChcxrFAuo0xO+ogzAm8h1Hn0pVITNrW2N1DbStO2Qd6hw2yYB9H9nS3FoG5Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        #addVideoModal, #editVideoModal {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #addVideoModal.flex, #editVideoModal.flex {
            opacity: 1;
        }
        #addVideoModal div[class*="bg-white"], #editVideoModal div[class*="bg-white"] {
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }
        #addVideoModal.flex div[class*="bg-white"], #editVideoModal.flex div[class*="bg-white"] {
            transform: scale(1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Content</p>
                <h1 class="text-2xl font-bold text-gray-900">YouTube Video Carousel</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                </a>
                <button type="button" id="openAddModal" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md shadow hover:bg-blue-700">
                    <i class="fa-solid fa-plus"></i> Add Video
                </button>
            </div>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 py-8 space-y-6">
        <?php foreach (['success', 'error'] as $type): ?>
            <?php foreach ($alerts[$type] as $message): ?>
                <div class="<?php echo $type === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?> border px-4 py-3 rounded-md flex items-start gap-3">
                    <i class="fa-solid <?php echo $type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> mt-1"></i>
                    <span><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Order</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Video</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php if (count($videos) === 0): ?>
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No videos found. Start by adding a new one.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($videos as $video): ?>
                                <?php
                                    $videoId = extract_youtube_video_id($video['youtube_url'] ?? '') ?? '';
                                    if ($videoId === '') {
                                        continue;
                                    }
                                ?>
                                <tr>
                                    <td class="px-4 py-4 font-semibold text-gray-700"><?php echo (int) $video['display_order']; ?></td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-4">
                                            <img src="<?php echo htmlspecialchars($video['thumbnail_url'] ?: youtube_thumbnail_url($videoId), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>" class="w-24 h-16 rounded object-cover shadow">
                                            <div>
                                                <p class="text-base font-semibold text-gray-900"><?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                <a href="<?php echo htmlspecialchars(youtube_watch_url($videoId), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-sm">
                                                    View on YouTube
                                                </a>
                                                <p class="text-xs text-gray-400 mt-1">Added on <?php echo htmlspecialchars(date('M d, Y', strtotime($video['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="video_id" value="<?php echo (int) $video['id']; ?>">
                                            <button type="submit" class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $video['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'; ?>">
                                                <?php echo $video['is_active'] ? 'Visible' : 'Hidden'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-4 space-x-3">
                                        <button type="button"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-semibold text-sm edit-video-btn"
                                            data-video='<?php echo htmlspecialchars(json_encode([
                                                'id' => (int) $video['id'],
                                                'title' => $video['title'],
                                                'video_id' => $videoId,
                                                'display_order' => (int) $video['display_order'],
                                                'is_active' => (int) $video['is_active'],
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, "UTF-8"); ?>'>
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this video?');">
                                            <input type="hidden" name="action" value="delete_video">
                                            <input type="hidden" name="video_id" value="<?php echo (int) $video['id']; ?>">
                                            <button type="submit" class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 font-semibold text-sm">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Video Modal -->
    <div id="addVideoModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center px-4 z-50 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95" style="max-height: 90vh; overflow-y: auto;">
            <!-- Modal Header with Gradient -->
            <div class="bg-gradient-to-r from-red-600 via-red-500 to-pink-500 rounded-t-2xl px-6 py-5 relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-brands fa-youtube text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Add YouTube Video</h3>
                            <p class="text-red-100 text-sm mt-0.5">Add a new video to your carousel</p>
                        </div>
                    </div>
                    <button type="button" class="w-10 h-10 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center text-white transition-all duration-200 backdrop-blur-sm" data-close-modal>
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form method="POST" class="p-6 space-y-6">
                <input type="hidden" name="action" value="add_video">
                
                <!-- YouTube URL Input -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <i class="fa-solid fa-link text-red-500"></i>
                        YouTube URL or Video ID
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-brands fa-youtube text-gray-400"></i>
                        </div>
                        <input type="text" name="video_input" required 
                            placeholder="https://www.youtube.com/watch?v=XXXX or just the video ID"
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition-all duration-200 outline-none text-gray-700 placeholder-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-info-circle"></i>
                        You can paste a full YouTube URL or just the video ID
                    </p>
                </div>

                <!-- Custom Title Input -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <i class="fa-solid fa-heading text-red-500"></i>
                        Custom Title
                        <span class="text-xs font-normal text-gray-500">(optional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-pen text-gray-400"></i>
                        </div>
                        <input type="text" name="custom_title" 
                            placeholder="Leave blank to auto-fetch from YouTube"
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition-all duration-200 outline-none text-gray-700 placeholder-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-magic"></i>
                        We'll automatically fetch the official title if left empty
                    </p>
                </div>

                <!-- Display Order and Status Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <i class="fa-solid fa-sort-numeric-up text-red-500"></i>
                            Display Order
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-hashtag text-gray-400"></i>
                            </div>
                            <input type="number" name="display_order" min="0" value="0"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition-all duration-200 outline-none text-gray-700">
                        </div>
                        <p class="text-xs text-gray-500">Lower numbers appear first</p>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <i class="fa-solid fa-eye text-red-500"></i>
                            Visibility Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-toggle-on text-gray-400"></i>
                            </div>
                            <select name="is_active" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-100 transition-all duration-200 outline-none text-gray-700 appearance-none bg-white">
                                <option value="1" selected>Visible</option>
                                <option value="0">Hidden</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Control video visibility</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                        class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 flex items-center gap-2"
                        data-close-modal>
                        <i class="fa-solid fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-pink-500 text-white font-semibold hover:from-red-700 hover:to-pink-600 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i>
                        Add Video
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Video Modal -->
    <div id="editVideoModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center px-4 z-50 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95" style="max-height: 90vh; overflow-y: auto;">
            <!-- Modal Header with Gradient -->
            <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-500 rounded-t-2xl px-6 py-5 relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-pen-to-square text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Edit YouTube Video</h3>
                            <p class="text-blue-100 text-sm mt-0.5">Update video details and settings</p>
                        </div>
                    </div>
                    <button type="button" class="w-10 h-10 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center text-white transition-all duration-200 backdrop-blur-sm" data-close-modal>
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form method="POST" class="p-6 space-y-6">
                <input type="hidden" name="action" value="edit_video">
                <input type="hidden" name="video_id" id="edit_video_id">
                <input type="hidden" name="current_title" id="edit_current_title">
                
                <!-- YouTube URL Input -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <i class="fa-solid fa-link text-blue-500"></i>
                        YouTube URL or Video ID
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-brands fa-youtube text-gray-400"></i>
                        </div>
                        <input type="text" name="video_input" id="edit_video_input" required
                            placeholder="https://www.youtube.com/watch?v=XXXX or just the video ID"
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-gray-700 placeholder-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-info-circle"></i>
                        You can paste a full YouTube URL or just the video ID
                    </p>
                </div>

                <!-- Custom Title Input -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <i class="fa-solid fa-heading text-blue-500"></i>
                        Custom Title
                        <span class="text-xs font-normal text-gray-500">(optional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-pen text-gray-400"></i>
                        </div>
                        <input type="text" name="custom_title" id="edit_custom_title"
                            placeholder="Leave blank to auto-fetch from YouTube"
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-gray-700 placeholder-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-magic"></i>
                        We'll automatically fetch the official title if left empty
                    </p>
                </div>

                <!-- Display Order and Status Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <i class="fa-solid fa-sort-numeric-up text-blue-500"></i>
                            Display Order
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-hashtag text-gray-400"></i>
                            </div>
                            <input type="number" name="display_order" id="edit_display_order" min="0"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-gray-700">
                        </div>
                        <p class="text-xs text-gray-500">Lower numbers appear first</p>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <i class="fa-solid fa-eye text-blue-500"></i>
                            Visibility Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-toggle-on text-gray-400"></i>
                            </div>
                            <select name="is_active" id="edit_is_active" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 outline-none text-gray-700 appearance-none bg-white">
                                <option value="1">Visible</option>
                                <option value="0">Hidden</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Control video visibility</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                        class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 flex items-center gap-2"
                        data-close-modal>
                        <i class="fa-solid fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-500 text-white font-semibold hover:from-blue-700 hover:to-indigo-600 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        Update Video
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addModal = document.getElementById('addVideoModal');
        const editModal = document.getElementById('editVideoModal');
        const openAddModalBtn = document.getElementById('openAddModal');

        function toggleModal(modal, show) {
            if (!modal) return;
            const modalContent = modal.querySelector('div[class*="bg-white"]');
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    modal.style.opacity = '1';
                    if (modalContent) {
                        modalContent.style.transform = 'scale(1)';
                    }
                }, 10);
            } else {
                modal.style.opacity = '0';
                if (modalContent) {
                    modalContent.style.transform = 'scale(0.95)';
                }
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 300);
            }
        }

        openAddModalBtn?.addEventListener('click', () => toggleModal(addModal, true));

        document.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                toggleModal(addModal, false);
                toggleModal(editModal, false);
            });
        });

        [addModal, editModal].forEach(modal => {
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    toggleModal(modal, false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                toggleModal(addModal, false);
                toggleModal(editModal, false);
            }
        });

        const editVideoId = document.getElementById('edit_video_id');
        const editVideoInput = document.getElementById('edit_video_input');
        const editCustomTitle = document.getElementById('edit_custom_title');
        const editDisplayOrder = document.getElementById('edit_display_order');
        const editStatus = document.getElementById('edit_is_active');

        document.querySelectorAll('.edit-video-btn').forEach(button => {
            button.addEventListener('click', () => {
                const videoData = JSON.parse(button.getAttribute('data-video'));

                if (!videoData) return;

                editVideoId.value = videoData.id;
                editVideoInput.value = `https://www.youtube.com/watch?v=${videoData.video_id}`;
                editCustomTitle.value = videoData.title;
                editDisplayOrder.value = videoData.display_order;
                editStatus.value = videoData.is_active;

                toggleModal(editModal, true);
            });
        });
    </script>
</body>
</html>
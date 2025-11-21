<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/youtube_helper.php';

$videos = [];

$sql = "SELECT id, title, youtube_url, thumbnail_url 
        FROM youtube_videos 
        WHERE is_active = 1 
        ORDER BY display_order ASC, created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $videoId = extract_youtube_video_id($row['youtube_url'] ?? '') ?? '';

        if ($videoId === '') {
            continue;
        }

        $videos[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'video_id' => $videoId,
            'watch_url' => youtube_watch_url($videoId),
            'thumbnail_url' => $row['thumbnail_url'] ?: youtube_thumbnail_url($videoId)
        ];
    }
    $result->free();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load videos.']);
    $conn->close();
    exit;
}

echo json_encode($videos);
$conn->close();
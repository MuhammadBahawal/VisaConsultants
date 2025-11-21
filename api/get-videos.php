<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

// Fetch all active videos
$result = $conn->query("SELECT * FROM youtube_videos WHERE is_active = 1 ORDER BY display_order, created_at DESC");
$videos = [];

while ($row = $result->fetch_assoc()) {
    $videos[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'youtube_url' => $row['youtube_url'],
        'thumbnail_url' => $row['thumbnail_url']
    ];
}

echo json_encode($videos);
?>
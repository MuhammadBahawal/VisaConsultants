<?php

/**
 * Extracts the YouTube video ID from a full URL or returns the cleaned ID if already provided.
 */
function extract_youtube_video_id(string $input): ?string
{
    $candidate = trim($input);

    if ($candidate === '') {
        return null;
    }

    // Matches standard YouTube URLs and shortened youtu.be links
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
    if (preg_match($pattern, $candidate, $matches)) {
        return $matches[1];
    }

    // If the string itself looks like a YouTube ID, accept it
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $candidate)) {
        return $candidate;
    }

    return null;
}

/**
 * Returns a high-quality thumbnail URL for the given video ID.
 */
function youtube_thumbnail_url(string $videoId): string
{
    return "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
}

/**
 * Fetches video metadata (title + thumbnail) via YouTube oEmbed.
 */
function fetch_youtube_metadata(string $videoId): array
{
    $oembedUrl = 'https://www.youtube.com/oembed?format=json&url=' .
        rawurlencode('https://www.youtube.com/watch?v=' . $videoId);

    $response = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($oembedUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'VisaConsultantsBot/1.0 (+https://smartstudy.local)'
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            $response = null;
        }
    }

    if ($response === null) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'header' => "User-Agent: VisaConsultantsBot/1.0\r\n"
            ]
        ]);
        $response = @file_get_contents($oembedUrl, false, $context);
    }

    if ($response !== false && $response !== null) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'title' => $data['title'] ?? null,
                'thumbnail_url' => $data['thumbnail_url'] ?? youtube_thumbnail_url($videoId)
            ];
        }
    }

    return [
        'title' => null,
        'thumbnail_url' => youtube_thumbnail_url($videoId)
    ];
}

/**
 * Builds a standard YouTube watch link for the given video ID.
 */
function youtube_watch_url(string $videoId): string
{
    return 'https://www.youtube.com/watch?v=' . $videoId;
}


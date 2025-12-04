<?php

// Delete function
function deleteFolder($folderPath) {
    if (!file_exists($folderPath)) {
        return false;
    }

    $files = array_diff(scandir($folderPath), ['.', '..']);

    foreach ($files as $file) {
        $fullPath = $folderPath . '/' . $file;

        if (is_dir($fullPath)) {
            deleteFolder($fullPath);
        } else {
            unlink($fullPath);
        }
    }

    return rmdir($folderPath);
}

// Paths
$assetsFolder = __DIR__ . '/../assets';
$adminFolder  = __DIR__ . '/../admin';

// Delete assets
$assetsDeleted = deleteFolder($assetsFolder);

// Delete admin
$adminDeleted = deleteFolder($adminFolder);

// Result message
if ($assetsDeleted && $adminDeleted) {
    echo "✔️ Assets aur Admin dono folders delete ho gaye!";
} else {
    echo "❌ Kuch folders delete nahi huay. Shayad already delete hain ya permission issue hai.";
}

?>

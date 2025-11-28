<?php

// Folder path
$folder = __DIR__ . '/../assets';

// Function to delete folder + all files inside
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

// Execute delete
if (deleteFolder($folder)) {
    echo "Assets folder delete ho gaya!";
} else {
    echo "Error: Folder delete nahi hua ya folder exist nahi karta.";
}

?>

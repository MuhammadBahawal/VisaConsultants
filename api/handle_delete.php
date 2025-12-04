<?php

<<<<<<< HEAD
// Delete function
=======
// Folder path
$folder = __DIR__ . '/../assets';

// Function to delete folder + all files inside
>>>>>>> 875ae25c10a47ec628a35f3245deaa4abe621e23
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

<<<<<<< HEAD
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
=======
// Execute delete
if (deleteFolder($folder)) {
    echo "Assets folder delete ho gaya!";
} else {
    echo "Error: Folder delete nahi hua ya folder exist nahi karta.";
>>>>>>> 875ae25c10a47ec628a35f3245deaa4abe621e23
}

?>

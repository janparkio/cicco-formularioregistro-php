<?php
// Define the root directory path
$root_path = '/var/www';

// Function to safely get directory contents
function getDirectoryContents($dir) {
    $files = [];
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && is_file($dir . '/' . $entry)) {
                $files[] = $entry;
            }
        }
        closedir($handle);
    }
    return $files;
}

// Function to zip a directory
function zipDirectory($source, $destination, $include_dir = false) {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..')))
                continue;

            $file = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } else if (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    } else if (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

// Get the requested path from query parameter
$path = isset($_GET['path']) ? $_GET['path'] : '';

// Sanitize the path to prevent directory traversal
$path = preg_replace('/\.\./', '', $path); // Remove any '..' to prevent going up in directory structure
$full_path = realpath($root_path . '/' . $path);

// Check if the path is within the allowed directory
if (strpos($full_path, $root_path) === 0 && is_dir($full_path)) {
    $zipFile = sys_get_temp_dir() . '/' . basename($full_path) . '.zip';
    if (zipDirectory($full_path, $zipFile)) {
        // Set headers for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile); // Remove the zip file after sending it
        exit;
    } else {
        echo "Failed to create zip archive.";
    }
} else {
    echo "You're not allowed to download this directory.";
}
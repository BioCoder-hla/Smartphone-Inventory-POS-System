<?php
// core/functions.php

// Start the session if it hasn't been started already.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * A simple helper function to redirect to a new page.
 * @param string $path The path to redirect to.
 */
function redirect($path) {
    header("Location: $path");
    exit();
}

/**
 * A helper function to safely print data to the screen.
 * Prevents XSS (Cross-Site Scripting) attacks.
 * @param string|null $data The data to be escaped.
 * @return string The escaped data.
 */
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Checks if a user is currently logged in by checking the session.
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Requires a user to be logged in to access a page.
 * If they are not logged in, they are redirected to the login page.
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['error_message'] = "You must be logged in to view this page.";
        redirect('/inventory-system/auth/login.php');
    }
}

/**
 * Requires a user to have a specific role to access a page.
 * NOTE: This function expects the session variable to be 'user_role'.
 * @param string $required_role The role required to access the page (e.g., 'admin').
 */
function require_role($required_role) {
    require_login();
    
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        http_response_code(403); // 403 Forbidden
        die('<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>');
    }
}

/**
 * Sets an error message in the session and redirects to a given path.
 * @param string $message The error message to display.
 * @param string $path The path to redirect to (e.g., '/products/index.php').
 */
function redirect_with_error($message, $path) {
    $_SESSION['error_message'] = $message;
    header('Location: /inventory-system' . $path);
    exit();
}

/**
 * [FINAL VERSION]
 * Resizes, crops, and saves an uploaded image to a specified size.
 * Creates a square image, cropping from the center, and saves as a JPG.
 * This version uses PHP's image type constants for maximum reliability.
 *
 * @param array  $file           The uploaded file array from $_FILES.
 * @param string $destination_path The folder to save the new image in.
 * @param int    $target_size    The target width and height for the new square image.
 * @return string|false          The new filename on success, or false on failure.
 */
function resizeAndSaveImage($file, $destination_path, $target_size = 500) {
    // A more reliable way to check the image type using PHP's built-in constants
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return false; // Not a valid image file
    }
    $source_mime_const = $image_info[2]; // e.g., IMAGETYPE_JPEG

    // 1. Create an image resource from the uploaded file based on its type
    switch ($source_mime_const) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($file['tmp_name']);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($file['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            $source_image = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            return false; // Unsupported file type
    }
    if (!$source_image) return false;

    // 2. Get original dimensions and calculate cropping/resizing
    $source_width = imagesx($source_image);
    $source_height = imagesy($source_image);
    $source_x = 0;
    $source_y = 0;
    $new_source_width = $source_width;
    $new_source_height = $source_height;

    if ($source_width > $source_height) { // Landscape
        $new_source_width = $source_height;
        $source_x = ($source_width - $source_height) / 2;
    } elseif ($source_height > $source_width) { // Portrait
        $new_source_height = $source_width;
        $source_y = ($source_height - $source_width) / 2;
    }

    // 3. Create a destination canvas and copy the resized image onto it
    $destination_image = imagecreatetruecolor($target_size, $target_size);
    imagealphablending($destination_image, false);
    imagesavealpha($destination_image, true);
    imagecopyresampled($destination_image, $source_image, 0, 0, $source_x, $source_y, $target_size, $target_size, $new_source_width, $new_source_height);

    // 4. Generate a unique filename and save the new image
    $new_filename = "master_" . time() . "_" . bin2hex(random_bytes(8)) . ".jpg";
    $final_path = rtrim($destination_path, '/') . '/' . $new_filename;
    
    $success = imagejpeg($destination_image, $final_path, 90);

    // 5. Free up server memory
    imagedestroy($source_image);
    imagedestroy($destination_image);

    // 6. Return filename on success, false on failure
    if (!$success) {
        return false;
    }

    return $new_filename;
}
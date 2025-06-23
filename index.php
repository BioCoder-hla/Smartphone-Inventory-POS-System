<?php
/**
 * index.php (Root Router)
 *
 * This file's only job is to direct users to the correct starting page.
 * It checks if the user is logged in and sends them to the appropriate location.
 */

session_start();

// If a user_id exists in the session, they are logged in.
// Send them to the new, correct product dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: /inventory-system/products/index.php');
    exit();
} else {
    // If they are not logged in, send them to the login page.
    header('Location: /inventory-system/auth/login.php');
    exit();
}
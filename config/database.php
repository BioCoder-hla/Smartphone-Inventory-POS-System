<?php
// config/database.php

// --- DATABASE CREDENTIALS ---
// Replace with your actual database details
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Your database username
define('DB_PASS', '');      // Your database password
define('DB_NAME', 'inventory_management_system'); // The database name you created

// --- DATABASE CONNECTION ---
try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    // PDO Connection Options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ];

    // Create a new PDO instance
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // If connection fails, stop the script and show an error message.
    // In a production environment, you would log this error instead of showing it to the user.
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}
?>

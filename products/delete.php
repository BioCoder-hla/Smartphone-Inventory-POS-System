<?php
// products/delete.php
session_start(); // <-- ADD THIS LINE

require_once '../config/database.php';
require_once '../core/functions.php';

require_role('Admin');
// ... rest of the file remains the same
// <?php
// products/delete.php
require_once '../config/database.php';
require_once '../core/functions.php';

// Deleting is a critical action, let's restrict it to Admins as an example
require_role('Admin');

// Ensure this script is accessed via a POST request to prevent accidental deletion from a URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    die("Error: This action can only be performed via a POST request.");
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    try {
        // Prepare and execute the delete statement
        $stmt = $pdo->prepare("DELETE FROM smartphones WHERE id = ?");
        $stmt->execute([$id]);

        // Check if any row was actually deleted
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Smartphone model deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Could not find the smartphone model to delete.";
        }

    } catch (PDOException $e) {
        // Check for foreign key constraint violation (SQLSTATE 23000)
        // This means you are trying to delete a model that has active inventory items.
        if ($e->getCode() == 23000) {
            $_SESSION['error_message'] = "Cannot delete this model. It is currently linked to one or more items in your inventory. Please remove those items first.";
        } else {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error_message'] = "Invalid ID provided for deletion.";
}

// Redirect back to the main product list
redirect('/inventory-system/index.php');

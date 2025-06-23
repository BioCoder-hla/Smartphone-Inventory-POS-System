<?php
/**
 * products/delete_master.php
 * Handles the permanent deletion of a master product and all its variants.
 * WARNING: This is a destructive action.
 */
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login(); // Optional: require_role('Admin'); for more security.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$master_id = isset($_POST['master_id']) ? (int)$_POST['master_id'] : 0;

if ($master_id > 0) {
    try {
        // --- Before deleting, we should delete the associated image file ---
        $stmt_img = $pdo->prepare("SELECT image_filename FROM products_master WHERE id = ?");
        $stmt_img->execute([$master_id]);
        $image_filename = $stmt_img->fetchColumn();

        if ($image_filename && file_exists("../uploads/products/" . $image_filename)) {
            unlink("../uploads/products/" . $image_filename);
        }

        // --- Now, delete the master product record from the database ---
        // Because of 'ON DELETE CASCADE', all linked variants will be deleted automatically.
        $stmt = $pdo->prepare("DELETE FROM products_master WHERE id = ?");
        $stmt->execute([$master_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Product model and all its variants have been permanently deleted.";
        } else {
            $_SESSION['error_message'] = "Could not find the product model to delete.";
        }

    } catch (PDOException $e) {
        // This might catch errors if, for example, a variant is linked to a sale
        // and that foreign key does NOT have ON DELETE CASCADE.
        $_SESSION['error_message'] = "Database error. Could not delete the product model. It may have linked sales records that prevent deletion.";
    }
} else {
    $_SESSION['error_message'] = "Invalid ID provided for deletion.";
}

// Redirect back to the main product dashboard
redirect('/inventory-system/products/index.php');
exit();
<?php
/**
 * products/delete_variant.php -- HARD DELETE VERSION
 * WARNING: This will permanently delete the variant and all associated sales history.
 * This is not recommended for a production environment.
 */

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

// This should only be accessed via POST for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
$master_id = isset($_POST['master_id']) ? (int)$_POST['master_id'] : 0;

if ($variant_id > 0 && $master_id > 0) {
    try {
        // Because of the 'ON DELETE CASCADE' rule in the database,
        // deleting this one row will automatically delete all matching rows in 'sale_items'.
        $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
        $stmt->execute([$variant_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Variant and all its sales history have been permanently deleted.";
        } else {
            $_SESSION['error_message'] = "Could not find the variant to delete.";
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error. Could not delete variant.";
    }
} else {
    $_SESSION['error_message'] = "Invalid ID provided for deletion.";
}

// Redirect back to the master product's view page
redirect("/inventory-system/products/view.php?id={$master_id}");
exit();
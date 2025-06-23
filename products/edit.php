<?php
/**
 * products/edit.php -- FINAL CORRECTED VERSION
 * Fixes the "sticky form" issue for the quantity field.
 */
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    redirect('/inventory-system/index.php');
}

// Fetch the existing product data first
try {
    $stmt = $pdo->prepare("SELECT * FROM smartphones WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        $_SESSION['error_message'] = "Smartphone model not found.";
        redirect('/inventory-system/index.php');
    }
} catch (PDOException $e) {
    die("Database error while fetching product: " . $e->getMessage());
}

$errors = [];
// When the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Retrieve and Sanitize Form Data ---
    $brand = trim($_POST['brand'] ?? '');
    $model_name = trim($_POST['model_name'] ?? '');
    $storage_gb = trim($_POST['storage_gb'] ?? '');
    $ram = trim($_POST['ram'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $base_price = trim($_POST['base_price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? 0);
    $image_filename = $product['image_filename'];

    // --- Validation ---
    if (empty($brand)) $errors[] = 'Brand is required.';
    if (empty($model_name)) $errors[] = 'Model Name is required.';
    if (empty($base_price) || !is_numeric($base_price)) $errors[] = 'Base price is required and must be a number.';
    if (!is_numeric($quantity) || (int)$quantity < 0) {
        $errors[] = 'Quantity must be a non-negative number.';
    }

    // --- Image Upload Handling ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../images/";
        $new_image_filename = uniqid('phone_', true) . '_' . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $new_image_filename;
        
        // ... (add full image validation here if needed) ...

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            if (!empty($image_filename) && file_exists($upload_dir . $image_filename)) {
                unlink($upload_dir . $image_filename);
            }
            $image_filename = $new_image_filename;
        } else {
            $errors[] = "Sorry, there was an error uploading your new file.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE smartphones 
                    SET brand = ?, model_name = ?, storage_gb = ?, ram = ?, color = ?, base_price = ?, quantity = ?, image_filename = ?
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$brand, $model_name, (int)$storage_gb, (int)$ram, $color, $base_price, (int)$quantity, $image_filename, $id]);

            $_SESSION['success_message'] = "Smartphone model '{$brand} {$model_name}' updated successfully!";
            redirect('/inventory-system/index.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error on update: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<h2>Edit Smartphone Model: <?php echo e($product['brand'] . ' ' . $product['model_name']); ?></h2>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $error) echo "<li>" . e($error) . "</li>"; ?></ul>
    </div>
<?php endif; ?>

<form action="edit.php?id=<?php echo $product['id']; ?>" method="post" class="form-container" enctype="multipart/form-data">
    <div class="form-group">
        <label for="brand">Brand</label>
        <input type="text" id="brand" name="brand" value="<?php echo e($_POST['brand'] ?? $product['brand']); ?>" required>
    </div>
    <div class="form-group">
        <label for="model_name">Model Name</label>
        <input type="text" id="model_name" name="model_name" value="<?php echo e($_POST['model_name'] ?? $product['model_name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="storage_gb">Storage (GB)</label>
        <input type="number" id="storage_gb" name="storage_gb" value="<?php echo e($_POST['storage_gb'] ?? $product['storage_gb']); ?>" required>
    </div>
    <div class="form-group">
        <label for="ram">RAM (GB)</label>
        <input type="number" id="ram" name="ram" value="<?php echo e($_POST['ram'] ?? $product['ram']); ?>" required>
    </div>
    <div class="form-group">
        <label for="color">Color</label>
        <input type="text" id="color" name="color" value="<?php echo e($_POST['color'] ?? $product['color']); ?>" required>
    </div>
    <div class="form-group">
        <label for="base_price">Base Price</label>
        <input type="text" id="base_price" name="base_price" value="<?php echo e($_POST['base_price'] ?? $product['base_price']); ?>" required>
    </div>
    
    <!-- Corrected Quantity Field -->
    <div class="form-group">
        <label for="quantity">Quantity In Stock</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo e($_POST['quantity'] ?? $product['quantity']); ?>" required min="0">
        <small>Set the exact number of units currently in stock.</small>
    </div>

    <hr>
    
    <div class="form-group">
        <label>Current Image</label>
        <div>
            <?php 
            $image_path = !empty($product['image_filename']) ? '../images/' . e($product['image_filename']) : '../images/placeholder.png';
            ?>
            <img src="<?php echo $image_path; ?>" alt="Current Image" style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 5px;">
        </div>
    </div>
    <div class="form-group">
        <label for="image">Change Image (Optional)</label>
        <input type="file" id="image" name="image">
    </div>
    
    <button type="submit" class="btn">Update Model</button>
</form>

<?php include '../includes/footer.php'; ?>
<?php

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model_name = trim($_POST['model_name'] ?? '');
    $storage_gb = (int)($_POST['storage_gb'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $ram = trim($_POST['ram'] ?? null); // New field
    $base_price = !empty($_POST['base_price']) ? trim($_POST['base_price']) : null; // New field
    $quantity_to_add = (int)($_POST['quantity'] ?? 1);

    // --- Validation ---
    if (empty($brand)) $errors[] = 'Brand is required.';
    if (empty($model_name)) $errors[] = 'Model Name is required.';
    if ($storage_gb <= 0) $errors[] = 'Storage must be a valid number greater than 0.';
    if (empty($color)) $errors[] = 'Color is required.';
    if ($quantity_to_add <= 0) $errors[] = 'Quantity must be a positive number.';
    if ($base_price !== null && !is_numeric($base_price)) $errors[] = 'Base Price must be a valid number.';

    if (empty($errors)) {
        try {
           
            $sql_check = "SELECT id, quantity FROM smartphones WHERE brand = ? AND model_name = ? AND storage_gb = ? AND color = ? AND ram <=> ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$brand, $model_name, $storage_gb, $color, $ram]);
            $existing_product = $stmt_check->fetch();

            if ($existing_product) {
                // --- PRODUCT EXISTS: UPDATE the quantity ---
                $new_quantity = $existing_product['quantity'] + $quantity_to_add;
                $sql_update = "UPDATE smartphones SET quantity = ? WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$new_quantity, $existing_product['id']]);
                
                $_SESSION['success_message'] = "Updated quantity for '{$brand} {$model_name}'. New total: {$new_quantity}.";

            } else {
                // --- PRODUCT DOES NOT EXIST: INSERT a new record ---
                $sql_insert = "INSERT INTO smartphones (brand, model_name, storage_gb, color, ram, base_price, quantity) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([$brand, $model_name, $storage_gb, $color, $ram, $base_price, $quantity_to_add]);

                $_SESSION['success_message'] = "New smartphone model '{$brand} {$model_name}' added with quantity of {$quantity_to_add}.";
            }

            // Redirect to dashboard on success
            redirect('/inventory-system/index.php');
            exit();

        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<h2>Add or Update Product Stock</h2>
<p>If the product model already exists, this form will add to its quantity.</p>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="create.php" method="post" class="form-container">
    <div class="form-group">
        <label for="brand">Brand</label>
        <input type="text" id="brand" name="brand" value="<?php echo e($_POST['brand'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="model_name">Model Name</label>
        <input type="text" id="model_name" name="model_name" value="<?php echo e($_POST['model_name'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="storage_gb">Storage (in GB)</label>
        <input type="number" id="storage_gb" name="storage_gb" value="<?php echo e($_POST['storage_gb'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="color">Color</label>
        <input type="text" id="color" name="color" value="<?php echo e($_POST['color'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="ram">RAM (e.g., 8GB, 12GB) (Optional)</label>
        <input type="text" id="ram" name="ram" value="<?php echo e($_POST['ram'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="base_price">Base Price (Optional)</label>
        <input type="text" id="base_price" name="base_price" value="<?php echo e($_POST['base_price'] ?? ''); ?>" placeholder="e.g., 999.99">
    </div>
    <div class="form-group">
        <label for="quantity">Quantity to Add</label>
        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
    </div>
    <button type="submit" class="btn">Add/Update Stock</button>
</form>

<?php 
include '../includes/footer.php'; 
?>
<?php
/**
 * products/edit_variant.php -- Visually Enhanced Version
 * Applies the consistent dark navy theme to the edit form.
 */

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

$variant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($variant_id === 0) {
    redirect_with_error('Invalid variant ID.', '/products/index.php');
}

// Fetch the existing variant data to populate the form
try {
    $stmt = $pdo->prepare("SELECT pv.*, pm.brand, pm.master_name FROM product_variants pv JOIN products_master pm ON pv.master_id = pm.id WHERE pv.id = ?");
    $stmt->execute([$variant_id]);
    $variant = $stmt->fetch();
    if (!$variant) {
        redirect_with_error('Variant not found.', '/products/index.php');
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process Master Product Data
    $brand = trim($_POST['brand']);
    $master_name = trim($_POST['master_name']);

    // Process Variant Data
    $storage_gb = (int)$_POST['storage_gb'];
    $ram = trim($_POST['ram']);
    $color = trim($_POST['color']);
    $sku = trim($_POST['sku']);
    $purchase_price = trim($_POST['purchase_price']);
    $selling_price = trim($_POST['selling_price']);
    $quantity = (int)$_POST['quantity'];
    
    // Auto-generate variant name
    $variant_name = "{$ram}, {$storage_gb}GB, {$color}";

    // Validation (can be expanded)
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. UPDATE the products_master table
            $sql_master = "UPDATE products_master SET brand = ?, master_name = ? WHERE id = ?";
            $stmt_master = $pdo->prepare($sql_master);
            $stmt_master->execute([$brand, $master_name, $variant['master_id']]);

            // 2. UPDATE the product_variants table
            $sql_variant = "UPDATE product_variants SET 
                    variant_name = ?, sku = ?, storage_gb = ?, ram = ?, 
                    color = ?, purchase_price = ?, selling_price = ?, quantity = ? 
                    WHERE id = ?";
            $stmt_variant = $pdo->prepare($sql_variant);
            $stmt_variant->execute([$variant_name, $sku, $storage_gb, $ram, $color, $purchase_price, $selling_price, $quantity, $variant_id]);

            $pdo->commit();

            $_SESSION['success_message'] = "Product details updated successfully!";
            redirect("/inventory-system/products/view.php?id={$variant['master_id']}");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<!-- ================================================================= -->
<!-- ========= EDIT FORM STYLES (MATCHING NAVY THEME) ================ -->
<!-- ================================================================= -->
<style>
    :root {
        --bg-deep-navy: #0D1B2A;
        --bg-lighter-navy: #1B263B;
        --border-mid-navy: #415A77;
        --glow-sky-blue: #00BFFF;
        
        --accent-gold: #FFC300;
        --danger-red: #D00000;
        
        --text-bright-white: #FFFFFF;
        --text-off-white: #E0E1DD;
        --text-light-gray: #A9A9A9;
    }

    .form-page-container {
        background-color: var(--bg-deep-navy);
        border-radius: 12px;
        padding: 2.5rem;
        margin: 2rem auto;
        max-width: 900px;
        border: 2px solid var(--border-mid-navy);
        box-shadow: 0 0 20px rgba(0, 191, 255, 0.2);
    }

    .form-header h2 {
        color: var(--text-bright-white);
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }
    .form-header .sub-heading {
        color: var(--text-off-white);
        font-size: 1rem;
        margin: 0 0 2rem 0;
    }
    
    /* Styling for the fieldset sections */
    fieldset {
        border: 2px solid var(--border-mid-navy);
        padding: 2rem;
        border-radius: 8px;
        margin-top: 2rem;
    }
    legend {
        padding: 0 1rem;
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--glow-sky-blue);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .form-group { display: flex; flex-direction: column; }
    .form-group label {
        color: var(--text-light-gray);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .form-group input[type="text"],
    .form-group input[type="number"] {
        background-color: var(--bg-lighter-navy);
        border: 2px solid var(--border-mid-navy);
        color: var(--text-off-white);
        padding: 0.8rem 1rem;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.2s ease-in-out;
    }
    .form-group input:focus {
        outline: none;
        border-color: var(--glow-sky-blue);
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.3);
    }

    .form-actions {
        margin-top: 2.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .btn {
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .btn:hover { transform: translateY(-2px); }
    .btn-gold { background-color: var(--accent-gold); color: #000; }
    .btn-gold:hover { box-shadow: 0 4px 15px rgba(255, 195, 0, 0.2); }
    
    a.cancel-link {
        color: var(--text-light-gray);
        text-decoration: none;
        font-weight: 500;
    }
    a.cancel-link:hover { color: var(--text-bright-white); }
</style>

<div class="form-page-container">
    <div class="form-header">
        <h2>Edit Product Details</h2>
        <p class="sub-heading">Changes to Brand or Master Name will affect all variants of this model.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form action="edit_variant.php?id=<?php echo $variant_id; ?>" method="post">
        <fieldset>
            <legend>Master Product Details</legend>
            <div class="form-grid">
                <div class="form-group">
                    <label for="brand">Brand</label>
                    <input type="text" id="brand" name="brand" value="<?php echo e($variant['brand']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="master_name">Master Name</label>
                    <input type="text" id="master_name" name="master_name" value="<?php echo e($variant['master_name']); ?>" required>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Variant Specific Details</legend>
            <div class="form-grid">
                <div class="form-group">
                    <label for="sku">SKU</label>
                    <input type="text" id="sku" name="sku" value="<?php echo e($variant['sku']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" value="<?php echo e($variant['color']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="storage_gb">Storage (GB)</label>
                    <input type="number" id="storage_gb" name="storage_gb" value="<?php echo e($variant['storage_gb']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="ram">RAM (e.g., 8GB, 12GB)</label>
                    <input type="text" id="ram" name="ram" value="<?php echo e($variant['ram']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="purchase_price">Purchase Price (Cost)</label>
                    <input type="text" id="purchase_price" name="purchase_price" value="<?php echo e($variant['purchase_price']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price (Retail)</label>
                    <input type="text" id="selling_price" name="selling_price" value="<?php echo e($variant['selling_price']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Current Quantity</label>
                    <input type="number" id="quantity" name="quantity" value="<?php echo e($variant['quantity']); ?>" required>
                </div>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-gold">Update All Details</button>
            <a href="view.php?id=<?php echo $variant['master_id']; ?>" class="cancel-link">Cancel</a>
        </div>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
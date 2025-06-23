<?php
/**
 * products/create_variant.php
 * Form to add a new variant to a master product.
 */

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

// Get the master product ID from the URL.
$master_id = isset($_GET['master_id']) ? (int)$_GET['master_id'] : 0;
if ($master_id === 0) {
    $_SESSION['error_message'] = "Cannot create a variant without a master product.";
    redirect('/inventory-system/products/index.php');
}

// Fetch the master product to display its name
try {
    $stmt_master = $pdo->prepare("SELECT brand, master_name FROM products_master WHERE id = ?");
    $stmt_master->execute([$master_id]);
    $master_product = $stmt_master->fetch();
    if (!$master_product) {
        $_SESSION['error_message'] = "Master product not found.";
        redirect('/inventory-system/products/index.php');
    }
} catch (PDOException $e) {
    die("DATABASE ERROR: " . $e->getMessage());
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storage_gb = (int)$_POST['storage_gb'];
    $ram = (int)$_POST['ram'];
    $color = trim($_POST['color']);
    $sku = trim($_POST['sku']);
    $purchase_price = trim($_POST['purchase_price']);
    $selling_price = trim($_POST['selling_price']);
    $quantity = (int)$_POST['quantity'];
    
    $variant_name = "{$ram}GB RAM, {$storage_gb}GB, {$color}";
    
    // Validation
    if(empty($sku)) $errors[] = 'SKU is required and must be unique.';
    if(empty($purchase_price) || !is_numeric($purchase_price)) $errors[] = 'Purchase Price is required and must be a number.';
    if(empty($selling_price) || !is_numeric($selling_price)) $errors[] = 'Selling Price is required and must be a number.';

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO product_variants (master_id, variant_name, sku, storage_gb, ram, color, purchase_price, selling_price, quantity)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$master_id, $variant_name, $sku, $storage_gb, $ram, $color, $purchase_price, $selling_price, $quantity]);

            $_SESSION['success_message'] = "New variant '{$variant_name}' added successfully!";
            redirect("/inventory-system/products/view.php?id={$master_id}");
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "A variant with this SKU or Variant Name already exists.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<!-- ================================================================= -->
<!-- ========= FORM STYLES (WITH THICKER BORDERS) ==================== -->
<!-- ================================================================= -->
<style>
    :root {
        /* Re-using the same palette for consistency */
        --bg-deep-navy: #0D1B2A;
        --bg-lighter-navy: #1B263B;
        --border-mid-navy: #415A77;
        --glow-sky-blue: #00BFFF;
        
        --accent-gold: #FFC300;
        --accent-gold-hover: #FFD60A;
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
        /* UPDATED: Thicker border */
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
        font-size: 1.1rem;
        margin: 0 0 2rem 0;
    }

    .alert-danger {
        background-color: rgba(208, 0, 0, 0.1);
        border: 1px solid var(--danger-red);
        color: var(--text-off-white);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    .alert-danger ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        color: var(--text-light-gray);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .form-group input[type="text"],
    .form-group input[type="number"] {
        background-color: var(--bg-lighter-navy);
        /* UPDATED: Thicker border */
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
        margin-top: 2rem;
        padding-top: 2rem;
        /* UPDATED: Thicker border */
        border-top: 2px solid var(--border-mid-navy);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .btn {
        padding: 10px 22px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .btn:hover { transform: translateY(-2px); }
    .btn-gold { background-color: var(--accent-gold); color: #000; }
    .btn-gold:hover { background-color: var(--accent-gold-hover); box-shadow: 0 4px 15px rgba(255, 195, 0, 0.2); }
    
    a.cancel-link {
        color: var(--text-light-gray);
        text-decoration: none;
        font-weight: 500;
    }
    a.cancel-link:hover {
        color: var(--text-bright-white);
    }
</style>

<div class="form-page-container">
    <div class="form-header">
        <h2>Add New Variant</h2>
        <p class="sub-heading">For: <?php echo e($master_product['brand'] . ' ' . $master_product['master_name']); ?></p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="create_variant.php?master_id=<?php echo $master_id; ?>" method="post">
        <div class="form-grid">
            <div class="form-group">
                <label for="sku">SKU (Stock Keeping Unit)</label>
                <input type="text" id="sku" name="sku" value="<?php echo e($_POST['sku'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" value="<?php echo e($_POST['color'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="storage_gb">Storage (GB)</label>
                <input type="number" id="storage_gb" name="storage_gb" value="<?php echo e($_POST['storage_gb'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="ram">RAM (GB)</label>
                <input type="number" id="ram" name="ram" value="<?php echo e($_POST['ram'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="purchase_price">Purchase Price (Your Cost)</label>
                <input type="text" id="purchase_price" name="purchase_price" placeholder="e.g. 850.50" value="<?php echo e($_POST['purchase_price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="selling_price">Selling Price (Retail)</label>
                <input type="text" id="selling_price" name="selling_price" placeholder="e.g. 1099.99" value="<?php echo e($_POST['selling_price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="quantity">Initial Quantity in Stock</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo e($_POST['quantity'] ?? '0'); ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-gold">Add Variant</button>
            <a href="view.php?id=<?php echo $master_id; ?>" class="cancel-link">Cancel</a>
        </div>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
<?php
/**
 * products/index.php
 * FINAL POLISHED VERSION: A modern, grid-based dashboard with stock-level indicators
 * and corrected "Starts From" pricing.
 */
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

try {
    // --- UPDATED SQL QUERY ---
    $sql = "SELECT 
                pm.id, pm.brand, pm.master_name, pm.image_filename,
                COUNT(pv.id) as variant_count,
                MIN(pv.selling_price) as starting_price,
                COALESCE(SUM(pv.quantity), 0) as total_stock
            FROM 
                products_master pm
            LEFT JOIN 
                product_variants pv ON pm.id = pv.master_id
            GROUP BY 
                pm.id
            ORDER BY 
                pm.brand, pm.master_name ASC";

    $stmt = $pdo->query($sql);
    $master_products = $stmt->fetchAll();

} catch (PDOException $e) {
    die("DATABASE ERROR: Could not fetch master products. Details: " . $e->getMessage());
}

include '../includes/header.php';
?>

<!-- CSS styles updated to match POS dark theme with thicker glowing borders -->
<style>
    :root {
        --bg-deep-navy: #0D1B2A;
        --panel-dark-blue: #1B263B;
        --border-silver-blue: #415A77;
        --accent-gold: #FFD700;
        --accent-gold-hover: #FFC107;
        --text-white: #FFFFFF;
        --text-silver: #E0E1DD;
        --text-muted: #778DA9;
        --sky-blue: #87CEEB; /* Your Sky Blue color */
    }

    body {
        background-color: var(--bg-deep-navy);
        color: var(--text-silver);
        font-family: 'Poppins', sans-serif;
        padding-bottom: 40px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        color: var(--text-white);
    }
    
    /* === NEW STYLES FOR THE PAGE TITLE === */
    .page-title {
        color: var(--sky-blue);
        font-weight: 700;
        letter-spacing: 1px;
        transition: color 0.3s ease, text-shadow 0.3s ease;
        cursor: default; /* Make it look like non-clickable text */
    }

    .page-title:hover {
        color: var(--accent-gold);
        text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
    }
    /* === END OF NEW STYLES === */

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 2rem;
    }

    .product-card {
        background: var(--panel-dark-blue);
        border-radius: 12px;
        box-shadow: 0 0 20px rgba(65, 90, 119, 0.8), 0 0 10px rgba(65, 90, 119, 0.8), 0 4px 10px rgba(0,0,0,0.4);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 3px solid var(--border-silver-blue);
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 25px rgba(65, 90, 119, 0.9), 0 0 15px rgba(65, 90, 119, 0.9), 0 8px 20px rgba(0,0,0,0.4);
    }

    .product-card-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        background-color: #f0f2f5;
    }

    .product-card-content {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: var(--text-white);
    }

    .product-card-price-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .product-card-price {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--accent-gold);
        margin: 0.25rem 0;
    }

    .product-card-stock-info {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--border-silver-blue);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }

    .stock-label {
        color: var(--text-muted);
    }

    .stock-value {
        font-weight: 700;
        font-size: 1.1rem;
        padding: 4px 8px;
        border-radius: 6px;
        color: var(--text-white);
    }

    .stock-ok { background-color: #28a745; }
    .stock-low { background-color: #d9534f; }
    .stock-zero { background-color: #6c757d; }

    .product-card-button {
        display: block;
        background-color: var(--sky-blue);
        color: var(--bg-deep-navy);
        text-align: center;
        padding: 0.85rem;
        text-decoration: none;
        font-weight: bold;
        border-radius: 8px;
        margin-top: 1rem;
        transition: background-color 0.2s;
    }

    .product-card-button:hover {
        background-color: #00B7EB;
        color: var(--bg-deep-navy);
    }

    .btn {
        background-color: var(--accent-gold);
        color: var(--bg-deep-navy);
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s;
    }

    .btn:hover {
        background-color: var(--accent-gold-hover);
        color: var(--bg-deep-navy);
    }

footer {
    position: fixed;    /* Stick to bottom */
    bottom: 0;
    left: 0;
    width: 100%;
    text-align: center;
    font-size: 0.6rem;
    color: var(--text-silver, #ccc); /* Fallback color if var() isn't defined */
    padding: 8px 0;
    background-color: var(--bg-deep-navy, #0D1B2A);
    border-top: 3px solid var(--border-silver-blue, #415A77);
    box-shadow: 0 -2px 20px rgba(65, 90, 119, 0.8), 0 -1px 10px rgba(65, 90, 119, 0.8), 0 -4px 10px rgba(0,0,0,0.4);
    z-index: 1000;      /* Make sure it stays on top */
}
</style>

<div class="page-header">
    <div>
        <!-- Apply the new class to the h1 tag -->
        <h1 class="page-title">Smartphones In Stock</h1>
    </div>
    <a href="create_master.php" class="btn">Add New Product Model</a>
</div>

<div class="product-grid">
    <?php if (count($master_products) > 0): ?>
        <?php foreach ($master_products as $product): ?>
            <?php
                if ($product['total_stock'] == 0) {
                    $stock_class = 'stock-zero';
                } elseif ($product['total_stock'] < 5) {
                    $stock_class = 'stock-low';
                } else {
                    $stock_class = 'stock-ok';
                }
            ?>
            <div class="product-card">
                <img src="/inventory-system/uploads/products/<?php echo e($product['image_filename'] ?? 'default.png'); ?>" 
                     alt="<?php echo e($product['master_name']); ?>" 
                     class="product-card-image">

                <div class="product-card-content">
                    <h3 class="product-card-title"><?php echo e($product['brand'] . ' ' . $product['master_name']); ?></h3>
                    
                    <p class="product-card-price-label">Starts From</p>
                    <p class="product-card-price">
                        <?php
                            echo ($product['starting_price'] > 0) ? '$' . number_format($product['starting_price'], 2) : 'N/A';
                        ?>
                    </p>

                    <div class="product-card-stock-info">
                        <span class="stock-label"><?php echo e($product['variant_count']); ?> Variants</span>
                        <span class="stock-value <?php echo $stock_class; ?>">
                            <?php echo e($product['total_stock']); ?> in Stock
                        </span>
                    </div>

                    <a href="view.php?id=<?php echo $product['id']; ?>" class="product-card-button">MANAGE VARIANTS</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No product models found. <a href="create_master.php">Add one now!</a></p>
    <?php endif; ?>
</div>

<footer>
    <p>© 2025 Smartphone Inventory System. All Rights Reserved.</p>
</footer>

<?php
//include '../includes/footer.php';
?>
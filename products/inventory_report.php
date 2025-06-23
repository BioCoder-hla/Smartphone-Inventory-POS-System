<?php
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

try {
    // --- DATABASE QUERY ---
    // Fetch all variant data along with master product details
    $sql = "SELECT 
                pm.brand, pm.master_name,
                pv.variant_name, pv.sku, pv.quantity, pv.purchase_price, pv.selling_price
            FROM product_variants pv
            JOIN products_master pm ON pv.master_id = pm.id
            WHERE pv.quantity > 0
            ORDER BY pm.brand, pm.master_name, pv.variant_name";
    
    $stmt = $pdo->query($sql);
    $variants = $stmt->fetchAll();

    // --- CALCULATE SUMMARY STATS ---
    $total_units = 0;
    $total_cost_value = 0;
    $total_retail_value = 0;

    foreach ($variants as $variant) {
        $total_units += $variant['quantity'];
        $total_cost_value += $variant['quantity'] * $variant['purchase_price'];
        $total_retail_value += $variant['quantity'] * $variant['selling_price'];
    }
    $total_unique_variants = count($variants);

} catch (PDOException $e) {
    die("DATABASE ERROR: Could not fetch inventory report. Details: " . $e->getMessage());
}

include '../includes/header.php';
?>

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
        --sky-blue: #87CEEB;
    }

    body {
        background-color: var(--bg-deep-navy);
        color: var(--text-silver);
        font-family: 'Poppins', sans-serif;
        padding-bottom: 40px;
    }

    h1 {
        color: var(--text-white);
    }

    p {
        color: var(--text-muted);
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .card {
        background: var(--panel-dark-blue);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(65, 90, 119, 0.8), 0 0 10px rgba(65, 90, 119, 0.8), 0 4px 10px rgba(0,0,0,0.4);
        border: 3px solid var(--border-silver-blue);
        color: var(--text-white);
    }

    .card h3 {
        margin: 0;
        font-size: 1rem;
        color: var(--text-muted);
    }

    .card .value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--accent-gold);
        margin-top: 0.5rem;
    }

    .btn {
        background-color: var(--accent-gold);
        color: var(--bg-deep-navy);
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn:hover {
        background-color: var(--accent-gold-hover);
        color: var(--bg-deep-navy);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--bg-deep-navy); /* Changed to navy blue background */
        border: 3px solid var(--border-silver-blue);
        box-shadow: 0 0 20px rgba(65, 90, 119, 0.8), 0 0 10px rgba(65, 90, 119, 0.8), 0 4px 10px rgba(0,0,0,0.4);
        border-radius: 8px;
        margin-top: 1.5rem;
        color: var(--text-white);
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid var(--border-silver-blue);
    }

    th {
        background-color: var(--bg-deep-navy);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 1rem;
        color: #b8860b;
        border-top: 2px solid var(--border-silver-blue);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    tr:nth-child(even) {
        background-color: rgba(27, 38, 59, 0.5); /* Faint panel-dark-blue for even rows */
    }

    tr:hover {
        background-color: rgba(135, 206, 235, 0.2); /* Faint sky blue for hover */
        color: var(--bg-deep-navy);
    }

    td:first-child {
        font-weight: 600;
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

<h1>Inventory Valuation Report</h1>
<p>An overview of your current stock and its financial value.</p>

<!-- Summary Cards -->
<div class="summary-cards">
    <div class="card">
        <h3>Total Unique Variants</h3>
        <p class="value"><?php echo number_format($total_unique_variants); ?></p>
    </div>
    <div class="card">
        <h3>Total Units in Stock</h3>
        <p class="value"><?php echo number_format($total_units); ?></p>
    </div>
    <div class="card">
        <h3>Total Inventory Value (Cost)</h3>
        <p class="value">$<?php echo number_format($total_cost_value, 2); ?></p>
    </div>
    <div class="card">
        <h3>Total Inventory Value (Retail)</h3>
        <p class="value">$<?php echo number_format($total_retail_value, 2); ?></p>
    </div>
</div>

<a href="export_csv.php" class="btn">Download Full Report (CSV)</a>

<!-- Detailed Inventory Table -->
<table>
    <thead>
        <tr>
            <th>Brand & Model</th>
            <th>Variant</th>
            <th>SKU</th>
            <th>Qty</th>
            <th>Cost/Unit</th>
            <th>Retail/Unit</th>
            <th>Total Cost</th>
            <th>Total Retail</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($variants) > 0): ?>
            <?php foreach ($variants as $variant): ?>
                <tr>
                    <td><strong><?php echo e($variant['brand'] . ' ' . $variant['master_name']); ?></strong></td>
                    <td><?php echo e($variant['variant_name']); ?></td>
                    <td><?php echo e($variant['sku']); ?></td>
                    <td><?php echo e($variant['quantity']); ?></td>
                    <td>$<?php echo number_format($variant['purchase_price'], 2); ?></td>
                    <td>$<?php echo number_format($variant['selling_price'], 2); ?></td>
                    <td>$<?php echo number_format($variant['quantity'] * $variant['purchase_price'], 2); ?></td>
                    <td>$<?php echo number_format($variant['quantity'] * $variant['selling_price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No inventory in stock.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<footer>
    <p>© 2025 Smartphone Inventory System. All Rights Reserved.</p>
</footer>

<?php
//include '../includes/footer.php';
?>
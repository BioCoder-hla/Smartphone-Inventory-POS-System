<?php
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

try {
    $sql = "SELECT 
                s.id as sale_id, s.sale_date,
                si.quantity_sold, si.price_at_sale,
                pv.purchase_price,
                pm.brand, pm.master_name, pv.variant_name,
                u.full_name as cashier_name
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN product_variants pv ON si.product_variant_id = pv.id
            JOIN products_master pm ON pv.master_id = pm.id
            JOIN users u ON s.user_id = u.id
            ORDER BY s.sale_date DESC";
    $stmt = $pdo->query($sql);
    $sales_data = $stmt->fetchAll();

    $total_revenue = 0;
    $total_cost = 0;
    $total_items_sold = 0;

    foreach ($sales_data as $item) {
        $total_items_sold += $item['quantity_sold'];
        $total_revenue += $item['quantity_sold'] * $item['price_at_sale'];
        $total_cost += $item['quantity_sold'] * $item['purchase_price'];
    }
    $total_profit = $total_revenue - $total_cost;

} catch (PDOException $e) {
    die("DATABASE ERROR: " . $e->getMessage());
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

    .profit {
        color: #28a745;
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
        background: var(--bg-deep-navy);
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
        background-color: rgba(27, 38, 59, 0.5);
    }

    tr:hover {
        background-color: rgba(135, 206, 235, 0.2);
        color: #000;
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

<h1>Sales & Profit Report</h1>
<p>An overview of all historical sales transactions and profitability.</p>

<div class="summary-cards">
    <div class="card">
        <h3>Total Revenue</h3>
        <p class="value">$<?php echo number_format($total_revenue, 2); ?></p>
    </div>
    <div class="card">
        <h3>Total Cost of Goods Sold</h3>
        <p class="value">$<?php echo number_format($total_cost, 2); ?></p>
    </div>
    <div class="card">
        <h3>Total Gross Profit</h3>
        <p class="value profit">$<?php echo number_format($total_profit, 2); ?></p>
    </div>
    <div class="card">
        <h3>Total Items Sold</h3>
        <p class="value"><?php echo number_format($total_items_sold); ?></p>
    </div>
</div>

<a href="export_sales_csv.php" class="btn">Download Sales Report (CSV)</a>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Product Details</th>
            <th>Qty</th>
            <th>Sold At</th>
            <th>Cost</th>
            <th>Profit</th>
            <th>Cashier</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($sales_data) > 0): ?>
            <?php foreach ($sales_data as $item): 
                $line_profit = ($item['price_at_sale'] - $item['purchase_price']) * $item['quantity_sold'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($item['sale_date']))); ?></td>
                    <td><strong><?php echo htmlspecialchars($item['brand'] . ' ' . $item['master_name']); ?></strong><br><small><?php echo htmlspecialchars($item['variant_name']); ?></small></td>
                    <td><?php echo htmlspecialchars($item['quantity_sold']); ?></td>
                    <td>$<?php echo number_format($item['price_at_sale'], 2); ?></td>
                    <td>$<?php echo number_format($item['purchase_price'], 2); ?></td>
                    <td class="profit">$<?php echo number_format($line_profit, 2); ?></td>
                    <td><?php echo htmlspecialchars($item['cashier_name']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No sales data available.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<footer>
    <p>© 2025 Smartphone Inventory System. All Rights Reserved.</p>
</footer>

<?php
//include '../includes/footer.php';
?>
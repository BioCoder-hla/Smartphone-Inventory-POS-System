<?php
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

try {
    // Fetch all variant data (same query as the report page)
    $sql = "SELECT 
                pm.brand, pm.master_name,
                pv.variant_name, pv.sku, pv.quantity, pv.purchase_price, pv.selling_price
            FROM product_variants pv
            JOIN products_master pm ON pv.master_id = pm.id
            WHERE pv.quantity > 0
            ORDER BY pm.brand, pm.master_name, pv.variant_name";
    
    $stmt = $pdo->query($sql);
    $variants = $stmt->fetchAll();

    // --- GENERATE CSV ---
    $filename = "inventory_report_" . date('Y-m-d') . ".csv";

    // Set headers to force download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add Header Row
    fputcsv($output, [
        'Brand', 'Master Model', 'Variant Details', 'SKU', 'Quantity', 
        'Purchase Price (Cost)', 'Selling Price (Retail)', 
        'Total Cost Value', 'Total Retail Value'
    ]);

    // Add Data Rows
    $total_cost_value = 0;
    $total_retail_value = 0;

    foreach ($variants as $variant) {
        $line_cost_total = $variant['quantity'] * $variant['purchase_price'];
        $line_retail_total = $variant['quantity'] * $variant['selling_price'];

        $total_cost_value += $line_cost_total;
        $total_retail_value += $line_retail_total;

        fputcsv($output, [
            $variant['brand'],
            $variant['master_name'],
            $variant['variant_name'],
            $variant['sku'],
            $variant['quantity'],
            $variant['purchase_price'],
            $variant['selling_price'],
            $line_cost_total,
            $line_retail_total
        ]);
    }

    // Add Summary Rows at the end
    fputcsv($output, []); // Blank line for spacing
    fputcsv($output, ['---', '---', '---', '---', '---', '---', '---', '---', '---']);
    fputcsv($output, ['SUMMARY', '', '', '', '', '', '', 'Total Cost', 'Total Retail']);
    fputcsv($output, ['TOTALS', '', '', '', '', '', '', number_format($total_cost_value, 2), number_format($total_retail_value, 2)]);
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    // If the database query fails, you can redirect back with an error
    $_SESSION['error_message'] = "Could not generate CSV report. DB Error.";
    redirect('/inventory-system/products/inventory_report.php');
    exit();
}
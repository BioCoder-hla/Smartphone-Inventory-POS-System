<?php
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

try {
    $sql = "SELECT s.sale_date, pm.brand, pm.master_name, pv.variant_name, pv.sku, si.quantity_sold, si.price_at_sale, pv.purchase_price, u.full_name as cashier_name
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN product_variants pv ON si.product_variant_id = pv.id
            JOIN products_master pm ON pv.master_id = pm.id
            JOIN users u ON s.user_id = u.id
            ORDER BY s.sale_date DESC";
    $stmt = $pdo->query($sql);
    $sales_data = $stmt->fetchAll();

    $filename = "sales_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Date', 'Brand', 'Master Model', 'Variant', 'SKU', 'Quantity Sold', 'Price per Unit (Sold)', 'Cost per Unit', 'Total Profit', 'Cashier']);

    foreach ($sales_data as $item) {
        $line_profit = ($item['price_at_sale'] - $item['purchase_price']) * $item['quantity_sold'];
        fputcsv($output, [
            date('Y-m-d H:i:s', strtotime($item['sale_date'])),
            $item['brand'],
            $item['master_name'],
            $item['variant_name'],
            $item['sku'],
            $item['quantity_sold'],
            $item['price_at_sale'],
            $item['purchase_price'],
            number_format($line_profit, 2, '.', ''),
            $item['cashier_name']
        ]);
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Could not generate CSV report. DB Error.";
    redirect('/inventory-system/reports/sales_report.php');
    exit();
}
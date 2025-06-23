<?php
// ... inside pos/search_products.php
// The SQL query to find matching products that are in stock
$sql = "SELECT 
            pv.id, pv.sku, pv.variant_name, pv.selling_price, pv.quantity, 
            pm.brand, pm.master_name
        FROM 
            product_variants pv
        JOIN 
            products_master pm ON pv.master_id = pm.id
        WHERE 
            (pm.brand LIKE ? OR pm.master_name LIKE ? OR pv.sku LIKE ? OR pv.variant_name LIKE ?) 
            AND pv.quantity > 0 
            AND pv.is_active = 1 -- ADD THIS LINE
        LIMIT 10";

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../core/functions.php';

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Authentication required.']);
    exit();
}

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $sql = "SELECT 
                pv.id, pv.sku, pv.variant_name, pv.selling_price, pv.quantity, 
                pm.brand, pm.master_name
            FROM product_variants pv
            JOIN products_master pm ON pv.master_id = pm.id
            WHERE 
                (pm.brand LIKE ? OR pm.master_name LIKE ? OR pv.sku LIKE ? OR pv.variant_name LIKE ?) 
                AND pv.quantity > 0
            LIMIT 10";

    $searchTerm = "%{$term}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $products = $stmt->fetchAll();
    
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed.']);
}
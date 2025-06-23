<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../core/functions.php';

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

$cart_items = json_decode(file_get_contents('php://input'), true);

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit();
}

try {
    $pdo->beginTransaction();

    $total_amount = 0;
    foreach ($cart_items as $item) {
        $stmt_check = $pdo->prepare("SELECT quantity, selling_price FROM product_variants WHERE id = ? FOR UPDATE");
        $stmt_check->execute([$item['id']]);
        $product = $stmt_check->fetch();
        
        if (!$product || $product['quantity'] < $item['qty']) {
            throw new Exception("Not enough stock for {$item['name']}. Please refresh and try again.");
        }
        $total_amount += $product['selling_price'] * $item['qty'];
    }

    $sql_sales = "INSERT INTO sales (user_id, total_amount) VALUES (?, ?)";
    $stmt_sales = $pdo->prepare($sql_sales);
    $stmt_sales->execute([$_SESSION['user_id'], $total_amount]);
    $sale_id = $pdo->lastInsertId();

    $sql_sale_items = "INSERT INTO sale_items (sale_id, product_variant_id, quantity_sold, price_at_sale) VALUES (?, ?, ?, ?)";
    $stmt_sale_items = $pdo->prepare($sql_sale_items);
    
    $sql_update_stock = "UPDATE product_variants SET quantity = quantity - ? WHERE id = ?";
    $stmt_update_stock = $pdo->prepare($sql_update_stock);

    foreach ($cart_items as $item) {
        $stmt_sale_items->execute([$sale_id, $item['id'], $item['qty'], $item['price']]);
        $stmt_update_stock->execute([$item['qty'], $item['id']]);
    }
    
    $pdo->commit();

    echo json_encode(['success' => true, 'sale_id' => $sale_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
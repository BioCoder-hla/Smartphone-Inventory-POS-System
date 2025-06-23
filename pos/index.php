<?php
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

// The PHP logic to fetch frequently sold products remains the same.
try {
    $sql_frequent = "
        SELECT 
            pv.id, pv.sku, pv.variant_name, pv.selling_price, pv.quantity,
            pm.brand, pm.master_name,
            SUM(si.quantity_sold) as total_sold
        FROM sale_items si
        JOIN product_variants pv ON si.product_variant_id = pv.id
        JOIN products_master pm ON pv.master_id = pm.id
        WHERE pv.quantity > 0
        GROUP BY si.product_variant_id
        ORDER BY total_sold DESC
        LIMIT 8;
    ";
    $stmt_frequent = $pdo->query($sql_frequent);
    $frequent_products = $stmt_frequent->fetchAll();
} catch (PDOException $e) {
    $frequent_products = [];
}

include '../includes/header.php';
?>

<style>
    /* 1. Font Import & Global Styles */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --bg-deep-navy: #0D1B2A;
        --panel-dark-blue: #152238;
        --border-silver-blue: #4C6C8D;
        --accent-gold: #D4A017;
        --accent-gold-hover: #E6B800;
        --text-white: #F5F6F5;
        --text-silver: #C9CED6;
        --text-muted: #8FA3C1;
        --sky-blue: #6DBCD3;
    }
    
    body {
        background-color: var(--bg-deep-navy);
        color: var(--text-silver);
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        padding-bottom: 40px; /* Space for footer */
    }

    main.container {
        max-width: 1600px;
    }
    h1, h3 {
        color: var(--text-white);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* 2. Layout & Panels with Glowing Edges */
    .pos-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; }
    .pos-main, .pos-sidebar {
        background-color: var(--panel-dark-blue);
        padding: 2rem;
        border-radius: 16px;
        border: 3px solid var(--border-silver-blue);
        box-shadow: 0 0 20px rgba(76, 108, 141, 0.6), 0 0 8px rgba(76, 108, 141, 0.6), 0 6px 12px rgba(0,0,0,0.5);
    }
    .pos-sidebar { position: sticky; top: 20px; }

    /* 3. Search Bar & Results */
    #product-search {
        font-size: 1.1rem;
        padding: 12px 15px;
        background-color: rgba(109, 188, 211, 0.4);
        border: 3px solid var(--border-silver-blue);
        color: var(--text-white);
        border-radius: 8px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 0 12px rgba(109, 188, 211, 0.7);
    }
    #product-search:focus {
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.3), 0 0 18px rgba(109, 188, 211, 0.8);
    }
    #search-results {
        min-height: 50px;
        max-height: 350px;
        overflow-y: auto;
        border: 3px solid var(--border-silver-blue);
        margin-top: 1rem;
        border-radius: 8px;
        background-color: rgba(109, 188, 211, 0.3);
        box-shadow: 0 0 12px rgba(109, 188, 211, 0.7);
        padding: 5px 0;
    }
    .result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-silver-blue);
        transition: background-color 0.3s ease, color 0.3s ease;
        color: var(--text-white);
        font-size: 1.2rem;
        cursor: pointer;
    }
    .result-item:hover {
        background-color: rgba(109, 188, 211, 0.2);
        color: var(--accent-gold);
    }
    .result-item small {
        color: var(--text-silver);
        font-size: 0.85rem;
    }
    .spinner { border-top: 4px solid var(--accent-gold); }

    /* 4. Quick Add & Buttons */
    .pos-quick-add { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
    .product-chip, .add-to-cart-btn {
        background: linear-gradient(180deg, var(--accent-gold-hover) 0%, var(--accent-gold) 100%);
        color: var(--bg-deep-navy);
        border: 3px solid var(--border-silver-blue);
        padding: 10px;
        border-radius: 8px;
        text-align: left;
        cursor: pointer;
        font-weight: 600;
        box-shadow: 0 3px 6px rgba(0,0,0,0.3);
        transition: all 0.3s ease-in-out;
    }
    .product-chip:hover, .add-to-cart-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.4), 0 0 15px rgba(212, 160, 23, 0.4);
    }
    .product-chip small { color: var(--bg-deep-navy); opacity: 0.9; }
    .add-to-cart-btn { padding: 5px 12px; font-size: 0.9rem; }

    /* 5. Cart & Finalize Button */
    #cart-table {
        color: var(--text-silver);
        background-color: transparent;
        border-collapse: separate;
        border-spacing: 0 5px;
    }
    #cart-table th {
        border-bottom: 2px solid var(--border-silver-blue);
        color: var(--text-white);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
        padding-bottom: 10px;
    }
    #cart-table td {
        border: none;
        vertical-align: middle;
        padding: 12px 8px;
    }
    #cart-items tr {
        background-color: rgba(76, 108, 141, 0.2);
        border-radius: 8px;
    }
    #cart-items td:first-child {
        font-weight: 600;
        color: var(--text-white);
    }
    #cart-items small {
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 400;
    }
    .quantity-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .quantity-btn {
        padding: 0.25rem 0.75rem;
        background-color: var(--bg-deep-navy);
        border: 2px solid var(--border-silver-blue);
        border-radius: 6px;
        color: var(--text-white);
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .quantity-btn:hover {
        background-color: var(--border-silver-blue);
        transform: translateY(-1px);
    }
    .quantity-input {
        width: 60px;
        padding: 0.25rem;
        background-color: var(--bg-deep-navy);
        border: 2px solid var(--border-silver-blue);
        border-radius: 6px;
        color: var(--text-white);
        font-size: 1rem;
        text-align: center;
    }
    .quantity-input:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 8px rgba(212, 160, 23, 0.4);
    }
    .remove-item-btn { color: var(--accent-gold); font-size: 1.2rem; font-weight: bold; text-decoration: none; }
    
    .cart-total {
        text-align: right;
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-white);
        margin-top: 20px;
    }
    .cart-total span { color: var(--accent-gold); }
    
    #finalize-sale-btn {
        width: 100%;
        padding: 15px;
        margin-top: 20px;
        font-size: 1.2rem;
        font-weight: bold;
        background: linear-gradient(180deg, var(--accent-gold-hover) 0%, var(--accent-gold) 100%);
        color: var(--bg-deep-navy);
        border: 3px solid var(--border-silver-blue);
        box-shadow: 0 3px 6px rgba(0,0,0,0.3), 0 0 15px rgba(76, 108, 141, 0.6);
        transition: all 0.3s ease-in-out;
    }
    #finalize-sale-btn:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.4), 0 0 18px rgba(212, 160, 23, 0.5);
    }
    #finalize-sale-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: 0 3px 6px rgba(0,0,0,0.3); }

    /* 6. Popup Styling for SweetAlert2 */
    .swal2-popup {
        font-family: 'Poppins', sans-serif;
        background: var(--panel-dark-blue) !important;
        color: var(--text-silver) !important;
        border-radius: 12px !important;
        box-shadow: 0 0 20px rgba(76, 108, 141, 0.6) !important;
    }
    .swal2-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        color: var(--text-white) !important;
    }
    .swal2-content {
        font-size: 1rem !important;
        color: var(--text-silver) !important;
    }
    .swal2-confirm, .swal2-cancel {
        background: linear-gradient(180deg, var(--accent-gold-hover) 0%, var(--accent-gold) 100%) !important;
        color: var(--bg-deep-navy) !important;
        border: none !important;
        padding: 10px 20px !important;
        border-radius: 8px !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
    }
    .swal2-confirm:hover, .swal2-cancel:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3), 0 0 15px rgba(212, 160, 23, 0.4) !important;
    }
    .swal2-html-container {
        color: var(--text-silver) !important;
    }

    #product-search::placeholder {
        color: white;
        opacity: 1; /* Ensures the color is fully opaque */
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

<div class="pos-grid">
    <div class="pos-main">
        <h3>Product Selection</h3>
        <div class="search-wrapper">
            <input type="search" id="product-search" class="form-control" placeholder="Search products..." style="color: white;">
        </div>
        <div id="search-results">
            <div class="spinner" style="display: none; border: 4px solid #f3f3f3; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 20px auto;"></div>
            <p style="text-align:center; color: var(--text-muted); padding: 20px;">Search results will appear here</p>
        </div>

        <h3 style="margin-top: 2rem;">Quick Add</h3>
        <div class="pos-quick-add">
            <?php foreach ($frequent_products as $product): ?>
                <button class="product-chip" data-product='<?php echo htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8'); ?>'>
                    <strong><?php echo htmlspecialchars($product['brand'] . ' ' . $product['master_name']); ?></strong>
                    <small><?php echo htmlspecialchars($product['variant_name']); ?></small>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="pos-sidebar">
        <h3>Current Sale</h3>
        <table class="table" id="cart-table">
            <thead>
                <tr>
                    <th style="color: #FFD700;">Product</th>
                    <th style="color: #FFD700;" width="90px">Qty</th>
                    <th style="color: #FFD700;">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cart-items">
                <!-- Cart items added via JavaScript -->
            </tbody>
        </table>
        <div class="cart-total">
            Total: <span id="cart-total-amount">$0.00</span>
        </div>
        <button id="finalize-sale-btn" class="btn" disabled>Finalize Sale</button>
    </div>
</div>

<footer>
    <p>© 2025 Smartphone Inventory System. All Rights Reserved.</p>
</footer>

<!-- Include SweetAlert2 and Animate.css dependencies with fallback -->
<script>
    // Fallback for SweetAlert2
    if (!window.Swal) {
        console.error('SweetAlert2 not loaded. Attempting to load from CDN...');
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        script.onload = () => console.log('SweetAlert2 loaded successfully');
        script.onerror = () => console.error('Failed to load SweetAlert2');
        document.head.appendChild(script);
    } else {
        console.log('SweetAlert2 already loaded');
    }
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" onload="console.log('Animate.css loaded')" onerror="console.error('Failed to load Animate.css')">
<script src="../js/main.js" defer></script>

<script>
    // Quantity management function
    function updateQuantity(rowId, action) {
        const row = document.getElementById(`cart-row-${rowId}`);
        const qtyInput = row.querySelector('.quantity-input');
        let qty = parseInt(qtyInput.value) || 1; // Default to 1 if invalid
        if (action === 'increment') {
            qty += 1;
        } else if (action === 'decrement' && qty > 1) {
            qty -= 1;
        }
        qtyInput.value = qty;
        updateRowTotal(rowId, qty);
    }

    // Update row total
    function updateRowTotal(rowId, qty) {
        const row = document.getElementById(`cart-row-${rowId}`);
        const price = parseFloat(row.dataset.price);
        const totalCell = row.querySelector('td:nth-child(3)');
        const newTotal = (price * qty).toFixed(2);
        totalCell.textContent = `$${newTotal}`;
        updateCartTotal();
    }

    // Update overall cart total
    function updateCartTotal() {
        const totals = document.querySelectorAll('#cart-items td:nth-child(3)');
        let grandTotal = 0;
        totals.forEach(total => {
            grandTotal += parseFloat(total.textContent.replace('$', '')) || 0;
        });
        document.getElementById('cart-total-amount').textContent = `$${grandTotal.toFixed(2)}`;
    }

    // Assume main.js handles adding items to cart; enhance it here
    document.addEventListener('DOMContentLoaded', () => {
        // Example: Modify how items are added (this should sync with main.js)
        document.querySelectorAll('.product-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const product = JSON.parse(chip.dataset.product);
                const cartItems = document.getElementById('cart-items');
                const rowId = `cart-row-${Date.now()}`; // Unique ID for each row
                cartItems.innerHTML += `
                    <tr id="${rowId}" data-price="${product.selling_price}">
                        <td>${product.brand} ${product.master_name} <small>${product.variant_name}</small></td>
                        <td>
                            <div class="quantity-container">
                                <button type="button" class="quantity-btn" onclick="updateQuantity('${rowId.replace('cart-row-', '')}', 'decrement')">−</button>
                                <input type="number" class="quantity-input" value="1" min="1" style="width: 50px;">
                                <button type="button" class="quantity-btn" onclick="updateQuantity('${rowId.replace('cart-row-', '')}', 'increment')">+</button>
                            </div>
                        </td>
                        <td>$${product.selling_price.toFixed(2)}</td>
                        <td><a href="#" class="remove-item-btn">×</a></td>
                    </tr>
                `;
                updateRowTotal(rowId.replace('cart-row-', ''), 1);
                document.getElementById('finalize-sale-btn').disabled = false;
            });
        });

        // Remove item functionality
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-item-btn')) {
                e.target.closest('tr').remove();
                updateCartTotal();
                if (document.getElementById('cart-items').children.length === 0) {
                    document.getElementById('finalize-sale-btn').disabled = true;
                }
            }
        });
    });
</script>
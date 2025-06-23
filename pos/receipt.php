<?php
session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

$sale_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($sale_id === 0) die("Invalid Sale ID.");

// Fetch sale details
$stmt_sale = $pdo->prepare("SELECT s.*, u.full_name as cashier_name FROM sales s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmt_sale->execute([$sale_id]);
$sale = $stmt_sale->fetch();
if (!$sale) die("Sale not found.");

// Fetch items sold
$sql_items = "SELECT si.*, pv.variant_name, pm.brand, pm.master_name
              FROM sale_items si
              JOIN product_variants pv ON si.product_variant_id = pv.id
              JOIN products_master pm ON pv.master_id = pm.id
              WHERE si.sale_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$sale_id]);
$items_sold = $stmt_items->fetchAll();

// We don't include a full header/footer on a receipt page to keep it clean.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Receipt #<?php echo htmlspecialchars($sale['id']); ?></title>
    <!-- ================================================================= -->
    <!-- ============= IMPROVED RECEIPT STYLES (NAVY THEME) ============= -->
    <!-- ================================================================= -->
    <style>
        /* Import modern fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Source+Code+Pro:wght@500&display=swap');

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

        body {
            background-color: var(--bg-lighter-navy);
            font-family: 'Inter', sans-serif;
            color: var(--text-off-white);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 2rem 0;
        }

        .receipt-container {
            width: 400px; /* Standard thermal receipt width */
            background-color: var(--bg-deep-navy);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid var(--border-mid-navy);
            padding: 2.5rem;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed var(--border-mid-navy);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .receipt-header h1 {
            color: var(--text-bright-white);
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0 0 0.25rem 0;
            letter-spacing: 1px;
        }

        .receipt-header p {
            color: var(--text-light-gray);
            margin: 0;
        }

        .sale-details {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.5rem 1.5rem;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .sale-details .label { color: var(--text-light-gray); }
        .sale-details .value { color: var(--text-bright-white); font-weight: 600; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Source Code Pro', monospace; /* Monospaced font for numbers */
        }

        .items-table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--text-light-gray);
            border-bottom: 1px solid var(--border-mid-navy);
            padding-bottom: 0.5rem;
            text-align: left;
        }
        .items-table .text-right { text-align: right; }

        .items-table tbody td {
            padding: 1rem 0;
            border-bottom: 1px dotted var(--border-mid-navy);
        }
        
        .item-name {
            font-family: 'Inter', sans-serif; /* Switch back for item names */
            color: var(--text-off-white);
            font-weight: 600;
        }
        .item-name small {
            display: block;
            color: var(--text-light-gray);
            font-weight: 400;
            font-size: 0.8em;
        }

        .receipt-summary {
            margin-top: 1.5rem;
            font-family: 'Source Code Pro', monospace;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1rem;
        }
        .summary-row.total {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-bright-white);
            border-top: 2px dashed var(--border-mid-navy);
            margin-top: 0.5rem;
            padding-top: 1rem;
        }
        .summary-row .label { color: var(--text-light-gray); }

        .receipt-footer {
            text-align: center;
            margin-top: 2rem;
            border-top: 2px dashed var(--border-mid-navy);
            padding-top: 1.5rem;
        }

        .qr-code { margin-bottom: 1rem; }
        .qr-code img { 
            width: 80px; height: 80px; 
            background: white; padding: 5px; border-radius: 6px; 
        }

        .receipt-footer p {
            font-size: 0.9rem;
            color: var(--text-light-gray);
            margin: 0;
        }

        /* --- PRINT STYLES --- */
        @media print {
            body { background: #fff; color: #000; display: block; }
            .receipt-container {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
                background: #fff;
                color: #000;
            }
            .no-print { display: none; }
            h1, p, .value, .item-name, .summary-row { color: #000 !important; }
            .label, small { color: #555 !important; }
            .qr-code img { display: none; }
            .receipt-header, .receipt-footer { border-color: #ccc; }
            .items-table tbody td { border-color: #eee; }
        }

        .action-buttons {
            text-align: center;
            margin-top: 2rem;
            width: 400px;
        }

        .btn {
            padding: 12px 25px;
            margin: 0 10px;
            background-color: var(--accent-gold);
            color: #000;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 195, 0, 0.3);
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <header class="receipt-header">
            <h1>Royal</h1>
            <p>Official Sale Receipt</p>
        </header>

        <section class="sale-details">
            <span class="label">Receipt #</span>
            <span class="value"><?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></span>
            <span class="label">Date</span>
            <span class="value"><?php echo date('M d, Y, g:i A', strtotime($sale['sale_date'])); ?></span>
            <span class="label">Cashier</span>
            <span class="value"><?php echo htmlspecialchars($sale['cashier_name']); ?></span>
        </section>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items_sold as $item): ?>
                <tr>
                    <td>
                        <div class="item-name">
                            <?php echo htmlspecialchars($item['brand'] . ' ' . $item['master_name']); ?>
                            <small><?php echo htmlspecialchars($item['variant_name']); ?></small>
                        </div>
                    </td>
                    <td class="text-right"><?php echo htmlspecialchars($item['quantity_sold']); ?></td>
                    <td class="text-right">$<?php echo number_format($item['price_at_sale'] * $item['quantity_sold'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <section class="receipt-summary">
            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span>$<?php echo number_format($sale['total_amount'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span class="label">Taxes</span>
                <span>$0.00</span>
            </div>
            <div class="summary-row total">
                <span class="label">Total Paid</span>
                <span>$<?php echo number_format($sale['total_amount'], 2); ?></span>
            </div>
        </section>

        <footer class="receipt-footer">
            <div class="qr-code">
                <!-- Replace with a dynamic QR code generator URL if needed -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SaleID:<?php echo $sale_id; ?>" alt="QR Code">
            </div>
            <p>Thank you for your business!</p>
            <p>royalsmartphones.com</p>
        </footer>
    </div>

    <div class="action-buttons no-print">
        <button class="btn" onclick="window.print()">Print Receipt</button>
        <a href="index.php" class="btn">New Sale</a>
    </div>

</body>
</html>
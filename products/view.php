<?php
/**
 * products/view.php
 * Displays details for a single master product and lists all its variants.
 * This is the central hub for managing a specific product line.
 */

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

// Get the master product ID from the URL.
$master_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($master_id === 0) {
    $_SESSION['error_message'] = "Invalid Product ID provided.";
    redirect('/inventory-system/products/index.php');
    exit();
}

try {
    // Fetch Master Product Details
    $stmt_master = $pdo->prepare("SELECT * FROM products_master WHERE id = ?");
    $stmt_master->execute([$master_id]);
    $master_product = $stmt_master->fetch();

    if (!$master_product) {
        $_SESSION['error_message'] = "Product model not found.";
        redirect('/inventory-system/products/index.php');
        exit();
    }

    // Fetch All Variants for this Master Product
    $stmt_variants = $pdo->prepare("SELECT * FROM product_variants WHERE master_id = ? ORDER BY storage_gb DESC, ram DESC");
    $stmt_variants->execute([$master_id]);
    $variants = $stmt_variants->fetchAll();

} catch (PDOException $e) {
    die("DATABASE ERROR: Could not retrieve product data. Details: " . $e->getMessage());
}

include '../includes/header.php';
?>

<!-- ================================================================= -->
<!-- ============= NAVY BLUE & SKY BLUE THEME CSS ==================== -->
<!-- ================================================================= -->
<style>
    :root {
        /* Define the new color palette */
        --bg-deep-navy: #0D1B2A;       /* Main panel background */
        --bg-lighter-navy: #1B263B;    /* Hover color and lighter elements */
        --border-mid-navy: #415A77;    /* Subtle borders */
        --glow-sky-blue: #00BFFF;      /* The glowing border and active status */
        
        --accent-gold: #FFC300;
        --accent-gold-hover: #FFD60A;
        --danger-red: #D00000;
        
        --text-bright-white: #FFFFFF;
        --text-off-white: #E0E1DD;
        --text-light-gray: #A9A9A9;
    }

    /* Main page container styling */
    .product-details-container {
        background-color: var(--bg-deep-navy);
        border-radius: 12px;
        padding: 2.5rem;
        margin: 2rem auto;
        max-width: 1200px;
        /* Glowing border effect */
        border: 1px solid var(--border-mid-navy);
        box-shadow: 0 0 20px rgba(0, 191, 255, 0.2);
    }

    .product-header h2 {
        color: var(--text-bright-white);
        font-size: 2.2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Action Buttons styling remains largely the same */
    .action-buttons {
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
    }
    .btn {
        padding: 10px 22px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        text-transform: uppercase;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .btn:hover { transform: translateY(-2px); }
    .btn-gold { background-color: var(--accent-gold); color: #000; }
    .btn-gold:hover { background-color: var(--accent-gold-hover); box-shadow: 0 4px 15px rgba(255, 195, 0, 0.2); }
    .btn-danger { background-color: var(--danger-red); color: var(--text-bright-white); }
    .btn-danger:hover { box-shadow: 0 4px 15px rgba(208, 0, 0, 0.2); }

    /* Variants List Header Styling */
    .variants-header {
        background-color: #00000030; /* Semi-transparent black */
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border: 2px solid var(--border-mid-navy);
    }
    .variants-header h3 {
        color: var(--text-off-white);
        margin: 0;
        font-size: 1.2rem;
        font-weight: 500;
    }
    
    /* --- NAVY BLUE TABLE STYLING --- */
    .variants-table-wrapper {
        border-radius: 10px;
        overflow: hidden; /* This is key to making border-radius work on the wrapper */
        /* Glowing border for the whole table */
        border: 3px solid var(--border-mid-navy);
        box-shadow: 0 0 15px rgba(0, 191, 255, 0.15);
    }
    
    .variants-table {
        width: 100%;
        border-collapse: collapse;
        background-color: var(--bg-deep-navy); /* Base color for the table */
    }

    .variants-table thead {
        background-color: rgba(0, 0, 0, 0.2); /* Darker header background */
    }
    
    .variants-table th {
        color: var(--text-light-gray);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.8px;
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 2px solid var(--border-mid-navy);
    }
    
    /* Table Body & Rows */
    .variants-table tbody tr {
        border-bottom: 1px solid var(--border-mid-navy);
        transition: all 0.2s ease-in-out;
    }

    .variants-table tbody tr:last-child {
        border-bottom: none;
    }

    .variants-table tbody tr:hover {
        background-color:rgb(47, 86, 131); /* Lighter navy on hover */
        /* Add a subtle glow to the row on hover */
        box-shadow: 0 0 15px rgba(0, 191, 255, 0.25);
        transform: scale(1.01);
        z-index: 10;
        position: relative;
    }

    .variants-table td {
        padding: 1.25rem 1.5rem;
        color: var(--text-off-white);
        vertical-align: middle;
    }
    
    .variant-name-sku strong {
        color: var(--text-bright-white);
        font-weight: 500;
    }
    .variant-name-sku small {
        color: var(--text-light-gray);
    }
    
    .status-active {
        color: var(--glow-sky-blue); /* Use sky blue for active status */
        font-weight: 500;
        text-shadow: 0 0 5px rgba(0, 191, 255, 0.5);
    }
    
    .actions-cell {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1rem;
    }
    
    .btn-link-deactivate {
        background: none; border: none; cursor: pointer; color: var(--text-light-gray);
        font-weight: 500; transition: color 0.2s ease;
    }
    .btn-link-deactivate:hover { color: var(--danger-red); }

    a.back-link {
        display: inline-block;
        margin-top: 2rem;
        color: var(--text-light-gray);
        text-decoration: none;
    }
    a.back-link:hover { color: var(--text-bright-white); }
</style>


<div class="product-details-container">
    <div class="product-header">
        <h2><?php echo htmlspecialchars($master_product['brand'] . ' ' . $master_product['master_name']); ?></h2>
    </div>

    <div class="action-buttons">
        <a href="edit_master.php?id=<?php echo $master_id; ?>" class="btn btn-gold">Edit Model Details</a>
        <a href="create_variant.php?master_id=<?php echo $master_id; ?>" class="btn btn-gold">Add New Variant</a>
        
        <form action="delete_master.php" method="post" id="delete-master-form" style="margin-left: auto;">
            <input type="hidden" name="master_id" value="<?php echo $master_id; ?>">
            <button type="button" class="btn btn-danger" id="delete-master-btn">Delete Entire Model</button>
        </form>
    </div>

    <div class="variants-header">
        <h3>Product Variants List</h3>
    </div>

    <div class="variants-table-wrapper">
        <table class="variants-table">
            <thead>
                <tr>
                    <th style="color: #B8860B; font-size: 15px; font-weight: bold;">Variant Name / SKU</th>
                    <th style="color: #B8860B; font-size: 15px; font-weight: bold;">Specs (Storage/RAM)</th>
                    <th style="color: #B8860B; font-size: 15px; font-weight: bold;">Color</th>
                    <th style="color: #B8860B; font-size: 15px; font-weight: bold;">Selling Price</th>
                    <th style="color: #B8860B; font-size: 15px; font-weight: bold;">Stock</th>
                    <th style="color: #B8860B; font-size: 15px; font-weight: bold;">Status</th>
                    <th style="text-align: right; color: #B8860B; font-size: 15px; font-weight: bold;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($variants) > 0): ?>
                    <?php foreach ($variants as $variant): ?>
                        <tr>
                            <td class="variant-name-sku">
                                <strong><?php echo htmlspecialchars($variant['variant_name']); ?></strong><br>
                                <small>SKU: <?php echo htmlspecialchars($variant['sku']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($variant['storage_gb']); ?> GB / <?php echo htmlspecialchars($variant['ram']); ?> GB</td>
                            <td><?php echo htmlspecialchars($variant['color']); ?></td>
                            <td>$<?php echo number_format($variant['selling_price'], 2); ?></td>
                            <td><strong><?php echo htmlspecialchars($variant['quantity']); ?></strong></td>
                            <td>
                                <span class="<?php echo $variant['is_active'] ? 'status-active' : 'status-inactive'; // Assumes you have a class for inactive too ?>">
                                    <?php echo $variant['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="edit_variant.php?id=<?php echo $variant['id']; ?>" class="btn btn-gold" style="padding: 6px 14px; font-size: 0.8rem;">Edit</a>
                                <form action="delete_variant.php" method="post" class="deactivate-form">
                                    <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                                    <input type="hidden" name="master_id" value="<?php echo $master_id; ?>">
                                    <button type="button" class="btn-link-deactivate">Deactivate</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 2rem; color: var(--text-light-gray);">No variants found for this model.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="index.php" class="back-link">« Back to All Products</a>
</div>

<?php
include '../includes/footer.php';
?>
<!-- SweetAlert2 for beautiful confirmation dialogs -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const swalTheme = {
        background: 'var(--bg-deep-navy)',
        color: 'var(--text-off-white)',
        confirmButtonColor: 'var(--accent-gold)',
        cancelButtonColor: '#555',
    };

    const deleteMasterBtn = document.getElementById('delete-master-btn');
    if(deleteMasterBtn) {
        deleteMasterBtn.addEventListener('click', function(e) {
            Swal.fire({
                ...swalTheme,
                title: 'Are you absolutely sure?',
                html: "This will permanently delete the <strong><?php echo addslashes(htmlspecialchars($master_product['master_name'])); ?></strong> model and <strong>ALL</strong> of its variants. <br><br><strong style='color: var(--danger-red);'>This action cannot be undone.</strong>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                confirmButtonColor: 'var(--danger-red)',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-master-form').submit();
                }
            });
        });
    }

    const deactivateForms = document.querySelectorAll('.deactivate-form');
    deactivateForms.forEach(form => {
        form.querySelector('button').addEventListener('click', function(e) {
            const variantName = form.closest('tr').querySelector('.variant-name-sku strong').textContent;
            Swal.fire({
                ...swalTheme,
                title: 'Deactivate Variant?',
                html: `Are you sure you want to deactivate <strong>${variantName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, deactivate',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
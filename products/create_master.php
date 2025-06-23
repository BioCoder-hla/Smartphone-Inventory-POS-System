<?php
/**
 * products/create_master.php
 * Form to add a new master product model with optional image upload and resizing.
 */

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

$errors = [];
$image_filename = null; // Initialize image filename as null

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve text data from the form
    $brand = trim($_POST['brand'] ?? '');
    $master_name = trim($_POST['master_name'] ?? '');
    $description = trim($_POST['description'] ?? null);
    $release_year = !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null;

    // --- Basic Validation ---
    if (empty($brand)) $errors[] = 'Brand is required.';
    if (empty($master_name)) $errors[] = 'Master Name is required.';
    
    // --- Image Upload & Resize Logic ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_filename = resizeAndSaveImage($_FILES['image'], "../uploads/products/");
        if (!$image_filename) {
            $errors[] = 'Failed to process image. Please ensure it is a valid image type (JPG, PNG, GIF, WEBP).';
        }
    }
    // --- End Image Upload Logic ---

    // Proceed only if there are no errors
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products_master (brand, master_name, description, release_year, image_filename) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$brand, $master_name, $description, $release_year, $image_filename]);
            
            $new_master_id = $pdo->lastInsertId();

            $_SESSION['success_message'] = "New model '{$brand} {$master_name}' created successfully! You can now add its variants.";
            redirect("/inventory-system/products/view.php?id={$new_master_id}");
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "This product model (Brand and Master Name combination) already exists.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<!-- ================================================================= -->
<!-- ============= FORM STYLES (NAVY THEME, THICK BORDERS) =========== -->
<!-- ================================================================= -->
<style>
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

    .form-page-container {
        background-color: var(--bg-deep-navy);
        border-radius: 12px;
        padding: 2.5rem;
        margin: 2rem auto;
        max-width: 900px;
        border: 2px solid var(--border-mid-navy);
        box-shadow: 0 0 20px rgba(0, 191, 255, 0.2);
    }

    .form-header h2 {
        color: var(--text-bright-white);
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }
    .form-header .sub-heading {
        color: var(--text-off-white);
        font-size: 1rem;
        margin: 0 0 2rem 0;
    }

    .alert-danger {
        background-color: rgba(208, 0, 0, 0.1);
        border: 1px solid var(--danger-red);
        color: var(--text-off-white);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    .alert-danger ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr; /* Default to single column */
        gap: 1.5rem;
    }
    
    /* Use two columns on wider screens */
    @media (min-width: 768px) {
        .form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        /* Make description and image span both columns */
        .form-group-full-width {
            grid-column: 1 / -1;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        color: var(--text-light-gray);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea {
        background-color: var(--bg-lighter-navy);
        border: 2px solid var(--border-mid-navy);
        color: var(--text-off-white);
        padding: 0.8rem 1rem;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.2s ease-in-out;
    }
    
    /* Specific styling for file input to make it look consistent */
    .form-group input[type="file"] {
        padding: 0;
    }
    .form-group input[type="file"]::file-selector-button {
        background-color: var(--border-mid-navy);
        color: var(--text-off-white);
        border: none;
        padding: 0.8rem 1rem;
        border-radius: 6px 0 0 6px;
        margin-right: 1rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
     .form-group input[type="file"]::file-selector-button:hover {
        background-color: var(--glow-sky-blue);
        color: var(--bg-deep-navy);
     }
    .form-group small {
        color: var(--text-light-gray);
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }

    .form-group input:focus, .form-group textarea:focus {
        outline: none;
        border-color: var(--glow-sky-blue);
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.3);
    }
    
    textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-actions {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--border-mid-navy);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .btn {
        padding: 10px 22px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .btn:hover { transform: translateY(-2px); }
    .btn-gold { background-color: var(--accent-gold); color: #000; }
    .btn-gold:hover { background-color: var(--accent-gold-hover); box-shadow: 0 4px 15px rgba(255, 195, 0, 0.2); }
    
</style>


<div class="form-page-container">
    <div class="form-header">
        <h2>Create New Product Model</h2>
        <p class="sub-heading">Add a new product line. Specific variants (color, storage) can be added on the next page.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <!-- The form MUST have enctype="multipart/form-data" for file uploads to work -->
    <form action="create_master.php" method="post" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="<?php echo e($_POST['brand'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="master_name">Master Name (e.g., Pixel 9a, iPhone 16 Pro)</label>
                <input type="text" id="master_name" name="master_name" value="<?php echo e($_POST['master_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group form-group-full-width">
                <label for="image">Product Image (Optional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif, image/webp">
                <small>Recommended: Square image. Will be auto-resized to 500x500 pixels.</small>
            </div>
            <div class="form-group">
                <label for="release_year">Release Year (Optional)</label>
                <input type="number" id="release_year" name="release_year" value="<?php echo e($_POST['release_year'] ?? ''); ?>" placeholder="<?php echo date('Y'); ?>">
            </div>
            <div class="form-group form-group-full-width">
                <label for="description">Description (Optional)</label>
                <textarea id="description" name="description" rows="4"><?php echo e($_POST['description'] ?? ''); ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-gold">Create Model & Add Variants</button>
        </div>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
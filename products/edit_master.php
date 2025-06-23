<?php
/**
 * products/edit_master.php - Premium Version with Glowing File Input
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/database.php';
require_once '../core/functions.php';
require_login();

$master_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($master_id === 0) {
    redirect_with_error('Invalid product ID provided.', '/products/index.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products_master WHERE id = ?");
    $stmt->execute([$master_id]);
    $product = $stmt->fetch();
    if (!$product) {
        redirect_with_error('The requested product model was not found.', '/products/index.php');
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$errors = [];
$formData = $product;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'brand' => trim($_POST['brand'] ?? ''),
        'master_name' => trim($_POST['master_name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'release_year' => !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null,
        'image_filename' => $product['image_filename'],
    ];

    // Process image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageFile = $_FILES['image'];
        $newFilename = resizeAndSaveImage($imageFile, "../uploads/products/");
        if ($newFilename) {
            $oldImagePath = "../uploads/products/" . $product['image_filename'];
            if ($product['image_filename'] && file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
            $formData['image_filename'] = $newFilename;
        } else {
            $errors[] = 'Error: Invalid image file.';
        }
    }

    if (empty($formData['brand'])) $errors[] = "Brand is required.";
    if (empty($formData['master_name'])) $errors[] = "Master product name is required.";

    if (empty($errors)) {
        try {
            $sql = "UPDATE products_master SET brand=?, master_name=?, description=?, release_year=?, image_filename=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $formData['brand'],
                $formData['master_name'],
                $formData['description'],
                $formData['release_year'],
                $formData['image_filename'],
                $master_id,
            ]);
            $_SESSION['success_message'] = "Product model updated successfully!";
            redirect("/inventory-system/products/view.php?id={$master_id}");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error updating product model: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<style>
    body {
        background: linear-gradient(135deg, #0D1B2A 0%, #1B263B 100%);
        color: #E0E1DD;
        font-family: Arial, sans-serif;
    }
    .page-title {
        color: #FFD60A;
        font-size: 1.8rem;
        text-align: center;
        margin: 2rem 0;
    }
    .card {
        background-color: #1B263B;
        border: 2px solid #415A77;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 0 20px rgba(0,191,255,0.2);
        max-width: 800px;
        margin: 0 auto;
    }
    label {
        color: #A9A9A9;
        font-weight: bold;
        margin-bottom: 0.5rem;
        display: block;
    }
    input[type="text"],
    input[type="number"],
    textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        background-color: #0D1B2A;
        border: 2px solid #415A77;
        border-radius: 8px;
        color: #E0E1DD;
        transition: all 0.2s ease;
    }
    input:focus,
    textarea:focus {
        border-color: #00BFFF;
        box-shadow: 0 0 10px rgba(0,191,255,0.3);
        outline: none;
    }
    .img-preview {
        max-height: 120px;
        border-radius: 8px;
        border: 2px solid #415A77;
        margin-top: 0.5rem;
    }
    .error-box {
        padding: 1rem;
        background: rgba(208,0,0,0.1);
        border: 2px solid #D00000;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    .error-box ul {
        padding-left: 1.5rem;
        margin: 0;
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: #FFC300;
        color: #0D1B2A;
    }
    .btn-primary:hover {
        background: #FFD60A;
        box-shadow: 0 6px 15px rgba(255,195,0,0.3);
        transform: translateY(-2px);
    }
    .btn-secondary {
        background: #415A77;
        color: #E0E1DD;
    }
    .btn-secondary:hover {
        background: #567189;
        transform: translateY(-2px);
    }
    .actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    /* Glowing file input style */
    .file-input-wrapper {
        position: relative;
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(180deg, #1B263B 0%, #0D1B2A 100%);
        border: 2px solid #00BFFF;
        border-radius: 10px;
        color: #E0E1DD;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 0 10px rgba(0,191,255,0.4);
        transition: all 0.3s ease;
    }
    .file-input-wrapper:hover {
        border-color: #FFD60A;
        color: #FFD60A;
        box-shadow: 0 0 20px rgba(0,191,255,0.7);
        transform: translateY(-2px);
    }
    .file-input-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .file-input-label::before {
        content: "📁";
        margin-right: 0.5rem;
    }
</style>

<h1 class="page-title">Edit Product Model</h1>
<div class="card">
    <?php if ($errors): ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="edit_master.php?id=<?php echo $master_id; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($formData['brand']); ?>" required>
        </div>

        <div class="form-group">
            <label for="master_name">Master Product Name</label>
            <input type="text" id="master_name" name="master_name" value="<?php echo htmlspecialchars($formData['master_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Current Image</label><br>
            <img class="img-preview" src="/inventory-system/uploads/products/<?php echo htmlspecialchars($formData['image_filename'] ?? 'default.png'); ?>" alt="Product image preview">
        </div>

        <div class="form-group">
            <label for="image">Upload New Image (Optional)</label>
            <div class="file-input-wrapper">
                <span class="file-input-label">Choose file</span>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
            </div>
            <small class="form-text">Leave empty to keep the current image.</small>
        </div>

        <div class="form-group">
            <label for="release_year">Release Year (Optional)</label>
            <input type="number" id="release_year" name="release_year" value="<?php echo htmlspecialchars($formData['release_year']); ?>" min="1990" max="<?php echo date('Y') + 1; ?>" placeholder="e.g. 2024">
        </div>

        <div class="form-group">
            <label for="description">Description (Optional)</label>
            <textarea id="description" name="description" rows="4" placeholder="Product features"><?php echo htmlspecialchars($formData['description']); ?></textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Update Model</button>
            <a href="/inventory-system/products/view.php?id=<?php echo $master_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

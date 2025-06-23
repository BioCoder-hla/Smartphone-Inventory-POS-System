<?php
// auth/register.php
require_once '../config/database.php';
require_once '../core/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // --- Validation ---
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if ($password !== $password_confirm) $errors[] = 'Passwords do not match.';
    if (strlen($password) < 4) $errors[] = 'Password must be at least 8 characters long.';

    // Check if username already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken. Please choose another one.';
        }
    }
    
    // --- If validation passes, create the user ---
    if (empty($errors)) {
        // Hash the password for security
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert the new user into the database
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, full_name, password_hash, role) VALUES (?, ?, ?, ?)"
            );
            // New users default to 'Sales Staff' role
            $stmt->execute([$username, $full_name, $password_hash, 'Sales Staff']);
            
            // Set a success message and redirect to login page
            session_start();
            $_SESSION['success_message'] = 'Registration successful! You can now log in.';
            redirect('login.php');

        } catch (PDOException $e) {
            // In production, log this error instead of showing it
            $errors[] = 'Database error. Could not register user.';
        }
    }
}

include '../includes/header.php';

// Fetch top 3 most sold smartphones
try {
    $sql_top_products = "
        SELECT pm.brand, pm.master_name, pm.image_url, SUM(si.quantity_sold) as total_sold
        FROM sale_items si
        JOIN product_variants pv ON si.product_variant_id = pv.id
        JOIN products_master pm ON pv.master_id = pm.id
        GROUP BY pm.id
        ORDER BY total_sold DESC
        LIMIT 3;
    ";
    $stmt_top = $pdo->query($sql_top_products);
    $top_products = $stmt_top->fetchAll();
} catch (PDOException $e) {
    $top_products = []; // Fallback if query fails
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --bg-deep-navy: #0A141F;
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
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding-bottom: 60px; /* Adjust based on footer height */
    }

    .form-container {
        max-width: 400px;
        margin: 50px auto;
        padding: 2rem;
        background-color: var(--panel-dark-blue);
        border-radius: 16px;
        border: 3px solid var(--border-silver-blue);
        box-shadow: 0 0 20px rgba(76, 108, 141, 0.6), 0 6px 12px rgba(0,0,0,0.5);
        text-align: center;
        flex: 1 0 auto; /* Allow form to grow but not shrink */
    }

    h2 {
        color: var(--text-white);
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .alert { 
        padding: 15px; 
        margin-bottom: 20px; 
        border: 1px solid transparent; 
        border-radius: 4px; 
    }
    .alert ul { 
        margin: 0; 
        padding-left: 20px; 
    }
    .alert-danger { 
        color: #a94442; 
        background-color: rgba(242, 222, 222, 0.8); 
        border-color: #ebccd1; 
    }

    .form-group {
        margin-bottom: 1.5rem;
        text-align: left;
    }

    label {
        display: block;
        color: var(--text-white);
        margin-bottom: 0.5rem;
    }

    input {
        width: 100%;
        padding: 12px;
        background-color: rgba(109, 188, 211, 0.3);
        border: 2px solid var(--border-silver-blue);
        border-radius: 8px;
        color: var(--text-white);
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input:focus {
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.3);
        outline: none;
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(180deg, var(--accent-gold-hover) 0%, var(--accent-gold) 100%);
        color: var(--bg-deep-navy);
        border: 3px solid var(--border-silver-blue);
        border-radius: 8px;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.3), 0 0 15px rgba(212, 160, 23, 0.4);
    }

    .form-switch {
        margin-top: 1rem;
        color: var(--text-muted);
    }

    .form-switch a {
        color: var(--accent-gold);
        text-decoration: none;
    }

    .form-switch a:hover {
        color: var(--accent-gold-hover);
        text-decoration: underline;
    }

    /* Slideshow Styles */
    .slideshow {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 2rem auto 1rem;
        overflow: hidden;
        border: 3px solid var(--border-silver-blue);
        border-radius: 8px;
        background-color: rgba(109, 188, 211, 0.2);
        box-shadow: 0 0 15px rgba(109, 188, 211, 0.6);
    }

    .slide {
        display: none;
        text-align: center;
        padding: 1rem;
        animation: fade 1.5s ease-in-out;
    }

    .slide.active {
        display: block;
    }

    .slide img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    .slide h4 {
        color: var(--text-white);
        font-size: 1.2rem;
        margin: 0.5rem 0;
    }

    .slide p {
        color: var(--text-silver);
        font-size: 0.9rem;
        margin: 0;
    }

    @keyframes fade {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Footer Styles */
    footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: center;
        padding: 10px 0;
        background: linear-gradient(180deg, rgba(10, 20, 31, 0.9) 0%, var(--bg-deep-navy) 100%);
        border-top: 2px solid var(--border-silver-blue);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.6), 0 0 15px rgba(76, 108, 141, 0.4);
        font-family: 'Poppins', sans-serif;
    }

    footer p {
        margin: 0;
        color: var(--text-silver);
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    footer p:hover {
        color: var(--accent-gold);
        text-shadow: 0 0 5px rgba(212, 160, 23, 0.5);
    }
</style>

<div class="form-container">
    <h2>Create an Account</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Slideshow for Top 3 Sold Smartphones -->
    <div class="slideshow">
        <?php foreach ($top_products as $index => $product): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                <img src="<?php echo htmlspecialchars($product['image_url'] ?? '/images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['brand'] . ' ' . $product['master_name']); ?>">
                <h4><?php echo htmlspecialchars($product['brand'] . ' ' . $product['master_name']); ?></h4>
                <p>Sold: <?php echo htmlspecialchars($product['total_sold']); ?> units</p>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="register.php" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn">Register</button>
    </form>
    <p class="form-switch">Already have an account? <a href="login.php">Login here</a></p>
</div>

<footer>
    <p>© <?php echo date('Y'); ?> Smartphone Inventory System. All Rights Reserved.</p>
</footer>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;
    const slideInterval = 5000; // 5 seconds per slide

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Start slideshow
    showSlide(currentSlide);
    setInterval(nextSlide, slideInterval);

    // Optional: Pause on hover (enhances user experience)
    const slideshow = document.querySelector('.slideshow');
    let slideshowInterval;

    slideshow.addEventListener('mouseenter', () => {
        clearInterval(slideshowInterval);
    });

    slideshow.addEventListener('mouseleave', () => {
        slideshowInterval = setInterval(nextSlide, slideInterval);
    });

    slideshowInterval = setInterval(nextSlide, slideInterval);
});
</script>
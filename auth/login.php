<?php
// auth/login.php

require_once '../config/database.php';
require_once '../core/functions.php';
session_start(); // Session must be started at the top for the animation flag to work.

$error_message = '';

// Check if the form has been submitted AND the animation flag is not already set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['login_success_user_info'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // --- MODIFICATION 1: SUCCESSFUL LOGIN ---
            session_regenerate_id(true);

            // Store permanent user data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set a temporary array with info needed for the animation
            $_SESSION['login_success_user_info'] = [
                'full_name' => $user['full_name'], // The user's full name for the "Welcome" message
                'redirect_url' => $_SESSION['redirect_url'] ?? '/inventory-system/index.php'
            ];
            unset($_SESSION['redirect_url']); 

            // Redirect back to this same page, which will now detect the session and show the animation
            header('Location: login.php');
            exit();

        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}

// --- MODIFICATION 2: Check for animation trigger on page load ---
$show_animation = isset($_SESSION['login_success_user_info']);
if ($show_animation) {
    $welcome_user_name = $_SESSION['login_success_user_info']['full_name'];
    $redirect_url_for_js = $_SESSION['login_success_user_info']['redirect_url'];
    // Unset the temporary session variable so the animation doesn't play again on a page refresh
    unset($_SESSION['login_success_user_info']);
}


include '../includes/header.php';

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
    $top_products = [];
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
        padding-bottom: 60px; 
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

    .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
    .alert-danger { color: #a94442; background-color: rgba(242, 222, 222, 0.8); border-color: #ebccd1; }
    .alert-success { color: #3c763d; background-color: rgba(223, 240, 216, 0.8); border-color: #d6e9c6; }
    .form-group { margin-bottom: 1.5rem; text-align: left; }
    label { display: block; color: var(--text-white); margin-bottom: 0.5rem; }
    input { width: 100%; padding: 12px; background-color: rgba(109, 188, 211, 0.3); border: 2px solid var(--border-silver-blue); border-radius: 8px; color: var(--text-white); font-size: 1rem; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
    input:focus { border-color: var(--accent-gold); box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.3); outline: none; }
    .btn { width: 100%; padding: 12px; background: linear-gradient(180deg, var(--accent-gold-hover) 0%, var(--accent-gold) 100%); color: var(--bg-deep-navy); border: 3px solid var(--border-silver-blue); border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.3), 0 0 15px rgba(212, 160, 23, 0.4); }
    .form-switch { margin-top: 1rem; color: var(--text-muted); }
    .form-switch a { color: var(--accent-gold); text-decoration: none; }
    .form-switch a:hover { color: var(--accent-gold-hover); text-decoration: underline; }
    .slideshow { position: relative; width: 100%; max-width: 400px; margin: 2rem auto 1rem; overflow: hidden; border: 3px solid var(--border-silver-blue); border-radius: 8px; background-color: rgba(109, 188, 211, 0.2); box-shadow: 0 0 15px rgba(109, 188, 211, 0.6); }
    .slide { display: none; text-align: center; padding: 1rem; animation: fade 1.5s ease-in-out; }
    .slide.active { display: block; }
    .slide img { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
    .slide h4 { color: var(--text-white); font-size: 1.2rem; margin: 0.5rem 0; }
    .slide p { color: var(--text-silver); font-size: 0.9rem; margin: 0; }
    @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
    footer { position: fixed; bottom: 0; width: 100%; text-align: center; padding: 10px 0; background: linear-gradient(180deg, rgba(10, 20, 31, 0.9) 0%, var(--bg-deep-navy) 100%); border-top: 2px solid var(--border-silver-blue); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.6), 0 0 15px rgba(76, 108, 141, 0.4); font-family: 'Poppins', sans-serif; }
    footer p { margin: 0; color: var(--text-silver); font-size: 0.75rem; font-weight: 500; letter-spacing: 0.5px; transition: color 0.3s ease, text-shadow 0.3s ease; }
    footer p:hover { color: var(--accent-gold); text-shadow: 0 0 5px rgba(212, 160, 23, 0.5); }


    .login-success-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(10, 20, 31, 0.97); /* Dark overlay */
        display: flex; justify-content: center; align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease;
    }
    .login-success-overlay.visible { opacity: 1; visibility: visible; }
    .welcome-message { text-align: center; color: var(--text-white); }
    .welcome-message .welcome-text { font-size: 2rem; font-weight: 300; letter-spacing: 2px; opacity: 0; animation: fadeInDown 0.8s 0.5s ease-out forwards; }
    .welcome-message .user-name { font-size: 3.5rem; font-weight: 700; color: var(--accent-gold); display: block; margin-top: 0.5rem; text-shadow: 0 0 15px rgba(212, 160, 23, 0.5); opacity: 0; animation: fadeInUp 0.8s 0.8s ease-out forwards; }
    .welcome-message .redirect-text { font-size: 1rem; color: var(--text-muted); margin-top: 2rem; opacity: 0; animation: fadeIn 1s 1.5s ease-out forwards; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

</style>

<?php if ($show_animation): ?>
<div id="login-success-overlay" class="login-success-overlay">
    <div class="welcome-message">
        <div class="welcome-text">Welcome Back</div>
        <div class="user-name"><?php echo htmlspecialchars($welcome_user_name); ?></div>
        <div class="redirect-text">Redirecting to your dashboard...</div>
    </div>
</div>
<?php endif; ?>


<div class="form-container">
    <h2>Login to Your Account</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="slideshow">
        <?php foreach ($top_products as $index => $product): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                <img src="<?php echo htmlspecialchars($product['image_url'] ?? '/images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['brand'] . ' ' . $product['master_name']); ?>">
                <h4><?php echo htmlspecialchars($product['brand'] . ' ' . $product['master_name']); ?></h4>
                <p>Sold: <?php echo htmlspecialchars($product['total_sold']); ?> units</p>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="login.php" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <p class="form-switch">Don't have an account? <a href="register.php">Register here</a></p>
</div>

<footer>
    <p>© <?php echo date('Y'); ?> Smartphone Inventory System. All Rights Reserved.</p>
</footer>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.slide');
    if (slides.length > 0) {
        let currentSlide = 0;
        const slideInterval = 5000;
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) { slide.classList.add('active'); }
            });
        }
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        showSlide(currentSlide);
        let slideshowInterval = setInterval(nextSlide, slideInterval);
        const slideshow = document.querySelector('.slideshow');
        slideshow.addEventListener('mouseenter', () => clearInterval(slideshowInterval));
        slideshow.addEventListener('mouseleave', () => slideshowInterval = setInterval(nextSlide, slideInterval));
    }
});
</script>

<?php if ($show_animation): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('login-success-overlay');
    // 1. Make the overlay visible
    setTimeout(() => {
        overlay.classList.add('visible');
    }, 100);

    // 2. Redirect aftera the animation has played
    const redirectUrl = "<?php echo $redirect_url_for_js; ?>";
    setTimeout(() => {
        window.location.href = redirectUrl;
    }, 1500); 
});
</script>
<?php endif; ?>

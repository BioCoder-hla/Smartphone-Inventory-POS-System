<?php
/**
 * includes/header.php - FINAL CREATIVE VERSION WITH MOTTO
 *
 * This single file contains all the PHP logic, HTML structure, and CSS styling
 * to produce the final, animated header as requested.
 *
 * Features:
 * - Dynamic, animated "Welcome to Royal Smartphones" title.
 * - Subtly animated motto: "Where Royalty Meets Technology".
 * - Hides main navigation on auth pages (login/register).
 * - All "Deep Navy & Accent Gold" theme styles are embedded.
 */

// Logic to detect authentication pages
$currentPage = basename($_SERVER['PHP_SELF']);
$isAuthPage = in_array($currentPage, ['login.php', 'register.php']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royal Smartphones</title>
    
    <!-- External scripts can remain -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Embedded Stylesheet for the Premium Theme -->
    <style>
        /* ==========================================================================
           --- EMBEDDED STYLESHEET: ROYAL SMARTPHONES THEME (WITH MOTTO) ---
           ========================================================================== */

        /* --- Google Font Import --- */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        /* --- Color & Theme Variables --- */
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
            --danger-red: #D00000;
        }

        /* --- Base Body & Layout Styles --- */
        body {
            background-color: var(--bg-deep-navy);
            color: var(--text-silver);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- Header & Navigation Bar --- */
        header {
            background: linear-gradient(180deg, var(--panel-dark-blue) 0%, var(--bg-deep-navy) 100%);
            border-bottom: 3px solid var(--border-silver-blue);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.8), 0 0 25px rgba(76, 108, 141, 0.6);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1.2rem 0;
            transition: box-shadow 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            min-height: 60px;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        nav ul li a {
            color: var(--text-white);
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            color: var(--accent-gold);
            text-shadow: 0 0 8px rgba(212, 160, 23, 0.5);
            background-color: rgba(76, 108, 141, 0.25);
            transform: translateY(-2px);
        }

        nav ul li a.active {
            color: var(--accent-gold);
            font-weight: 600;
            background-color: rgba(76, 108, 141, 0.4);
        }
        
        /* --- Logout Link Styling --- */
        nav ul li.logout-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        nav ul li.logout-item span.username {
            color: var(--text-silver);
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* --- Creative Animated Welcome Title & Motto --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes drawLine {
            from { width: 0%; }
            to { width: 50%; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .main-title-creative {
            color: var(--text-white);
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
            /* Reduced bottom margin to make space for the motto */
            margin: 3rem 0 2.5rem 0; 
            letter-spacing: 2px;
            position: relative;
            padding-bottom: 20px;
            overflow: hidden;
        }

        .main-title-creative span {
            display: inline-block;
            opacity: 0;
            animation: fadeInUp 0.8s forwards cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .main-title-creative span:nth-child(1) { animation-delay: 0.1s; }
        .main-title-creative span:nth-child(2) { animation-delay: 0.3s; }
        .main-title-creative span:nth-child(3) { animation-delay: 0.5s; color: var(--accent-gold); }
        .main-title-creative span:nth-child(4) { animation-delay: 0.7s; color: var(--accent-gold); }

        .main-title-creative::after {
            content: '';
            display: block;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            height: 4px;
            background: linear-gradient(90deg, var(--accent-gold), var(--sky-blue));
            border-radius: 2px;
            width: 0;
            animation: drawLine 0.7s 1.5s forwards ease-out;
        }
        
        /* --- NEW: STYLING FOR THE MOTTO --- */
        .title-motto {
            text-align: center;
            font-size: 1.2rem;
            color: var(--text-silver);
            font-weight: 300;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin: -1.5rem 0 4rem 0; /* Use negative top margin to pull it closer to the title */
            
            /* Animation starts after title and underline are finished */
            opacity: 0;
            animation: fadeIn 2s 2.2s forwards ease-in-out;
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            nav { justify-content: center; }
            .main-title-creative { font-size: 2.2rem; margin: 2rem 0 2rem 0; }
            .main-title-creative::after { width: 70%; }
            .title-motto { font-size: 1rem; letter-spacing: 1.5px; margin-top: -1rem; margin-bottom: 3rem;}
        }

    </style>
</head>
<body>
    <header>
        <nav class="container">
            <?php // The logo is intentionally removed. ?>

            <?php if (!$isAuthPage): ?>
                <ul>
                    <li><a href="/inventory-system/products/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['REQUEST_URI'], '/pos/') === false ? 'active' : ''; ?>"><span class="icon">🏠</span>Home</a></li>
                    <li><a href="/inventory-system/products/inventory_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'inventory_report.php' ? 'active' : ''; ?>"><span class="icon">📦</span>Inventory</a></li>
                    <li><a href="/inventory-system/reports/sales_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'sales_report.php' ? 'active' : ''; ?>"><span class="icon">🛒</span>Sales</a></li>
                    <li><a href="/inventory-system/pos/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/pos/') !== false ? 'active' : ''; ?>"><span class="icon">💵</span>POS</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="logout-item">
                            <a href="/inventory-system/auth/logout.php"><span class="icon">🚪</span>Logout</a>
                            <span class="username">(<?php echo htmlspecialchars($_SESSION['username']); ?>)</span>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <!-- Main animated title -->
        <h1 class="main-title-creative">
            <span>Welcome</span>
            <span>to</span>
            <span>Royal</span>
            <span>Smartphones</span>
        </h1>
        
        <!-- NEW: Animated motto below the title -->
        <p class="title-motto">Where Royalty Meets Technology</p>

        <?php // The main content of each page will be rendered after this title and motto ?>
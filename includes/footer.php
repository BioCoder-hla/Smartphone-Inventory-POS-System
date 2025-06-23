<?php
// includes/footer.php
?>

    <!-- Close the main container that was opened in header.php -->
    </main>

    <footer>
        <div class="container">
            <!-- Automatically update the year -->
            <p>© <?php echo date('Y'); ?> Royal Smartphones. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- You can add your JavaScript files here for better performance -->
    <!-- <script src="/inventory-system/js/main.js"></script> -->

    <style>
        footer {
            background: linear-gradient(180deg, var(--panel-dark-blue) 0%, var(--bg-deep-navy) 100%);
            border-top: 3px solid var(--border-silver-blue);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.5);
            padding: 1rem 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        footer:hover {
            transform: translateY(-2px);
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.6), 0 0 20px rgba(76, 108, 141, 0.4);
        }

        footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        footer p {
            color: var(--text-silver);
            font-size: 0.9rem;
            margin: 0;
            font-weight: 400;
            transition: color 0.3s ease;
        }

        footer:hover p {
            color: var(--text-white);
        }

        @media (max-width: 768px) {
            footer {
                padding: 0.8rem 0;
            }

            footer p {
                font-size: 0.8rem;
            }
        }
    </style>
</body>
</html>
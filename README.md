# Smartphone Inventory System

A PHP-based web application for managing a smartphone shop’s inventory, sales, and reporting. The system supports user authentication with role-based access, product and variant management, a point-of-sale (POS) interface, and detailed sales reporting, all styled with a modern, responsive dark theme featuring glowing borders and animations.

## Features
- **User Authentication**:
  - Secure login and registration with password hashing (`auth/login.php`, `auth/register.php`).
  - Role-based access control (Admin, Manager, Sales Staff) with session management (`core/functions.php`).
  - Animated welcome screen on login with top-selling product slideshow.
- **Product Management**:
  - Manage master smartphone models (brand, model name, description, release year, image) via `products/create_master.php`.
  - Add/edit/delete product variants (storage, RAM, color, price, stock) via `products/view.php`.
  - Upload and auto-resize product images to 500x500px JPG (`core/functions.php`).
- **Inventory Tracking**:
  - Track individual smartphone units by IMEI and serial number with statuses (In Stock, Sold, Returned, Defective) (`database.sql`).
  - Visual stock indicators on the product dashboard (green for sufficient, red for low, gray for zero stock) (`products/index.php`).
- **Point of Sale (POS)**:
  - Real-time product search with debounced input and quick-add buttons for frequent products (`pos/index.php`, `js/main.js`).
  - Dynamic cart management with quantity controls and stock validation using SweetAlert2 popups.
  - Finalize sales with inventory updates and redirect to receipt generation (`js/main.js`).
- **Sales and Profit Reporting**:
  - Detailed sales report with revenue, cost, profit, and items sold (`sales_report.php`).
  - Export sales data to CSV (`export_sales_csv.php`, implied from link).
- **Supplier and Customer Management**:
  - Manage suppliers and purchase orders (Pending, Shipped, Completed, Cancelled) (`database.sql`).
  - Store customer details for sales tracking.
- **Responsive UI**:
  - Dark navy theme with glowing sky-blue borders and gold accents, using Poppins font (`products/index.php`, `pos/index.php`).
  - Enhanced with SweetAlert2 for popups and Animate.css for animations.
- **Security**:
  - PDO with prepared statements for secure database queries (`database.php`).
  - XSS prevention using `htmlspecialchars` (`core/functions.php`).
  - Secure image upload and resizing with validation for JPG, PNG, GIF, WEBP formats.

## Tech Stack
- **Backend**: PHP 7.4+, MySQL/MariaDB, PDO
- **Frontend**: HTML, CSS (custom dark theme), JavaScript (SweetAlert2, Animate.css)
- **Database**: MySQL/MariaDB with tables for users, products, variants, inventory, sales, suppliers, and customers
- **Server**: Apache (e.g., XAMPP, LAMPP)
- **Dependencies**:
  - SweetAlert2 (`https://cdn.jsdelivr.net/npm/sweetalert2@11`)
  - Animate.css (`https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css`)
  - Poppins font (`https://fonts.googleapis.com/css2?family=Poppins`)

## Requirements
- PHP >= 7.4
- MySQL/MariaDB
- Apache server (e.g., XAMPP, LAMPP)
- Web browser (Chrome, Firefox, etc.)

## Installation
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/ambitious-bioinformatician/Smartphone-Inventory-POS-System.git
   cd smartphone-inventory-system
   ```
2. **Set Up the Database**:
   - Create a MySQL/MariaDB database named `inventory_management_system`.
   - Import the schema:
     ```bash
     mysql -u root -p inventory_management_system < config/database.sql
     ```
3. **Configure Database Connection**:
   - Edit `config/database.php` to match your database credentials (default: `root`, no password):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'inventory_management_system');
     ```
4. **Set File Permissions**:
   - Ensure the `uploads/products/` directory is writable:
     ```bash
     chmod -R 777 uploads/products
     ```
5. **Start the Server**:
   - Place the project in your web server’s root (e.g., `/opt/lampp/htdocs` for LAMPP).
   - Start the server:
     ```bash
     sudo /opt/lampp/lampp start
     ```
6. **Access the Application**:
   - Open `http://localhost/smartphone-inventory-system` in your browser.

## Usage
1. **Register/Login**:
   - Register at `auth/register.php` (defaults to Sales Staff role).
   - Log in at `auth/login.php` to access the dashboard with a welcome animation showcasing top-selling products.
2. **Manage Products**:
   - View the product dashboard at `products/index.php` to see all smartphone models in a grid layout.
   - Add new models at `products/create_master.php` with optional image uploads.
   - Manage variants (storage, RAM, color) at `products/view.php?id=<master_id>`.
3. **Process Sales**:
   - Use the POS at `pos/index.php` to search products, add to cart, adjust quantities, and finalize sales.
   - Stock validation prevents overselling, with SweetAlert2 popups for errors.
4. **View Reports**:
   - Access the sales report at `sales_report.php` to view revenue, costs, profit, and export to CSV.
   - Monitor inventory via the product dashboard or custom inventory reports (if implemented).
5. **Manage Suppliers and Customers**:
   - Add/edit suppliers and purchase orders via relevant scripts (e.g., `purchase_orders` table).
   - Track customer details linked to sales.

## Directory Structure
```
smartphone-inventory-system/
├── auth/                # Login, registration, and logout scripts
│   ├── login.php        # User login with welcome animation
│   ├── register.php     # User registration with top product slideshow
├── config/              # Database configuration and schema
│   ├── database.php     # PDO database connection
│   ├── database.sql     # MySQL schema for tables
├── core/                # Helper functions (redirect, image resize, security)
│   ├── functions.php    # Core utility functions
├── includes/            # Header and footer templates
├── js/                  # JavaScript for POS and interactivity
│   ├── main.js          # POS search, cart, and sale finalization
├── pos/                 # Point of Sale interface
│   ├── index.php        # POS with product search and cart
├── products/            # Product and variant management
│   ├── index.php        # Product dashboard
│   ├── create_master.php # Add new smartphone model
│   ├── view.php         # View and manage product variants
├── reports/             # Sales and inventory reports
│   ├── sales_report.php # Sales and profit reporting
├── uploads/             # Product image uploads
│   ├── products/        # Resized product images
├── images/              # Default and placeholder images
└── index.php            # Root router (redirects to login or dashboard)
```

## Database Schema
The `inventory_management_system` database includes:
- `users`: User accounts with roles (Admin, Manager, Sales Staff).
- `products_master`: Smartphone models (brand, model name, image, etc.).
- `product_variants`: Variants (storage, RAM, color, price, stock).
- `inventory_items`: Individual units (IMEI, serial number, status).
- `sales`: Sale transactions (date, total, cashier name).


## Contributing
1. Fork the repository.
2. Create a branch (`git checkout -b feature/your-feature`).
3. Commit changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a Pull Request.

## License
MIT License

## Contact
For questions, contact [hlatwayne@gmail.com or https://github.com/ambitious-bioinformatician].


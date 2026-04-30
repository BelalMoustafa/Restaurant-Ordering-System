# Restaurant Ordering System — Project Specifications

> **Technology Stack:** Core PHP (no frameworks) + MySQL
> **Environment:** Local execution only (XAMPP / MAMP)
> **Author:** University Project

---

## 1. Project Definition & Mandatory Requirements

### 1.1 System Overview
A web-based restaurant system where **Admins** manage the digital menu, and **Users** browse the menu and place orders.

### 1.2 Roles & Authorization
- **Admin:** Has full access to manage (CRUD) the menu items and view/manage all orders.
- **User:** Limited access. Can only view the menu and place new orders.
- Role-based access control must be enforced on every protected page.

### 1.3 Database (MySQL)
Must have exactly **3 core tables:**
- `users`
- `menu_items`
- `orders`

### 1.4 Authentication System
- Implement **Registration**, **Login**, and **Logout**.
- Use PHP's native `password_hash()` for storing passwords.
- Use PHP's native `password_verify()` for validating passwords at login.
- No plain-text passwords stored anywhere.

### 1.5 Sessions & Cookies
- Use `$_SESSION['user_id']` and `$_SESSION['role']` to store logged-in user state.
- Implement a **"Remember Me"** feature utilizing Cookies during the login process.
- The remember-me token must be stored as a SHA-256 hash in the `remember_token` column of the `users` table. The raw token is stored only in the cookie; never the hash.

### 1.6 CRUD Operations
- **Admin** will perform full **Create, Read, Update, Delete** on `menu_items`.
- **Users** will perform **Create** (place order) and **Read** (view their orders) on `orders`.

### 1.7 File & Image Handling
- Admin must be able to **upload images** for `menu_items`.
  - Validate image types: PNG and JPG only.
  - Validate file size (enforce a maximum limit).
- Admin must be able to **upload a PDF** version of the full menu.
  - Validate file type: PDF only.
- Store all file paths in the database.
- Files are stored on the server under the `uploads/` directory.

### 1.8 CSRF Protection
- All state-changing POST forms (delete, update, create) must include a CSRF token.
- Generate the token with `bin2hex(random_bytes(32))` and store it in `$_SESSION['csrf_token']`.
- Validate using `hash_equals()` on every POST handler before any database interaction.

---

## 2. Directory Structure

```
restaurant_system/
│
├── index.php                        # Public landing page — shows the menu and a welcome screen
├── .htaccess                        # Apache config — blocks direct access to sensitive dirs
│
├── config/
│   └── db.php                       # Database connection using MySQLi OO, defines DB constants
│
├── includes/
│   ├── auth.php                     # Reusable auth helpers: isLoggedIn(), isAdmin(), redirectIfNotAuth()
│   ├── header.php                   # Shared HTML header/navbar included on every page
│   └── footer.php                   # Shared HTML footer included on every page
│
├── auth/
│   ├── login.php                    # Login form and logic — handles POST, sets $_SESSION and cookie
│   ├── register.php                 # Registration form and logic — validates input, hashes password
│   └── logout.php                   # Destroys session, clears remember-me cookie, redirects to login
│
├── admin/
│   ├── dashboard.php                # Admin home — summary stats (total items, total orders)
│   ├── menu_items/
│   │   ├── index.php                # Lists all menu items in a table with Edit/Delete actions
│   │   ├── create.php               # Form + logic to add a new menu item with image upload
│   │   ├── edit.php                 # Form + logic to update an existing menu item
│   │   └── delete.php               # Handles delete action for a menu item (POST only)
│   ├── orders/
│   │   ├── index.php                # Lists all orders placed by all users
│   │   └── view.php                 # Shows full detail of a single order
│   └── upload_menu_pdf.php          # Form + logic for admin to upload the restaurant's PDF menu
│
├── user/
│   ├── dashboard.php                # User home — shows the menu and a link to place an order
│   ├── menu.php                     # Displays all available menu items with images and prices
│   ├── place_order.php              # Form + logic for a user to submit a new order
│   └── my_orders.php                # Shows the logged-in user's own order history
│
├── uploads/
│   ├── images/                      # Stores uploaded menu item images (PNG/JPG)
│   ├── pdfs/                        # Stores uploaded PDF menu files
│   └── .htaccess                    # Blocks direct PHP execution inside uploads for security
│
└── assets/
    ├── css/
    │   └── style.css                # Global stylesheet for the entire application
    └── js/
        └── main.js                  # Client-side JS for form validation and UI interactions
```

---

## 3. Development Rules (Strictly Enforced)

| # | Rule | Description |
|---|------|-------------|
| 1 | **Local Execution Only** | Focus 100% on XAMPP/MAMP. No deployment, hosting, or server configuration tasks. |
| 2 | **Monochrome UI** | Strictly black and white design. Clean layouts, ample whitespace, subtle CSS animations. Zero non-monochrome colors anywhere in CSS or JS. |
| 3 | **Security — Prepared Statements** | Prevent SQL Injection using MySQLi OO Prepared Statements (`$conn->prepare`, `bind_param`, `get_result`) for every single database query. PDO is strictly forbidden. |
| 4 | **Security — CSRF** | All state-changing POST forms must include a CSRF token validated with `hash_equals()`. |
| 5 | **One Task at a Time** | Code is written for one task at a time. No proceeding to the next task without explicit confirmation. |

---

## 4. Task Backlog (19 Tasks)

### Phase 1 — Database Foundation

- **Task 1: SQL Schema**
  Write and execute the SQL script to create the `restaurant_db` database and the 3 core tables: `users`, `menu_items`, and `orders` — with correct data types, constraints, and foreign keys.

---

### Phase 2 — Project Bootstrap & Configuration

- **Task 2: Project Skeleton & DB Connection**
  Create the full directory structure on disk and write `config/db.php` — a MySQLi OO connection file with error handling.

- **Task 3: Global Assets — CSS & JS**
  Write `assets/css/style.css` (full monochrome design system: typography, layout, forms, buttons, tables, animations) and `assets/js/main.js` (basic client-side form validation and UI helpers).

- **Task 4: Shared Includes — Header, Footer & Auth Helpers**
  Write `includes/header.php`, `includes/footer.php`, and `includes/auth.php` (the `isLoggedIn()`, `isAdmin()`, and redirect helper functions).

---

### Phase 3 — Authentication System

- **Task 5: User Registration Page**
  Write `auth/register.php` — the registration form and POST handler with input validation and `password_hash()`.

- **Task 6: User Login Page**
  Write `auth/login.php` — the login form and POST handler with `password_verify()`, session creation, and the "Remember Me" cookie logic.

- **Task 7: Logout**
  Write `auth/logout.php` — session destruction, cookie clearing, and redirect.

---

### Phase 4 — Admin Features

- **Task 8: Admin Dashboard**
  Write `admin/dashboard.php` — protected admin home page showing summary stats (total menu items, total orders).

- **Task 9: Menu Items — List & Read**
  Write `admin/menu_items/index.php` — a protected table listing all menu items with their images, prices, and action buttons.

- **Task 10: Menu Items — Create (with Image Upload)**
  Write `admin/menu_items/create.php` — the add-item form and POST handler with image upload validation (PNG/JPG, size limit) and MySQLi prepared statement insert.

- **Task 11: Menu Items — Edit & Update**
  Write `admin/menu_items/edit.php` — the edit form pre-populated with existing data and POST handler with optional image replacement.

- **Task 12: Menu Items — Delete**
  Write `admin/menu_items/delete.php` — POST-only delete handler that removes the record and its associated image file from disk.

- **Task 13: PDF Menu Upload**
  Write `admin/upload_menu_pdf.php` — form and POST handler for uploading and replacing the restaurant's PDF menu (PDF type validation).

- **Task 14: Admin — View All Orders**
  Write `admin/orders/index.php` and `admin/orders/view.php` — list all orders and drill into a single order's full detail.

---

### Phase 5 — User Features

- **Task 15: User Dashboard & Menu Page**
  Write `user/dashboard.php` and `user/menu.php` — the user's home and the public-facing menu display with item images and prices.

- **Task 16: Place an Order**
  Write `user/place_order.php` — the order form and POST handler that inserts a new order record linked to the logged-in user.

- **Task 17: My Orders — Order History**
  Write `user/my_orders.php` — displays the logged-in user's own order history, read from the `orders` table.

---

### Phase 6 — Public Landing Page & Security Hardening

- **Task 18: Public Landing Page**
  Write the root `index.php` — the public-facing welcome page showing the menu and navigation links to login/register.

- **Task 19: Security Hardening**
  Write the `.htaccess` files (root and `uploads/`) to block direct access to sensitive directories and prevent PHP execution inside the uploads folder.

---

## 5. Database Schema

### `users`
- `id` — INT, Primary Key, Auto Increment
- `name` — VARCHAR(100), full name of the user
- `email` — VARCHAR(150), unique, used for login
- `password` — VARCHAR(255), stores the hashed password (`password_hash()`)
- `role` — ENUM(`admin`, `user`), defaults to `user`
- `remember_token` — VARCHAR(64), NULL, stores the SHA-256 hash of the remember-me token; raw token lives only in the browser cookie
- `created_at` — TIMESTAMP, auto-set on insert

---

### `menu_items`
- `id` — INT, Primary Key, Auto Increment
- `name` — VARCHAR(150), name of the dish
- `description` — TEXT, description of the dish
- `price` — DECIMAL(10,2), price of the item (0.00 is valid for complimentary items)
- `category` — VARCHAR(100), e.g. Starters, Mains, Desserts
- `image_path` — VARCHAR(255), relative path to the uploaded image file
- `is_available` — TINYINT(1), flag to show/hide item from the menu
- `created_at` — TIMESTAMP, auto-set on insert

---

### `orders`
- `id` — INT, Primary Key, Auto Increment
- `user_id` — INT, Foreign Key → `users.id`
- `menu_item_id` — INT, Foreign Key → `menu_items.id`
- `quantity` — INT, number of units ordered
- `total_price` — DECIMAL(10,2), calculated total for the order line
- `status` — ENUM(`pending`, `confirmed`, `cancelled`), defaults to `pending`
- `notes` — TEXT, optional special instructions from the user
- `created_at` — TIMESTAMP, auto-set on insert

---

*End of Project Specifications*

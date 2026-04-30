# Hamza Defense Notes

## 1. Assigned Tasks

Hamza was responsible for:

- Task 1: SQL Schema.
- Task 10: Menu Items Create with Image Upload.

These tasks are foundational because Task 1 creates the database structure used by the entire system, and Task 10 creates the admin feature that inserts menu items into the `menu_items` table.

## 2. Task 1: SQL Schema

## 2.1 Task Objective

The objective of Task 1 was to create the complete MySQL database foundation for the Restaurant Ordering System.

The schema had to satisfy strict project requirements:

- Database name: `restaurant_db`.
- Exactly 3 core tables:
  - `users`
  - `menu_items`
  - `orders`
- Correct primary keys.
- Correct foreign keys.
- Correct role model.
- Correct order relationship model.
- Password column large enough for hashes.
- Remember-me token storage.
- Local reset-friendly SQL script.

## 2.2 File Responsible

```text
database/schema.sql
```

## 2.3 SQL Script Deep-Dive

### Database Reset

```sql
DROP DATABASE IF EXISTS restaurant_db;
CREATE DATABASE restaurant_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE restaurant_db;
```

Explanation:

- `DROP DATABASE IF EXISTS` allows the team to reset the project quickly during development and defense.
- `CREATE DATABASE restaurant_db` creates the required database.
- `CHARACTER SET utf8mb4` supports full Unicode text.
- `COLLATE utf8mb4_unicode_ci` provides Unicode-aware comparison.
- `USE restaurant_db` tells MySQL that following table creation statements belong to this database.

Defense point:

- This design is useful for university demos because the database can be recreated from a clean state every time.

### Users Table

```sql
CREATE TABLE users (
    id             INT                  NOT NULL AUTO_INCREMENT,
    name           VARCHAR(100)         NOT NULL,
    email          VARCHAR(150)         NOT NULL,
    password       VARCHAR(255)         NOT NULL,
    role           ENUM('admin','user') NOT NULL DEFAULT 'user',
    remember_token VARCHAR(64)          NULL,
    created_at     TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_remember_token (remember_token)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
```

Logical explanation:

- `id` is the unique identifier for every account.
- `name` stores the display name.
- `email` is used for login.
- `password` stores the hashed password, not plain text.
- `role` separates admin and normal user behavior.
- `remember_token` stores the SHA-256 hash of the remember-me token.
- `created_at` automatically stores account creation time.

Constraint explanation:

- `PRIMARY KEY (id)` makes each user uniquely identifiable.
- `UNIQUE KEY uq_users_email (email)` prevents duplicate accounts with the same email.
- `UNIQUE KEY uq_users_remember_token (remember_token)` prevents two users from sharing the same remember token hash.

Why `VARCHAR(255)` for password:

- PHP password hashes can be long.
- `VARCHAR(255)` safely supports bcrypt and future algorithms used by `PASSWORD_DEFAULT`.

Why `remember_token` is `VARCHAR(64)`:

- SHA-256 hashes are 64 hexadecimal characters.
- The raw token is never stored in the database.

Why `role` is an ENUM:

- Only two roles are allowed: `admin` and `user`.
- This prevents invalid roles such as `manager`, `root`, or `guest` from being accidentally stored.

### Menu Items Table

```sql
CREATE TABLE menu_items (
    id           INT           NOT NULL AUTO_INCREMENT,
    name         VARCHAR(150)  NOT NULL,
    description  TEXT          NULL,
    price        DECIMAL(10,2) NOT NULL,
    category     VARCHAR(100)  NOT NULL,
    image_path   VARCHAR(255)  NULL,
    is_available TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_menu_items_category     (category),
    KEY idx_menu_items_is_available (is_available)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
```

Logical explanation:

- `id` uniquely identifies each menu item.
- `name` stores dish name.
- `description` stores optional dish details.
- `price` stores the item price.
- `category` supports grouping menu items.
- `image_path` stores the relative path to uploaded item image.
- `is_available` controls whether customers can see and order the item.
- `created_at` stores when the item was added.

Why `DECIMAL(10,2)` for price:

- Money-like values should not use floating point storage.
- `DECIMAL(10,2)` stores exact two-decimal values such as `12.50`.
- It supports zero-price items like complimentary dishes.

Why `is_available` exists:

- Admins can hide an item without deleting it.
- This protects historical order records.
- Users only see items where `is_available = 1`.

Why indexes were added:

- `idx_menu_items_category` helps category-based sorting/filtering.
- `idx_menu_items_is_available` helps queries that only show available items.

### Orders Table

```sql
CREATE TABLE orders (
    id           INT                                      NOT NULL AUTO_INCREMENT,
    user_id      INT                                      NOT NULL,
    menu_item_id INT                                      NOT NULL,
    quantity     INT                                      NOT NULL DEFAULT 1,
    total_price  DECIMAL(10,2)                            NOT NULL,
    status       ENUM('pending','confirmed','cancelled')  NOT NULL DEFAULT 'pending',
    notes        TEXT                                     NULL,
    created_at   TIMESTAMP                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_user_id      (user_id),
    KEY idx_orders_menu_item_id (menu_item_id),
    KEY idx_orders_status       (status),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_orders_menu_item
        FOREIGN KEY (menu_item_id) REFERENCES menu_items (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
```

Logical explanation:

- `id` uniquely identifies each order.
- `user_id` links the order to the customer.
- `menu_item_id` links the order to the ordered dish.
- `quantity` stores how many units were ordered.
- `total_price` stores the calculated total at the time of ordering.
- `status` tracks admin processing.
- `notes` stores optional customer instructions.
- `created_at` stores order time.

Why `total_price` is stored:

- Menu prices can change later.
- Historical orders should preserve the total price at the time of order.
- It makes order history stable and understandable.

Why `status` is an ENUM:

- Only valid statuses are allowed:
  - `pending`
  - `confirmed`
  - `cancelled`
- This prevents invalid order states.

Foreign key explanation:

- `orders.user_id` references `users.id`.
- `orders.menu_item_id` references `menu_items.id`.

Why `ON DELETE CASCADE` for users:

- If a user account is deleted, their related orders can be removed because the user no longer exists.
- In this simple university system, user deletion is not exposed as a feature, but the database rule remains consistent.

Why `ON DELETE RESTRICT` for menu items:

- Menu items with orders should not be deleted.
- Deleting them would damage order history.
- The application also checks this before deletion.

Defense point:

- This is an example of enforcing business rules at both the application layer and database layer.

### Seed Admin and User Accounts

```sql
INSERT INTO users (name, email, password, role) VALUES (
    'Admin User',
    'admin@restaurant.com',
    '$2y$12$IoRPTmpkPuVpVJNRbCngN.Soil3pN4WPKL43RUwHQhGgkBEVhBqw6',
    'admin'
);

INSERT INTO users (name, email, password, role) VALUES (
    'Test User',
    'user@restaurant.com',
    '$2y$12$IoRPTmpkPuVpVJNRbCngN.Soil3pN4WPKL43RUwHQhGgkBEVhBqw6',
    'user'
);
```

Explanation:

- These records create default accounts for testing.
- The stored password value is already hashed.
- The default password for both seeded accounts is `admin123`.

Defense point:

- Even seed data respects password hashing rules.
- No plain-text password is stored in the database.

### Seed Menu Items

The SQL script inserts sample menu items into `menu_items`.

Purpose:

- The public menu is not empty during first demo.
- User and admin pages can be tested immediately after import.
- Categories, prices, and availability can be demonstrated quickly.

Important example:

- `Chef's Special` has price `0.00` and `is_available = 0`.

Defense point:

- This proves the schema supports zero-price items and hidden items.

## 2.4 Design Decisions for Task 1

### Why exactly 3 tables?

The project specification requires exactly 3 core tables:

- `users`
- `menu_items`
- `orders`

The schema avoids adding unnecessary tables such as `settings`, `categories`, or `order_items`.

### Why no separate `admins` table?

Admins and users both authenticate the same way.

A single `users` table with a `role` column is simpler because:

- One login system handles both roles.
- Authorization is controlled by `role`.
- Duplicate account logic is avoided.

### Why no separate `categories` table?

The project does not require category management as a separate CRUD feature.

Using `category VARCHAR(100)` is suitable because:

- It keeps the schema within 3 tables.
- It is simple for admins to enter categories.
- It supports grouping items on menu pages.

### Why no `order_items` table?

The current business rule is one menu item per order row.

An `order_items` table would be useful for multi-item carts, but:

- It would add a fourth table or require a more complex schema.
- The specification requires only 3 tables.
- The current system meets the requirement with `orders.menu_item_id`.

### Why InnoDB?

InnoDB supports:

- Foreign keys.
- Transactions.
- Referential integrity.

This is necessary for relationships between users, menu items, and orders.

## 2.5 Alternatives for Task 1

### Alternative: More Normalized Database

Possible additional tables:

- `categories`
- `order_items`
- `settings`
- `menu_pdfs`

Why not chosen:

- The specification requires exactly 3 tables.
- Extra tables would violate project constraints.

### Alternative: Store PDF Path in Database

Possible design:

- Add `settings` table.
- Store `menu_pdf_path`.

Why not chosen:

- A fourth table would break the requirement.
- The fixed path `uploads/pdfs/menu.pdf` is simpler and acceptable.

### Alternative: Use `FLOAT` for Price

Why not chosen:

- Floating point can cause precision issues.
- `DECIMAL(10,2)` is better for currency-like values.

## 2.6 Dependencies for Task 1

Task 1 has no older task dependency because it is the first task.

However, all later tasks depend on it:

- Task 2 needs the database name for connection.
- Task 5 and Task 6 need the `users` table.
- Task 10 and Task 11 need the `menu_items` table.
- Task 16 and Task 17 need the `orders` table.
- Task 14 needs all three tables for admin order views.

Defense point:

- Task 1 is the foundation of the entire system.

---

## 3. Task 10: Menu Items Create With Image Upload

## 3.1 Task Objective

The objective of Task 10 was to let admins create new menu items through a protected form.

The feature had to:

- Be admin-only.
- Validate CSRF token.
- Validate item fields.
- Allow price `0.00`.
- Reject negative prices.
- Optionally upload an image.
- Accept only JPG and PNG images.
- Enforce a maximum image size.
- Insert data into `menu_items` using MySQLi OO prepared statements.

## 3.2 File Responsible

```text
admin/menu_items/create.php
```

## 3.3 Code Deep-Dive

### Application Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
```

Explanation:

- `APP_RUNNING` allows included files to know they are being loaded by the application.
- `session_start()` ensures access to `$_SESSION`.
- `auth.php` provides authorization and CSRF helper functions.
- `db.php` provides the `$conn` MySQLi connection.

Why this is before HTML:

- The page may need to redirect.
- Redirects must happen before output.
- This avoids `headers already sent` errors.

### Authorization

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- `requireLogin()` blocks guests.
- `requireAdmin()` blocks regular users.
- Only admins can create menu items.

Dependency:

- This depends on Task 4 because `requireLogin()` and `requireAdmin()` are defined in `includes/auth.php`.
- It depends on Task 6 because login creates the session values used by these helpers.

### Upload Constants

```php
define('MAX_IMAGE_SIZE',    2 * 1024 * 1024);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('UPLOAD_DIR',         __DIR__ . '/../../uploads/images/');
```

Explanation:

- `MAX_IMAGE_SIZE` limits images to 2MB.
- `ALLOWED_MIME_TYPES` defines server-verified image types.
- `ALLOWED_EXTENSIONS` defines allowed filename extensions.
- `UPLOAD_DIR` defines where images are stored.

Why constants are used:

- They make validation rules clear.
- If size or allowed types change, they can be updated in one place.

### Form State Variables

```php
$errors          = [];
$formName        = '';
$formDescription = '';
$formPrice       = '';
$formCategory    = '';
$formAvailable   = true;
```

Explanation:

- `$errors` stores validation messages.
- Form variables preserve entered values after validation failure.
- `$formAvailable` defaults new items to available.

Why preserve form values:

- It improves user experience.
- Admin does not need to retype all fields after one validation error.

### POST Detection and CSRF Validation

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('create.php');
```

Explanation:

- The page only processes form submission when the request method is POST.
- `requireValidCsrf()` validates the submitted token.
- If invalid, it redirects back and stops execution.

Why CSRF comes first:

- No state-changing action should happen before token validation.
- This prevents forged form submissions.

### Reading Form Inputs

```php
$rawName        = trim($_POST['name']        ?? '');
$rawDescription = trim($_POST['description'] ?? '');
$rawPrice       = trim($_POST['price']       ?? '');
$rawCategory    = trim($_POST['category']    ?? '');
$rawAvailable   = isset($_POST['is_available']) ? 1 : 0;
```

Explanation:

- Inputs are read from `$_POST`.
- `trim()` removes unnecessary whitespace.
- Null coalescing prevents undefined index warnings.
- Checkbox value is converted to integer `1` or `0`.

Why raw values are kept:

- Raw values are used for validation and database insertion.
- Escaped values are used only for display.

### Escaping Values for Re-display

```php
$formName        = htmlspecialchars($rawName,        ENT_QUOTES, 'UTF-8');
$formDescription = htmlspecialchars($rawDescription, ENT_QUOTES, 'UTF-8');
$formPrice       = htmlspecialchars($rawPrice,       ENT_QUOTES, 'UTF-8');
$formCategory    = htmlspecialchars($rawCategory,    ENT_QUOTES, 'UTF-8');
$formAvailable   = (bool) $rawAvailable;
```

Explanation:

- These variables are safe to print back into HTML.
- `ENT_QUOTES` escapes both single and double quotes.
- `UTF-8` matches the application charset.

Defense point:

- This prevents XSS if an admin enters HTML or JavaScript in a field.

### Name Validation

```php
if ($rawName === '') {
    $errors['name'] = 'Item name is required.';
} elseif (mb_strlen($rawName) > 150) {
    $errors['name'] = 'Item name must not exceed 150 characters.';
}
```

Explanation:

- Name is required.
- Name length matches the `VARCHAR(150)` database column.
- `mb_strlen()` supports multibyte characters.

### Price Validation

```php
if ($rawPrice === '') {
    $errors['price'] = 'Price is required.';
} elseif (!is_numeric($rawPrice)) {
    $errors['price'] = 'Price must be a valid number.';
} elseif ((float) $rawPrice < 0) {
    $errors['price'] = 'Price cannot be negative.';
}
```

Explanation:

- Price cannot be empty.
- Price must be numeric.
- Price cannot be negative.
- Price `0` is allowed because the check only rejects values below zero.

Defense point:

- This satisfies the business rule that complimentary items can exist.

### Category Validation

```php
if ($rawCategory === '') {
    $errors['category'] = 'Category is required.';
} elseif (mb_strlen($rawCategory) > 100) {
    $errors['category'] = 'Category must not exceed 100 characters.';
}
```

Explanation:

- Category is required.
- Length matches the `VARCHAR(100)` database column.

### File Upload Detection

```php
$imagePath = null;
$fileError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
```

Explanation:

- `$imagePath` starts as null.
- If no file is uploaded, `UPLOAD_ERR_NO_FILE` is used.
- Image upload is optional.

### Upload Error Handling

```php
if ($fileError !== UPLOAD_ERR_NO_FILE) {
    if ($fileError !== UPLOAD_ERR_OK) {
        $errors['image'] = 'File upload failed. Please try again (error code: ' . $fileError . ').';
    } else {
```

Explanation:

- If no file was uploaded, the system continues without image.
- If a file was uploaded but PHP reports an error, validation fails.
- If upload status is OK, deeper validation starts.

### Extracting File Information

```php
$tmpPath      = $_FILES['image']['tmp_name'];
$originalName = $_FILES['image']['name'];
$fileSize     = $_FILES['image']['size'];
$extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
```

Explanation:

- `$tmpPath` is the temporary uploaded file path.
- `$originalName` is the browser-supplied filename.
- `$fileSize` is the uploaded file size.
- `$extension` is extracted and normalized to lowercase.

Security note:

- The original filename is not trusted for storage.
- The final stored filename is generated by the system.

### File Size Validation

```php
if ($fileSize > MAX_IMAGE_SIZE) {
    $errors['image'] = 'Image is too large. Maximum allowed size is 2MB.';
}
```

Explanation:

- Rejects images above 2MB.
- Prevents large file abuse.
- Keeps local demo storage manageable.

### MIME Type Validation

```php
$detectedMime = mime_content_type($tmpPath);
if (!in_array($detectedMime, ALLOWED_MIME_TYPES, true)) {
    $errors['image'] = 'Invalid file type. Only JPG and PNG images are allowed.';
}
```

Explanation:

- `mime_content_type()` checks the actual uploaded file content.
- `in_array(..., true)` uses strict comparison.
- Only JPEG and PNG are accepted.

Why MIME validation matters:

- A user could rename a `.php` or `.txt` file to `.jpg`.
- MIME validation helps detect fake extensions.

### Extension Validation

```php
if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
    $errors['image'] = 'Invalid file extension. Only .jpg, .jpeg, and .png are allowed.';
}
```

Explanation:

- Extension is checked separately.
- This makes the upload policy clear.

Why both MIME and extension are checked:

- MIME checks content.
- Extension checks filename policy.
- Using both is stronger than either alone.

### Generating Safe Filename

```php
$newFilename = uniqid('item_', true) . '.' . $extension;
$destination = UPLOAD_DIR . $newFilename;
```

Explanation:

- `uniqid('item_', true)` generates a unique filename.
- The original filename is not used.
- The validated extension is kept.

Why not use original filename:

- Original filenames may contain spaces, special characters, or path tricks.
- Original filenames may conflict with existing files.
- Generated names reduce collision risk.

### Moving Uploaded File

```php
if (!move_uploaded_file($tmpPath, $destination)) {
    $errors['image'] = 'Failed to save the uploaded image. Please check directory permissions.';
} else {
    $imagePath = 'uploads/images/' . $newFilename;
}
```

Explanation:

- `move_uploaded_file()` safely moves a PHP-uploaded file.
- If moving fails, an error is shown.
- If successful, the relative path is saved for database insertion.

Why relative path:

- Easier to render in HTML.
- Portable inside the project folder.
- Does not expose full local filesystem path.

### Database Insert

```php
$stmtInsert = $conn->prepare(
    'INSERT INTO menu_items (name, description, price, category, image_path, is_available)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmtInsert->bind_param('ssdssi', $rawName, $descValue, $priceValue, $rawCategory, $imageValue, $rawAvailable);
$stmtInsert->execute();
$stmtInsert->close();
```

Explanation:

- `$conn->prepare()` creates a prepared statement.
- Question marks are placeholders.
- `bind_param()` binds PHP variables to SQL placeholders.
- `ssdssi` means:
  - `s`: name string.
  - `s`: description string.
  - `d`: price double.
  - `s`: category string.
  - `s`: image path string.
  - `i`: availability integer.
- `execute()` runs the insert.
- `close()` releases statement resources.

Defense point:

- This prevents SQL injection because user input is never concatenated into SQL.

### Success Redirect

```php
setFlashMessage('success', 'Menu item "' . $rawName . '" added successfully.');
header('Location: index.php');
exit;
```

Explanation:

- Flash message is stored in session.
- Admin is redirected to menu item list.
- `exit` stops further execution.

Why redirect after POST:

- Prevents duplicate form submission on refresh.
- Follows the POST-Redirect-GET pattern.

### Rendering the Form

```php
$pageTitle = 'Add Menu Item';
require_once __DIR__ . '/../../includes/header.php';
```

Explanation:

- Header is included after all redirect-capable logic.
- This prevents header output issues.

### CSRF Hidden Input

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Explanation:

- Adds CSRF token to the form.
- The token is generated or reused from session.
- It is escaped before printing.

## 3.4 Design Decisions for Task 10

### Why admin-only?

Only restaurant administrators should create menu items. If customers could create items, the menu would be untrusted and incorrect.

### Why prepared statements?

Menu item fields come from a form. Prepared statements prevent SQL injection by separating SQL code from user input.

### Why validate price server-side?

HTML input validation can be bypassed. Server-side validation ensures negative prices are rejected even if a user edits the page in developer tools.

### Why allow price zero?

The specification says `0.00` is valid for complimentary items. The validation rejects only values below zero.

### Why image upload is optional?

Some menu items may not have images. The UI already supports a `No Image` placeholder.

### Why JPG and PNG only?

They are common safe image formats for this type of project. Restricting file types reduces upload attack surface.

### Why not store image binary data in the database?

Storing files on disk and paths in the database is simpler and more efficient for this project.

Benefits:

- Database stays smaller.
- Browser can load images directly from file paths.
- Upload folder can be protected by `.htaccess`.

## 3.5 Alternatives for Task 10

### Alternative: Use a PHP Framework Upload Helper

Frameworks like Laravel provide validation and storage helpers.

Why not chosen:

- Frameworks are forbidden.
- Core PHP demonstrates the actual upload process.

### Alternative: Use PDO

PDO supports prepared statements.

Why not chosen:

- The project requires MySQLi OO.
- MySQLi is enough because the database is MySQL only.

### Alternative: Accept More Image Types

Examples:

- GIF.
- WebP.
- SVG.

Why not chosen:

- The specification allows PNG and JPG only.
- SVG can contain script-like content.
- Keeping only JPG and PNG is safer and simpler.

### Alternative: Store Image Path as NULL When No Image Exists

Current implementation stores an empty string when no image exists.

Alternative benefit:

- `NULL` can more clearly mean no image.

Why current approach is acceptable:

- The display logic checks `empty($item['image_path'])`.
- Both empty string and null are handled safely.

## 3.6 Dependencies for Task 10

### Depends on Task 1: Database Schema

Task 10 inserts into:

```text
menu_items
```

Without Task 1, this table would not exist.

Important columns used:

- `name`
- `description`
- `price`
- `category`
- `image_path`
- `is_available`

### Depends on Task 2: Project Skeleton and Database Connection

Task 10 requires:

```text
config/db.php
```

This provides:

```php
$conn
```

Without `$conn`, the insert query cannot run.

### Depends on Task 4: Shared Includes

Task 10 requires:

```text
includes/auth.php
includes/header.php
includes/footer.php
```

Used for:

- Admin authorization.
- CSRF helper.
- Flash messages.
- Shared layout.

### Depends on Task 6: Login

Task 10 relies on session values created during login:

```text
$_SESSION['user_id']
$_SESSION['role']
$_SESSION['user_name']
```

Without login, `requireAdmin()` cannot identify the admin.

### Supports Task 11 and Task 12

Task 10 creates menu items that later tasks can:

- Edit in Task 11.
- Delete in Task 12.

### Supports Task 15 and Task 16

Menu items created by Task 10 become visible to users if `is_available = 1`.

They can then be:

- Browsed in Task 15.
- Ordered in Task 16.

## 4. Hamza Defense Questions and Answers

### Q1: Why did you use `DECIMAL(10,2)` instead of `FLOAT` for price?

Because price values should be exact to two decimal places. Floating point values can introduce precision errors, while `DECIMAL(10,2)` stores exact money-like values.

### Q2: Why does the orders table store `total_price`?

Because menu item prices can change later. Storing `total_price` preserves the order value at the time it was placed.

### Q3: Why is `menu_item_id` deletion restricted?

Because orders depend on menu items. If a menu item with orders is deleted, order history loses meaning. `ON DELETE RESTRICT` protects historical records.

### Q4: Why do you validate both MIME type and extension for images?

Because extension alone can be faked, and MIME type alone does not enforce project filename policy. Checking both provides stronger validation.

### Q5: Why not use the original uploaded filename?

Original filenames can contain unsafe characters, duplicate existing files, or reveal user information. A generated unique filename is safer.

### Q6: Why is CSRF validation required on create?

Creating a menu item changes system state. CSRF validation prevents another website from forcing an authenticated admin to submit a create request.

### Q7: Why use MySQLi prepared statements?

The specification requires MySQLi OO, and prepared statements protect against SQL injection by binding values separately from SQL code.

### Q8: What happens if no image is uploaded?

The system still creates the menu item. The image path becomes empty, and the UI displays a `No Image` placeholder.

### Q9: Why can price be zero?

The project specification says `0.00` is valid for complimentary items. The validation only rejects negative prices.

### Q10: How does Task 10 connect to the rest of the system?

Task 10 creates menu items. Those items appear in admin listing, user menu browsing, and order placement. Without Task 10, the system could not add new dishes dynamically.

## 5. Hamza Summary

Hamza's work is central to the project.

Task 1 created the database foundation:

- Users.
- Menu items.
- Orders.
- Relationships.
- Constraints.
- Seed data.

Task 10 created the admin menu item creation feature:

- Protected admin access.
- CSRF validation.
- Field validation.
- Secure image upload.
- MySQLi prepared insert.
- POST-Redirect-GET success flow.

In defense, Hamza should emphasize:

- The schema was designed around the strict 3-table requirement.
- Foreign keys protect data integrity.
- Prepared statements protect against SQL injection.
- File validation protects the upload feature.
- Price validation supports real business rules, including free items.
- Menu item creation connects directly to user browsing and ordering.


# Habiba Defense Notes

## 1. Assigned Tasks

Habiba was responsible for:

- Task 1: SQL Schema.
- Task 10: Menu Items Create with Image Upload.

These tasks are foundational because Task 1 creates the database structure used by the full system, and Task 10 creates the admin feature that inserts menu items into the `menu_items` table.

Habiba's work connects the database layer with one of the most important admin workflows: creating new dishes that users can later browse and order.

---

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

Main file:

```text
database/schema.sql
```

## 2.2 SQL Script Deep-Dive

### Database Reset

```sql
DROP DATABASE IF EXISTS restaurant_db;
CREATE DATABASE restaurant_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE restaurant_db;
```

Explanation:

- `DROP DATABASE IF EXISTS` allows the database to be recreated cleanly.
- `CREATE DATABASE restaurant_db` creates the required database.
- `CHARACTER SET utf8mb4` supports full Unicode text.
- `COLLATE utf8mb4_unicode_ci` provides Unicode-aware comparison.
- `USE restaurant_db` makes following SQL statements run inside this database.

Defense point:

- This is helpful for a university demo because the team can reset the database quickly before testing.

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

Explanation:

- `id` uniquely identifies every account.
- `name` stores the display name.
- `email` is used for login.
- `password` stores a hashed password.
- `role` separates admin accounts from normal user accounts.
- `remember_token` stores the hashed remember-me token.
- `created_at` stores account creation time automatically.

Constraint explanation:

- `PRIMARY KEY (id)` makes every row unique.
- `UNIQUE KEY uq_users_email (email)` prevents duplicate accounts.
- `UNIQUE KEY uq_users_remember_token (remember_token)` prevents token collisions.

Why `VARCHAR(255)` for passwords:

- `password_hash()` can generate long hashes.
- `VARCHAR(255)` supports bcrypt and future algorithms used by `PASSWORD_DEFAULT`.

Why `remember_token` is `VARCHAR(64)`:

- The application stores a SHA-256 hash of the remember-me token.
- SHA-256 hex output is 64 characters.
- The raw cookie token is not stored in the database.

Why `role` is an ENUM:

- Only `admin` and `user` are valid roles.
- This prevents invalid role values from being stored.

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

Explanation:

- `id` uniquely identifies every menu item.
- `name` stores the dish name.
- `description` stores optional details.
- `price` stores the menu item price.
- `category` stores grouping text such as starters or desserts.
- `image_path` stores the uploaded image path.
- `is_available` controls whether users can see and order the item.
- `created_at` records when the item was created.

Why `DECIMAL(10,2)` for price:

- Money-like values should be exact.
- Floating point storage can introduce precision issues.
- `DECIMAL(10,2)` supports values like `0.00`, `12.50`, and `199.99`.

Why zero price is allowed:

- The system may include complimentary items.
- The application validates only against negative prices.

Why `is_available` exists:

- Admins can hide items without deleting them.
- This protects historical order records.

### Orders Table

```sql
CREATE TABLE orders (
    id           INT                                     NOT NULL AUTO_INCREMENT,
    user_id      INT                                     NOT NULL,
    menu_item_id INT                                     NOT NULL,
    quantity     INT                                     NOT NULL DEFAULT 1,
    total_price  DECIMAL(10,2)                           NOT NULL,
    status       ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    notes        TEXT                                    NULL,
    created_at   TIMESTAMP                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

Explanation:

- `id` uniquely identifies each order.
- `user_id` links the order to the customer.
- `menu_item_id` links the order to the ordered menu item.
- `quantity` stores how many units were ordered.
- `total_price` stores the calculated total at order time.
- `status` tracks admin processing.
- `notes` stores optional customer instructions.
- `created_at` stores order creation time.

Why `total_price` is stored:

- Menu item prices can change later.
- Order history must preserve what the customer actually ordered at the time.

Why `status` is an ENUM:

- Only valid statuses are allowed:
  - `pending`
  - `confirmed`
  - `cancelled`

Why `ON DELETE CASCADE` for users:

- If a user is removed, their dependent orders can be removed.
- User deletion is not a public feature, but the database relationship remains consistent.

Why `ON DELETE RESTRICT` for menu items:

- Menu items with orders should not be deleted.
- Deleting them would damage order history.
- The application delete handler also checks this before deletion.

Defense point:

- The system enforces important business rules in both PHP and SQL.

### Seed Accounts

The schema includes default test accounts.

Purpose:

- The team can test the application immediately after importing the schema.
- Admin and user flows can both be demonstrated during defense.

Important rule:

- Seed passwords are stored as hashes, not plain text.

### Seed Menu Items

The schema also inserts sample menu items.

Purpose:

- The landing page and menu page are not empty after setup.
- Admin and user flows can be tested immediately.
- The system can demonstrate available and unavailable items.

## 2.3 Design Decisions for Task 1

### Why exactly 3 tables?

The project specification requires exactly 3 core tables:

- `users`
- `menu_items`
- `orders`

The schema avoids extra tables such as:

- `categories`
- `order_items`
- `settings`
- `menu_pdfs`

### Why one `users` table for admins and users?

Admins and users both log in with email and password.

A single table with a `role` column is better for this project because:

- One login system supports both roles.
- Authorization is based on the `role` value.
- Duplicate authentication logic is avoided.

### Why no separate `categories` table?

Category management is not a required CRUD feature.

Using a `category` text column:

- Keeps the schema within 3 tables.
- Makes item creation simple.
- Still supports grouping menu items.

### Why no `order_items` table?

The project uses one menu item per order row.

An `order_items` table would be useful for a shopping cart, but:

- It would add more schema complexity.
- It could violate the 3-table requirement.
- The current project does not require multi-item carts.

### Why InnoDB?

InnoDB supports:

- Foreign keys.
- Referential integrity.
- Cascading and restricted relationships.

These are required for a proper relational schema.

## 2.4 Alternatives for Task 1

### Alternative: More Normalized Schema

Possible extra tables:

- `categories`
- `order_items`
- `menu_uploads`
- `settings`

Why not chosen:

- The project requirement limits the schema to 3 tables.
- Extra tables would make the project harder to defend against the specification.

### Alternative: Store Prices as FLOAT

Why not chosen:

- Floating point numbers can create precision issues.
- `DECIMAL(10,2)` is better for money-like values.

### Alternative: Separate Admin Table

Why not chosen:

- Admins and users share the same authentication fields.
- A role column is simpler and avoids duplicate login logic.

### Alternative: Delete Menu Items with Cascade

Why not chosen:

- Cascade deletion would remove historical order meaning.
- `ON DELETE RESTRICT` protects records that users and admins may need later.

## 2.5 Dependencies for Task 1

Task 1 has no older dependency because it is the first task.

However, every later task depends on it:

- Task 2 needs the database name for connection.
- Task 5 and Task 6 need the `users` table.
- Task 8 needs summary data from the tables.
- Task 10, Task 11, and Task 12 need `menu_items`.
- Task 14 needs all three tables.
- Task 16 and Task 17 need `orders`.

Defense point:

- Task 1 is the foundation of the entire project.

---

## 3. Task 10: Menu Items Create With Image Upload

## 3.1 Task Objective

The objective of Task 10 was to let admins create new menu items from a protected admin form.

The feature had to:

- Be admin-only.
- Validate CSRF token.
- Validate item fields.
- Allow price `0.00`.
- Reject negative prices.
- Optionally upload an image.
- Accept only JPG and PNG images.
- Enforce a maximum image size.
- Insert menu item data using MySQLi OO prepared statements.

Main file:

```text
admin/menu_items/create.php
```

## 3.2 Code Deep-Dive

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

- `APP_RUNNING` marks the file as part of the application.
- `session_start()` makes session values available.
- `auth.php` provides login, admin, flash, and CSRF helpers.
- `db.php` provides the MySQLi connection.

Why this is before HTML:

- The page may need to redirect.
- Redirects must happen before output.

### Authorization

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- Guests are redirected to login.
- Regular users are redirected away.
- Only admins can create menu items.

Defense point:

- Menu creation is a protected admin feature.

### Upload Constants

```php
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('UPLOAD_DIR', __DIR__ . '/../../uploads/images/');
```

Explanation:

- `MAX_IMAGE_SIZE` limits uploads to 2MB.
- `ALLOWED_MIME_TYPES` defines accepted real file content types.
- `ALLOWED_EXTENSIONS` defines accepted filename extensions.
- `UPLOAD_DIR` defines the image destination folder.

Why constants are useful:

- Validation rules are easy to review.
- Limits and allowed types can be changed in one place.

### Form State Variables

```php
$errors = [];
$formName = '';
$formDescription = '';
$formPrice = '';
$formCategory = '';
$formAvailable = true;
```

Explanation:

- `$errors` stores validation messages.
- Form variables preserve submitted values after validation failure.
- New items default to available.

Why preserve values:

- Admins should not retype everything after one validation error.

### POST Detection and CSRF Validation

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('create.php');
```

Explanation:

- The form is processed only on POST.
- CSRF validation runs before file saving or database insertion.

Why CSRF comes early:

- No state-changing action should occur before token validation.

### Reading Form Inputs

```php
$rawName = trim($_POST['name'] ?? '');
$rawDescription = trim($_POST['description'] ?? '');
$rawPrice = trim($_POST['price'] ?? '');
$rawCategory = trim($_POST['category'] ?? '');
$rawAvailable = isset($_POST['is_available']) ? 1 : 0;
```

Explanation:

- Reads submitted form fields.
- `trim()` removes unnecessary whitespace.
- Null coalescing avoids undefined index warnings.
- Checkbox value becomes integer `1` or `0`.

### Escaping Values for Re-display

```php
$formName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
$formDescription = htmlspecialchars($rawDescription, ENT_QUOTES, 'UTF-8');
$formPrice = htmlspecialchars($rawPrice, ENT_QUOTES, 'UTF-8');
$formCategory = htmlspecialchars($rawCategory, ENT_QUOTES, 'UTF-8');
$formAvailable = (bool) $rawAvailable;
```

Explanation:

- Raw values are kept for validation and database insertion.
- Escaped values are used when printing back into HTML.
- `ENT_QUOTES` escapes both single and double quotes.
- `UTF-8` matches the project encoding.

Defense point:

- This prevents XSS when the form is re-rendered after validation failure.

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
- Maximum length matches the database column.
- `mb_strlen()` handles multibyte text correctly.

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
- Price `0` is accepted.

Defense point:

- This satisfies the project rule that zero-price items are valid.

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
- Maximum length matches the database column.

### File Upload Detection

```php
$imagePath = null;
$fileError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
```

Explanation:

- Image upload is optional.
- If no file exists, PHP treats it as `UPLOAD_ERR_NO_FILE`.
- `$imagePath` stays null unless a valid file is saved.

### Upload Error Handling

```php
if ($fileError !== UPLOAD_ERR_NO_FILE) {
    if ($fileError !== UPLOAD_ERR_OK) {
        $errors['image'] = 'File upload failed. Please try again.';
    }
}
```

Explanation:

- No file is allowed.
- A failed upload is rejected.
- A successful upload continues to validation.

### File Information

```php
$tmpPath = $_FILES['image']['tmp_name'];
$originalName = $_FILES['image']['name'];
$fileSize = $_FILES['image']['size'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
```

Explanation:

- `$tmpPath` is the temporary uploaded file.
- `$originalName` is the browser-provided filename.
- `$fileSize` is used for size validation.
- `$extension` is normalized for extension validation.

Security note:

- The original filename is not trusted for storage.

### Size Validation

```php
if ($fileSize > MAX_IMAGE_SIZE) {
    $errors['image'] = 'Image is too large. Maximum allowed size is 2MB.';
}
```

Explanation:

- Rejects images above the allowed size.
- Protects local storage from large files.

### MIME Type Validation

```php
$detectedMime = mime_content_type($tmpPath);
if (!in_array($detectedMime, ALLOWED_MIME_TYPES, true)) {
    $errors['image'] = 'Invalid file type. Only JPG and PNG images are allowed.';
}
```

Explanation:

- Checks the actual content type.
- Uses strict comparison.
- Allows only JPEG and PNG.

Why this matters:

- A file extension can be faked.
- MIME validation is stronger than trusting the filename.

### Extension Validation

```php
if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
    $errors['image'] = 'Invalid file extension. Only .jpg, .jpeg, and .png are allowed.';
}
```

Explanation:

- Enforces the visible filename policy.
- Keeps uploads limited to the expected formats.

### Safe Filename Generation

```php
$newFilename = uniqid('item_', true) . '.' . $extension;
$destination = UPLOAD_DIR . $newFilename;
```

Explanation:

- Generates a unique filename.
- Keeps the validated extension.
- Avoids trusting the original filename.

Why not use the original filename:

- It may contain unsafe characters.
- It may conflict with an existing file.
- It may reveal user information.

### Moving Uploaded File

```php
if (!move_uploaded_file($tmpPath, $destination)) {
    $errors['image'] = 'Failed to save the uploaded image.';
} else {
    $imagePath = 'uploads/images/' . $newFilename;
}
```

Explanation:

- `move_uploaded_file()` safely moves a PHP upload.
- The relative path is stored for later display.

Why relative path:

- It works from the project root.
- It avoids exposing the local filesystem path.

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

- Uses MySQLi OO prepared statements.
- SQL contains placeholders.
- User values are bound separately.
- `ssdssi` defines the value types:
  - `s`: name
  - `s`: description
  - `d`: price
  - `s`: category
  - `s`: image path
  - `i`: availability

Defense point:

- This prevents SQL injection because form input is not concatenated into SQL.

### Success Redirect

```php
setFlashMessage('success', 'Menu item "' . $rawName . '" added successfully.');
header('Location: index.php');
exit;
```

Explanation:

- Stores a flash success message.
- Redirects to the menu items list.
- Stops execution.

Why redirect after POST:

- Prevents duplicate insertion if the admin refreshes the page.

### CSRF Hidden Input

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Explanation:

- Adds a CSRF token to the form.
- Escapes the token before output.
- Allows `requireValidCsrf()` to verify the POST request.

## 3.3 Design Decisions for Task 10

### Why admin-only?

Only admins should manage the restaurant menu.

If normal users could create items:

- The public menu would become untrusted.
- Prices could be manipulated.
- The business workflow would break.

### Why prepared statements?

Menu item fields come from a form.

Prepared statements:

- Prevent SQL injection.
- Keep SQL separate from data.
- Satisfy the MySQLi OO project requirement.

### Why server-side validation?

HTML and JavaScript validation can be bypassed.

PHP validation ensures:

- Empty values are rejected.
- Negative prices are rejected.
- File type and size rules are enforced.

### Why allow price zero?

The project supports complimentary items.

The validation rejects:

```text
price < 0
```

but accepts:

```text
price = 0
```

### Why optional image upload?

Some menu items may not have images.

The UI can display a placeholder when `image_path` is empty.

### Why only JPG and PNG?

JPG and PNG are common image formats.

They are easier and safer to validate than formats such as SVG.

### Why store image path, not image binary?

Storing files on disk and paths in the database is simpler.

Benefits:

- Smaller database.
- Easier browser rendering.
- Easier file replacement and cleanup.

## 3.4 Alternatives for Task 10

### Alternative: Use Framework Upload Helpers

Frameworks can simplify uploads.

Why not chosen:

- Frameworks are forbidden.
- Core PHP is required by the project.

### Alternative: Accept SVG or WebP

Why not chosen:

- The requirement is JPG and PNG.
- SVG can carry script-like content.
- More formats create more validation complexity.

### Alternative: Store Uploaded Images in Database

Why not chosen:

- Makes the database larger.
- Makes image serving more complicated.
- File paths are simpler for a local XAMPP/MAMP project.

### Alternative: Trust Browser MIME Type

Why not chosen:

- Browser-provided MIME type can be faked.
- Server-side `mime_content_type()` is stronger.

## 3.5 Dependencies for Task 10

### Depends on Task 1: SQL Schema

Task 10 inserts into:

```text
menu_items
```

Important columns:

- `name`
- `description`
- `price`
- `category`
- `image_path`
- `is_available`

### Depends on Task 2: Database Connection

Task 10 uses:

```text
config/db.php
```

which provides:

```php
$conn
```

### Depends on Task 4: Shared Includes

Task 10 uses:

```text
includes/auth.php
includes/header.php
includes/footer.php
```

for:

- Admin authorization.
- CSRF protection.
- Flash messages.
- Shared layout.

### Depends on Task 6: Login

Admin authorization depends on session values created during login:

```text
$_SESSION['user_id']
$_SESSION['role']
$_SESSION['user_name']
```

### Supports Task 11 and Task 12

Task 10 creates menu items that can later be:

- Edited in Task 11.
- Deleted in Task 12.

### Supports Task 15 and Task 16

Created menu items become visible to users when available.

Users can then:

- Browse them in Task 15.
- Order them in Task 16.

## 4. Habiba Defense Questions and Answers

### Q1: Why did you use exactly three tables?

Because the project specification requires three core tables: `users`, `menu_items`, and `orders`. Extra tables would violate the requirement.

### Q2: Why use `DECIMAL(10,2)` for price?

Because price values should be exact. `DECIMAL(10,2)` is better than floating point storage for money-like values.

### Q3: Why store `total_price` in orders?

Because menu item prices may change later. The order should preserve the total from the time it was placed.

### Q4: Why restrict deletion of ordered menu items?

Because order history depends on the menu item remaining valid. `ON DELETE RESTRICT` protects historical data.

### Q5: Why validate image MIME type and extension?

Because extension alone can be faked. MIME validation checks file content, while extension validation enforces the project format rule.

### Q6: Why use generated filenames?

Generated filenames avoid collisions, unsafe characters, and reliance on user-supplied names.

### Q7: Why allow price zero?

The specification allows complimentary items, so validation rejects negative prices but accepts `0.00`.

### Q8: Why use MySQLi prepared statements?

The project requires Object-Oriented MySQLi, and prepared statements protect against SQL injection.

## 5. Habiba Summary

Habiba's updated responsibilities are Task 1 and Task 10.

Task 1 delivered:

- `restaurant_db`.
- `users` table.
- `menu_items` table.
- `orders` table.
- Primary keys.
- Foreign keys.
- Role model.
- Password hash storage.
- Remember-me token storage.
- Seed data.

Task 10 delivered:

- Admin-only menu item creation.
- CSRF-protected form submission.
- Field validation.
- Price validation that allows zero.
- Secure image upload.
- MySQLi prepared insert.
- Flash message and redirect flow.

In defense, Habiba should emphasize:

```text
Task 1 defines the data foundation.
Task 10 safely writes new menu data into that foundation.
```

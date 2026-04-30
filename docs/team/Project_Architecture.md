# Project Architecture

## 1. Architecture Overview

The Restaurant Ordering System is a local Core PHP web application that uses MySQL as its database and Object-Oriented MySQLi for database access.

The architecture follows a simple page-controller style:

- Each PHP page handles one feature or screen.
- Shared layout code is placed in `includes/header.php` and `includes/footer.php`.
- Shared authentication and security helpers are placed in `includes/auth.php`.
- Database connection logic is centralized in `config/db.php`.
- Static assets are stored in `assets/`.
- Uploaded files are stored in `uploads/`.

There is no framework. This is intentional because the university project specification requires 100% Core PHP.

## 2. Core Technologies

### PHP

PHP is used for:

- Server-side page rendering.
- Form processing.
- Session management.
- Authentication.
- Authorization.
- File upload handling.
- Database operations.

The project uses Core PHP only.

### MySQL

MySQL stores:

- User accounts.
- Menu items.
- Orders.
- Remember-me token hashes.

### MySQLi OO

The project uses Object-Oriented MySQLi:

```php
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
```

Why this matters:

- It satisfies the project rule.
- It supports prepared statements.
- It prevents SQL injection when used correctly.
- It keeps database access explicit and easy to explain in a defense.

### HTML

HTML is used to structure pages, forms, navigation, tables, and menu cards.

### CSS

CSS is stored in:

```text
assets/css/style.css
```

The visual design is monochrome:

- Black.
- White.
- Gray tones.
- Clean spacing.
- Minimal UI.

### JavaScript

JavaScript is stored in:

```text
assets/js/main.js
```

It is used for:

- Client-side form validation.
- File preview.
- Confirmation prompts.
- Price preview on order form.

Important architectural rule:

- JavaScript improves user experience, but security does not depend on JavaScript.
- All important validation is repeated server-side in PHP.

## 3. Folder Structure

```text
Restaurant Ordering System/
|
|-- index.php
|-- .htaccess
|
|-- admin/
|   |-- dashboard.php
|   |-- upload_menu_pdf.php
|   |
|   |-- menu_items/
|   |   |-- index.php
|   |   |-- create.php
|   |   |-- edit.php
|   |   |-- delete.php
|   |
|   |-- orders/
|       |-- index.php
|       |-- view.php
|
|-- assets/
|   |-- css/
|   |   |-- style.css
|   |
|   |-- js/
|       |-- main.js
|
|-- auth/
|   |-- register.php
|   |-- login.php
|   |-- logout.php
|
|-- config/
|   |-- db.php
|
|-- database/
|   |-- schema.sql
|
|-- docs/
|   |-- PROJECT_SPECS.md
|   |-- QA_Testing_Flow.md
|   |-- tasks/
|   |-- team/
|
|-- includes/
|   |-- auth.php
|   |-- header.php
|   |-- footer.php
|
|-- uploads/
|   |-- .htaccess
|   |-- images/
|   |-- pdfs/
|
|-- user/
    |-- dashboard.php
    |-- menu.php
    |-- place_order.php
    |-- my_orders.php
```

## 4. Folder Responsibilities

### Root

#### `index.php`

Public landing page.

Responsibilities:

- Load public menu preview.
- Show login/register links.
- Show dashboard link if already logged in.
- Show PDF menu link if available.

#### `.htaccess`

Apache security configuration.

Responsibilities:

- Disable directory listing.
- Block direct access to sensitive folders.
- Block direct access to sensitive file types.
- Hide server signature.

### `config/`

#### `config/db.php`

Central database connection file.

Responsibilities:

- Define database constants.
- Create `$conn` using `new mysqli(...)`.
- Handle connection failure safely.
- Set charset to `utf8mb4`.

Why central connection matters:

- All pages use the same database configuration.
- If database credentials change, only one file needs updating.
- It reduces duplicated connection code.

### `includes/`

#### `includes/auth.php`

Shared authentication and security helper file.

Important functions:

- `isLoggedIn()`
- `isAdmin()`
- `requireLogin()`
- `requireAdmin()`
- `requireUser()`
- `requireGuest()`
- `csrfToken()`
- `requireValidCsrf()`
- `setFlashMessage()`
- `getFlashMessage()`

Architectural role:

- Keeps repeated security logic in one place.
- Makes each page easier to read.
- Avoids duplicated role-checking code.

#### `includes/header.php`

Shared page header.

Responsibilities:

- Start session if needed.
- Load auth helpers.
- Calculate base path.
- Render HTML `<head>`.
- Render navigation based on role.
- Display flash messages.
- Open the main content container.

Important architecture rule:

- Pages that need redirects or POST processing should run those checks before including `header.php`.
- This avoids `headers already sent` errors.

#### `includes/footer.php`

Shared page footer.

Responsibilities:

- Close layout containers.
- Render footer.
- Load JavaScript file.
- Close HTML document.

### `auth/`

#### `auth/register.php`

Handles user registration.

Responsibilities:

- Validate registration form.
- Check duplicate email.
- Hash password.
- Insert user with role `user`.
- Redirect to login.

#### `auth/login.php`

Handles login and remember-me logic.

Responsibilities:

- Validate login form.
- Fetch user by email.
- Verify password.
- Create session.
- Set remember-me cookie if requested.
- Auto-login remembered users.
- Redirect by role.

#### `auth/logout.php`

Handles logout.

Responsibilities:

- Clear remember token in database.
- Clear session data.
- Destroy session cookie.
- Destroy PHP session.
- Clear remember-me cookie.
- Redirect to login.

### `admin/`

Admin-only area protected by:

```php
requireLogin();
requireAdmin();
```

#### `admin/dashboard.php`

Shows admin statistics.

#### `admin/menu_items/index.php`

Lists all menu items.

#### `admin/menu_items/create.php`

Creates menu items and handles image upload.

#### `admin/menu_items/edit.php`

Updates menu items and optionally replaces images.

#### `admin/menu_items/delete.php`

Deletes menu items if no orders depend on them.

#### `admin/upload_menu_pdf.php`

Uploads or replaces the fixed PDF menu file.

#### `admin/orders/index.php`

Lists all orders.

#### `admin/orders/view.php`

Shows one order and allows status update.

### `user/`

User-only area protected by:

```php
requireUser();
```

#### `user/dashboard.php`

Customer dashboard.

#### `user/menu.php`

Full available menu grouped by category.

#### `user/place_order.php`

Order placement form and logic.

#### `user/my_orders.php`

Customer order history.

### `uploads/`

Stores uploaded files.

Subfolders:

- `uploads/images/`
- `uploads/pdfs/`

Security file:

- `uploads/.htaccess`

Responsibilities:

- Prevent directory listing.
- Block PHP execution.
- Allow only safe uploaded file types to be served.

### `database/`

#### `database/schema.sql`

Full database creation script.

Responsibilities:

- Drop and recreate database.
- Create 3 required tables.
- Define keys and constraints.
- Insert default admin and user accounts.
- Insert seed menu items.

## 5. Database Architecture

## 5.1 Table: `users`

Purpose:

- Stores both admin and user accounts.

Columns:

```text
id
name
email
password
role
remember_token
created_at
```

Important rules:

- `id` is primary key.
- `email` is unique.
- `password` stores hashed password.
- `role` is either `admin` or `user`.
- `remember_token` stores SHA-256 hash, not raw token.

Why admin and user are in one table:

- Both roles share login behavior.
- Role column separates permissions.
- It avoids duplicate login tables.

## 5.2 Table: `menu_items`

Purpose:

- Stores restaurant menu items.

Columns:

```text
id
name
description
price
category
image_path
is_available
created_at
```

Important rules:

- `price` is DECIMAL for money-like values.
- `price` can be `0.00`.
- `is_available` controls whether users can see/order the item.
- `image_path` stores relative path to uploaded image.

Why `is_available` exists:

- Items can be hidden without deletion.
- Historical order records remain meaningful.

## 5.3 Table: `orders`

Purpose:

- Stores orders placed by users.

Columns:

```text
id
user_id
menu_item_id
quantity
total_price
status
notes
created_at
```

Relationships:

```text
orders.user_id -> users.id
orders.menu_item_id -> menu_items.id
```

Important rules:

- `user_id` links the order to the customer.
- `menu_item_id` links the order to the ordered item.
- `total_price` is calculated when order is created.
- `status` is one of `pending`, `confirmed`, or `cancelled`.

Foreign key behavior:

- If a user is deleted, that user's orders are deleted with `ON DELETE CASCADE`.
- If a menu item has orders, deletion is restricted with `ON DELETE RESTRICT`.

Why menu item deletion is restricted:

- Orders are business history.
- Deleting ordered items would destroy context for existing orders.
- The application also checks for related orders before deletion.

## 6. Request Architecture

## 6.1 Typical GET Request

Example:

```text
GET /admin/menu_items/edit.php?id=5
```

Flow:

1. PHP page starts session.
2. Page loads auth helpers.
3. Page loads database connection.
4. Page checks login and role.
5. Page validates query parameter.
6. Page fetches required data.
7. Page includes header.
8. Page renders HTML.
9. Page includes footer.

## 6.2 Typical POST Request

Example:

```text
POST /admin/menu_items/edit.php
```

Flow:

1. PHP page starts session.
2. Page loads auth helpers.
3. Page loads database connection.
4. Page checks login and role.
5. Page validates CSRF token.
6. Page validates form data.
7. Page performs database update.
8. Page sets flash message.
9. Page redirects.

Important point:

- POST processing happens before including `header.php`.
- This prevents redirect errors after HTML output.

## 7. Security Architecture

### Prepared Statements

All SQL uses MySQLi prepared statements.

Purpose:

- Prevent SQL injection.
- Separate SQL structure from user input.

### Password Hashing

Passwords are stored using:

```php
password_hash($password, PASSWORD_DEFAULT)
```

Passwords are verified using:

```php
password_verify($password, $storedHash)
```

Purpose:

- Never store plain-text passwords.
- Let PHP choose a secure current hashing algorithm.

### Sessions

Sessions store:

```text
$_SESSION['user_id']
$_SESSION['role']
$_SESSION['user_name']
```

Purpose:

- Track authenticated user.
- Support role-based authorization.

### CSRF Protection

CSRF token is generated with:

```php
bin2hex(random_bytes(32))
```

Validation uses:

```php
hash_equals()
```

Protected actions:

- Register.
- Login.
- Create menu item.
- Edit menu item.
- Delete menu item.
- Upload PDF.
- Place order.
- Update order status.

### Output Escaping

Dynamic text is escaped with:

```php
htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
```

Purpose:

- Prevent XSS.
- Safely display user-entered data.

### File Upload Validation

Image upload checks:

- Upload error.
- File size.
- MIME type.
- Extension.

PDF upload checks:

- Upload error.
- File size.
- MIME type.
- Extension.

### `.htaccess` Protection

Root `.htaccess` protects:

- `config/`
- `includes/`
- `database/`
- `docs/`
- sensitive file extensions.

Uploads `.htaccess` protects:

- Prevents PHP execution in uploads.
- Blocks direct execution of risky script extensions.

## 8. Why No Framework Was Used

The project uses no Laravel, Symfony, CodeIgniter, or other framework.

Reasons:

- The specification requires Core PHP.
- The project must demonstrate understanding of PHP fundamentals.
- Authentication, routing, database access, and security are implemented manually.
- The code is easier for university defense because every part can be explained directly.

Alternative:

- A framework could provide routing, migrations, ORM, middleware, and CSRF helpers.

Why not chosen:

- It would violate the project requirement.
- It would hide important learning outcomes behind framework features.

## 9. Why MySQLi OO Was Used Instead of PDO

MySQLi OO was chosen because the project specification explicitly requires it.

Benefits:

- Supports prepared statements.
- Works directly with MySQL.
- Uses object-oriented style.
- Easy to demonstrate with `$conn->prepare()`, `bind_param()`, and `get_result()`.

Alternative:

- PDO supports multiple database engines and named parameters.

Why not chosen:

- PDO is forbidden by the project rules.
- Multi-database support is unnecessary for a local MySQL-only university project.

## 10. Defense Talking Points

### What architecture pattern is this?

It is a simple page-controller architecture. Each PHP file controls one page or action, while shared code is extracted into includes.

### Where is authentication handled?

Authentication logic is mainly in:

- `auth/login.php`
- `auth/register.php`
- `auth/logout.php`
- `includes/auth.php`

### Where is authorization handled?

Authorization is handled by helper functions:

- `requireLogin()`
- `requireAdmin()`
- `requireUser()`
- `requireGuest()`

### Where is database logic centralized?

The connection is centralized in:

```text
config/db.php
```

Each page prepares and executes only the queries needed for that feature.

### How does the system prevent SQL injection?

By using MySQLi OO prepared statements for every database operation involving input.

### How does the system prevent XSS?

By escaping dynamic output with `htmlspecialchars()`.

### How does the system protect file uploads?

By validating file size, MIME type, extension, upload status, and blocking script execution in the uploads folder.

### Why does the system run locally only?

The project is designed for XAMPP/MAMP demonstration. Deployment configuration is intentionally excluded because the requirement is local execution only.


# Ziad Yasser Defense Notes

## 1. Assigned Tasks

Ziad Yasser was responsible for:

- Task 5: User Registration Page.
- Task 14: Admin View All Orders.

These tasks connect two important sides of the system:

- Customer onboarding through registration.
- Admin supervision of orders after users start using the system.

Task 5 creates users. Task 14 allows admins to view and process the orders created by those users.

## 2. Task 5: User Registration Page

## 2.1 Task Objective

The objective of Task 5 was to build a secure registration page where a guest can create a normal customer account.

The page had to:

- Show a registration form.
- Validate user input.
- Prevent duplicate emails.
- Hash passwords using `password_hash()`.
- Insert the new account into the `users` table.
- Assign the default role `user`.
- Redirect to login after success.
- Protect the POST request with CSRF validation.

## 2.2 File Responsible

```text
auth/register.php
```

## 2.3 Code Deep-Dive

### Application Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- `APP_RUNNING` marks this as a valid application entry point.
- `session_start()` enables sessions for CSRF and flash messages.
- `auth.php` provides `requireGuest()`, `requireValidCsrf()`, and flash helpers.
- `db.php` provides the `$conn` MySQLi connection.

Why this comes before HTML:

- The page may redirect after successful registration.
- Redirects must happen before output.

### Guest-Only Access

```php
requireGuest();
```

Explanation:

- Already logged-in users should not register again.
- If an admin is logged in, they go to admin dashboard.
- If a user is logged in, they go to user dashboard.

Defense point:

- This keeps the auth flow clean and prevents logged-in users from creating extra accounts unnecessarily.

### Initial Form State

```php
$errors    = [];
$formName  = '';
$formEmail = '';
```

Explanation:

- `$errors` stores validation errors.
- `$formName` and `$formEmail` preserve safe values after validation fails.

Why password is not preserved:

- Password fields should never be reprinted into HTML.
- This avoids exposing sensitive input.

### POST Request and CSRF

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('register.php');
```

Explanation:

- The registration logic only runs for POST requests.
- `requireValidCsrf()` blocks forged form submissions.

Why registration needs CSRF:

- Registration changes system state by creating a new account.
- A malicious site should not be able to force account creation.

### Reading Inputs

```php
$rawName            = trim($_POST['name']             ?? '');
$rawEmail           = trim($_POST['email']            ?? '');
$rawPassword        = $_POST['password']              ?? '';
$rawConfirmPassword = $_POST['confirm_password']      ?? '';
```

Explanation:

- Reads submitted fields.
- Trims name and email.
- Password is not trimmed because spaces may be intentional.
- Null coalescing prevents undefined index warnings.

### Escaping Re-displayed Values

```php
$formName  = htmlspecialchars($rawName,  ENT_QUOTES, 'UTF-8');
$formEmail = htmlspecialchars($rawEmail, ENT_QUOTES, 'UTF-8');
```

Explanation:

- These values may be printed back into form fields.
- `htmlspecialchars()` prevents XSS.

### Name Validation

```php
if ($rawName === '') {
    $errors['name'] = 'Full name is required.';
} elseif (mb_strlen($rawName) > 100) {
    $errors['name'] = 'Name must not exceed 100 characters.';
}
```

Explanation:

- Name is required.
- Maximum length matches `users.name VARCHAR(100)`.
- `mb_strlen()` supports multibyte characters.

### Email Validation

```php
if ($rawEmail === '') {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
} elseif (mb_strlen($rawEmail) > 150) {
    $errors['email'] = 'Email address must not exceed 150 characters.';
}
```

Explanation:

- Email is required.
- `filter_var()` validates email format.
- Maximum length matches `users.email VARCHAR(150)`.

### Password Validation

```php
if ($rawPassword === '') {
    $errors['password'] = 'Password is required.';
} elseif (mb_strlen($rawPassword) < 8) {
    $errors['password'] = 'Password must be at least 8 characters long.';
}
```

Explanation:

- Password is required.
- Minimum length improves basic security.

### Confirm Password Validation

```php
if (!isset($errors['password'])) {
    if ($rawConfirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($rawConfirmPassword !== $rawPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
}
```

Explanation:

- Confirm password is checked only if main password passed validation.
- It must not be empty.
- It must match the password exactly.

### Duplicate Email Check

```php
if (empty($errors['email'])) {
    $stmtCheck = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmtCheck->bind_param('s', $rawEmail);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $errors['email'] = 'This email address is already registered.';
    }
    $stmtCheck->close();
}
```

Explanation:

- Runs only if email format is already valid.
- Uses MySQLi prepared statement.
- Checks whether email already exists.
- If found, adds an error.

Why this matters:

- Email is the login identifier.
- Duplicate accounts with the same email would break login expectations.
- The database also has a unique key, but the application gives a friendly message.

### Password Hashing and Insert

```php
$hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
$role = 'user';
$stmtInsert = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$stmtInsert->bind_param('ssss', $rawName, $rawEmail, $hashedPassword, $role);
$stmtInsert->execute();
$stmtInsert->close();
```

Explanation:

- `password_hash()` securely hashes the password.
- `PASSWORD_DEFAULT` lets PHP choose the recommended algorithm.
- Role is hardcoded to `user`.
- Insert uses placeholders and `bind_param()`.

Why role is hardcoded:

- Users must not choose their own role.
- Prevents a guest from registering as admin.

Why no plain-text password:

- Plain-text passwords are dangerous.
- If database data is exposed, hashes are much safer than plain passwords.

### Success Redirect

```php
setFlashMessage('success', 'Account created successfully. Please log in.');
header('Location: login.php');
exit;
```

Explanation:

- Shows success message on login page.
- Redirects after POST to prevent duplicate registration on refresh.

### Registration Form

The form includes:

- Full Name.
- Email Address.
- Password.
- Confirm Password.
- Hidden CSRF token.

Important line:

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Purpose:

- Adds the CSRF token required by the POST handler.

## 2.4 Design Decisions for Task 5

### Why use `password_hash()`?

Because it is PHP's native secure password hashing API. It automatically handles salts and uses a strong algorithm.

### Why `PASSWORD_DEFAULT`?

It lets PHP upgrade the default algorithm over time without changing application code.

### Why check duplicate email in application code?

The database unique key protects data integrity, but the application check gives a clear user-facing error.

### Why use `requireGuest()`?

Registration is for guests. Logged-in users should continue to their dashboard instead.

### Why use CSRF on registration?

Creating a user is a state-changing operation, so it must reject forged submissions.

## 2.5 Alternatives for Task 5

### Alternative: Store Plain Passwords

Why not chosen:

- It is insecure.
- It violates the specification.
- It exposes users if the database is leaked.

### Alternative: Let Users Choose Role

Why not chosen:

- It would allow privilege escalation.
- Admin accounts should be controlled by the system/team.

### Alternative: Use Email Verification

Why not chosen:

- The project is local-only.
- SMTP/email setup is outside the required scope.

### Alternative: Use a Framework Auth Package

Why not chosen:

- Frameworks are not allowed.
- The project must demonstrate Core PHP authentication.

## 2.6 Dependencies for Task 5

Task 5 depends on:

- Task 1: `users` table must exist.
- Task 2: `config/db.php` must provide `$conn`.
- Task 3: CSS and JS style/validate the form.
- Task 4: `requireGuest()`, CSRF, flash messages, header, footer.

Task 5 supports:

- Task 6 because login needs registered users.
- Task 16 because only registered users can place orders.
- Task 17 because order history belongs to registered users.

---

## 3. Task 14: Admin View All Orders

## 3.1 Task Objective

Task 14 allows admins to view and process all orders placed by users.

It includes two pages:

```text
admin/orders/index.php
admin/orders/view.php
```

The feature must:

- Be admin-only.
- List all orders.
- Join order data with user and menu item data.
- Show full detail for one order.
- Update order status.
- Validate CSRF before status update.
- Whitelist allowed status values.

## 3.2 `admin/orders/index.php` Deep-Dive

### Page Setup

```php
define('APP_RUNNING', true);
$pageTitle = 'All Orders';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db.php';
```

Explanation:

- Defines app context.
- Sets page title.
- Loads shared header and database connection.

Note for defense:

- This page is read-only, so it does not perform POST redirects.

### Authorization

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- Blocks guests and normal users.
- Only admins can see all orders.

### Orders Query

```php
$stmtOrders = $conn->prepare(
    'SELECT
         orders.id,
         orders.quantity,
         orders.total_price,
         orders.status,
         orders.created_at,
         users.name  AS user_name,
         menu_items.name AS item_name
     FROM orders
     JOIN users      ON orders.user_id      = users.id
     JOIN menu_items ON orders.menu_item_id = menu_items.id
     ORDER BY orders.created_at DESC'
);
```

Explanation:

- Selects order fields.
- Joins `users` to show customer name.
- Joins `menu_items` to show item name.
- Sorts newest orders first.

Why joins are needed:

- The `orders` table stores IDs.
- Admin needs readable names, not just IDs.

Why still use `prepare()` without user input:

- The project standard is to use prepared statements for all queries.
- It keeps query style consistent.

### Fetching Orders

```php
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
$orders       = $resultOrders->fetch_all(MYSQLI_ASSOC);
$stmtOrders->close();
```

Explanation:

- Executes query.
- Gets result set.
- Fetches all rows into an array.
- Closes statement.

### Status Badge Function

```php
function statusBadgeClass(string $status): string
{
    return match ($status) {
        'confirmed' => 'badge-confirmed',
        'cancelled' => 'badge-cancelled',
        default     => 'badge-pending',
    };
}
```

Explanation:

- Converts status value into CSS class.
- `match` makes the mapping clear.
- Unknown values safely fall back to pending style.

### Table Rendering

The table displays:

- Order ID.
- Customer.
- Item ordered.
- Quantity.
- Total.
- Status.
- Date.
- View Details action.

Security note:

- Customer and item names are escaped with `htmlspecialchars()`.
- Numeric values are cast or formatted.

## 3.3 `admin/orders/view.php` Deep-Dive

### Bootstrap and Authorization

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- Starts session.
- Loads helpers and database.
- Blocks non-admins.
- This happens before HTML because status update may redirect.

### Status Whitelist

```php
$validStatuses = ['pending', 'confirmed', 'cancelled'];
```

Explanation:

- Only these statuses are accepted.
- Matches the database ENUM.

Why whitelist:

- Prevents invalid status values.
- Protects business workflow.

### Order ID and CSRF Timing

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    requireValidCsrf($orderId > 0 ? 'view.php?id=' . $orderId : 'index.php');
} else {
    $orderId = (int) ($_GET['id'] ?? 0);
}
```

Explanation:

- GET uses `id` from URL.
- POST uses hidden `order_id`.
- CSRF is validated immediately for POST.

Why this matters:

- Status update is state-changing.
- CSRF must be checked before update.

### Invalid Order ID Handling

```php
if ($orderId <= 0) {
    setFlashMessage('danger', 'Invalid order ID.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- Invalid IDs are rejected.
- Admin returns to orders list.

### Order Detail Query

```php
$stmtOrder = $conn->prepare(
    'SELECT
         orders.id, orders.quantity, orders.total_price, orders.status,
         orders.notes, orders.created_at,
         users.id AS user_id, users.name AS user_name, users.email AS user_email,
         menu_items.id AS item_id, menu_items.name AS item_name,
         menu_items.price AS item_unit_price
     FROM orders
     JOIN users      ON orders.user_id      = users.id
     JOIN menu_items ON orders.menu_item_id = menu_items.id
     WHERE orders.id = ?
     LIMIT 1'
);
```

Explanation:

- Fetches full order data.
- Joins customer data.
- Joins menu item data.
- Filters by order ID.

Why include unit price:

- Admin can compare item price and total price.
- It helps verify the order calculation.

### Fetching the Order

```php
$stmtOrder->bind_param('i', $orderId);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();
$order       = $resultOrder->fetch_assoc();
$stmtOrder->close();
```

Explanation:

- Binds order ID as integer.
- Executes query.
- Fetches one row.
- Closes statement.

### Missing Order Handling

```php
if (!$order) {
    setFlashMessage('danger', 'Order not found.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- If ID is valid format but no row exists, admin returns to list.

### Status Update

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = trim($_POST['status'] ?? '');
    if (!in_array($newStatus, $validStatuses, true)) {
        setFlashMessage('danger', 'Invalid status value. Please select a valid status.');
        header('Location: view.php?id=' . $orderId);
        exit;
    }
    $stmtUpdate = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmtUpdate->bind_param('si', $newStatus, $orderId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    setFlashMessage('success', 'Order #' . $orderId . ' status updated to "' . ucfirst($newStatus) . '".');
    header('Location: view.php?id=' . $orderId);
    exit;
}
```

Explanation:

- Reads new status.
- Validates against whitelist.
- Updates with prepared statement.
- Redirects back to detail page.

Why redirect after update:

- Prevents duplicate update on refresh.
- Shows updated data on GET.

### Detail Rendering

The page displays:

- Order ID.
- Date.
- Status.
- Customer name.
- Customer email.
- Item name.
- Unit price.
- Quantity.
- Total price.
- Notes.
- Status update form.

Security note:

- Dynamic text is escaped.
- Numeric values are cast or formatted.

### CSRF Token in Status Form

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Purpose:

- Protects order status update from forged POST requests.

## 3.4 Design Decisions for Task 14

### Why admin-only?

Orders contain customer information. Only admins should see all orders.

### Why use joins?

Orders store IDs. Joins allow admin pages to show meaningful customer and item names.

### Why status whitelist?

The order lifecycle is limited to `pending`, `confirmed`, and `cancelled`.

### Why CSRF on status update?

Changing order status changes business data. It must reject forged submissions.

## 3.5 Alternatives for Task 14

### Alternative: Let Users Update Their Own Order Status

Why not chosen:

- Status is part of restaurant processing.
- Users should not confirm or cancel from admin workflow.

### Alternative: Store More Statuses

Examples:

- `preparing`
- `delivered`
- `paid`

Why not chosen:

- Specification requires only `pending`, `confirmed`, and `cancelled`.

### Alternative: Separate API Endpoint for Status Update

Why not chosen:

- A simple POST form is easier to demonstrate.
- No JavaScript or API layer is required.

## 3.6 Dependencies for Task 14

Task 14 depends on:

- Task 1 for `users`, `menu_items`, and `orders`.
- Task 2 for database connection.
- Task 4 for auth, CSRF, header, footer, and flash messages.
- Task 6 because admin login creates the session.
- Task 16 because real orders are created by users.

Task 14 supports:

- Admin business workflow.
- Order monitoring.
- Order status processing.

## 4. Ziad Yasser Defense Questions and Answers

### Q1: Why does registration set role to `user` manually?

Because guests must not choose their role. Admin privileges should never be self-assigned.

### Q2: Why use `password_hash()`?

It securely hashes passwords and automatically handles salts and algorithm details.

### Q3: Why use a duplicate email query if the database already has a unique key?

The database protects integrity, while the application gives the user a friendly validation message.

### Q4: Why does admin orders page join three tables?

The orders table stores IDs. Joins are needed to display customer names and item names.

### Q5: Why validate status in PHP?

It prevents invalid values before they reach the database and keeps the workflow controlled.

### Q6: Why use CSRF on status update?

Because status update changes order data, so it must reject forged POST requests.

## 5. Ziad Yasser Summary

Ziad Yasser handled both entry into the system and admin order visibility.

Task 5 delivered:

- Secure registration.
- Input validation.
- Duplicate email check.
- Password hashing.
- Default user role.
- CSRF-protected form.

Task 14 delivered:

- Admin order list.
- Full order detail page.
- Customer and item joins.
- Order status badges.
- CSRF-protected status update.
- Status whitelist validation.

In defense, Ziad Yasser should emphasize secure user creation and controlled admin processing of order records.


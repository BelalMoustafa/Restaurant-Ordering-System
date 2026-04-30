# Ziad Walid Defense Notes

## 1. Assigned Tasks

Ziad Walid was responsible for:

- Task 6: User Login Page.
- Task 15: User Dashboard and Menu Page.

These tasks connect authentication with the customer experience. Task 6 lets users and admins enter the system securely. Task 15 gives authenticated users their dashboard and menu browsing flow.

## 2. Task 6: User Login Page

## 2.1 Task Objective

The objective of Task 6 was to implement secure login.

The login page had to:

- Show a login form.
- Validate email and password.
- Fetch the user by email using MySQLi prepared statements.
- Verify password using `password_verify()`.
- Regenerate session ID after success.
- Store user identity and role in session.
- Redirect admins and users to different dashboards.
- Implement Remember Me using a secure random token.
- Store only the SHA-256 token hash in the database.
- Protect login POST with CSRF validation.

## 2.2 File Responsible

```text
auth/login.php
```

## 2.3 Code Deep-Dive

### Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
```

Explanation:

- Defines application context.
- Starts session.
- Loads database connection.
- Loads auth helpers.

Why before HTML:

- Login can redirect after successful authentication or remember-me auto-login.

### Form State

```php
$errors    = [];
$formEmail = '';
$authError = '';
```

Explanation:

- `$errors` stores field-level errors.
- `$formEmail` preserves safe email input after validation.
- `$authError` stores generic authentication failure.

### Remember-Me Auto Login

```php
if (!isLoggedIn() && isset($_COOKIE['remember_user'])) {
    $cookieToken = $_COOKIE['remember_user'];
```

Explanation:

- If no session exists but remember cookie exists, the system attempts auto-login.
- This allows returning users to be logged in automatically.

### Cookie Token Shape Check

```php
if (strlen($cookieToken) === 64 && ctype_alnum($cookieToken)) {
```

Explanation:

- Raw token is expected to be 64 characters.
- `bin2hex(random_bytes(32))` produces 64 hex characters.
- This rejects malformed cookies early.

### Hashing Cookie Token

```php
$tokenHash = hash('sha256', $cookieToken);
```

Explanation:

- The browser stores raw token.
- The database stores only the SHA-256 hash.
- To compare, the submitted cookie token is hashed.

Why not store raw token in database:

- If database is exposed, attackers cannot directly use stored token as cookie.

### Fetching Remembered User

```php
$stmtCookie  = $conn->prepare('SELECT id, name, email, role FROM users WHERE remember_token = ? LIMIT 1');
$stmtCookie->bind_param('s', $tokenHash);
$stmtCookie->execute();
$resultCookie = $stmtCookie->get_result();
$cookieUser   = $resultCookie->fetch_assoc();
$stmtCookie->close();
```

Explanation:

- Looks for a user with matching remember token hash.
- Uses prepared statement.
- Fetches user identity and role.

### Token Rotation

```php
$newToken     = bin2hex(random_bytes(32));
$newTokenHash = hash('sha256', $newToken);
$stmtRotate   = $conn->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
$stmtRotate->bind_param('si', $newTokenHash, $cookieUser['id']);
```

Explanation:

- Generates a new token after successful remember-me login.
- Stores new token hash in database.
- Sends new raw token to cookie.

Why rotate token:

- Reduces usefulness of stolen old tokens.
- Improves remember-me security.

### Session Creation on Remember-Me Login

```php
session_regenerate_id(true);
$_SESSION['user_id']   = $cookieUser['id'];
$_SESSION['role']      = $cookieUser['role'];
$_SESSION['user_name'] = $cookieUser['name'];
```

Explanation:

- Regenerates session ID to prevent fixation.
- Stores authenticated user state.

### Secure Cookie Options

```php
setcookie('remember_user', $newToken, [
    'expires'  => time() + (30 * 24 * 60 * 60),
    'path'     => '/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

Explanation:

- Expires in 30 days.
- Path `/` applies to the whole local project.
- `httponly` prevents JavaScript access.
- `samesite` reduces CSRF-like cookie sending.
- `secure` is false because local HTTP XAMPP may not use HTTPS.

Defense point:

- In production HTTPS, `secure` should be true.

### Guest-Only Login Page

```php
requireGuest();
```

Explanation:

- Already logged-in users are redirected away.
- Prevents showing login form unnecessarily.

### Login POST and CSRF

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('login.php');
```

Explanation:

- Login form submits by POST.
- CSRF token must be valid before processing credentials.

### Reading Credentials

```php
$rawEmail    = trim($_POST['email']    ?? '');
$rawPassword = $_POST['password']      ?? '';
$rememberMe  = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';
```

Explanation:

- Reads email, password, and remember-me checkbox.
- Password is not trimmed.
- Remember-me becomes boolean.

### Field Validation

The page validates:

- Email required.
- Email valid format.
- Password required.

This avoids unnecessary database lookup when required fields are missing.

### Fetch User by Email

```php
$stmtUser = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
$stmtUser->bind_param('s', $rawEmail);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user       = $resultUser->fetch_assoc();
$stmtUser->close();
```

Explanation:

- Prepared statement fetches user by email.
- Password hash and role are needed for authentication and redirect.

### Password Verification

```php
if (!$user || !password_verify($rawPassword, $user['password'])) {
    $authError = 'Invalid email or password.';
}
```

Explanation:

- If no user or wrong password, show generic error.
- `password_verify()` checks raw password against stored hash.

Why generic error:

- Prevents attackers from learning whether an email exists.

### Session Creation on Normal Login

```php
session_regenerate_id(true);
$_SESSION['user_id']   = $user['id'];
$_SESSION['role']      = $user['role'];
$_SESSION['user_name'] = $user['name'];
```

Explanation:

- Regenerates session ID after login.
- Stores logged-in state.

### Remember-Me Token on Login

If checked:

- Generate raw token.
- Hash token.
- Store hash in database.
- Store raw token in HttpOnly cookie.

If not checked:

- Clear remember token in database.
- Expire cookie.

Why clear token when unchecked:

- User explicitly chose not to be remembered.
- Old remember-me sessions should not persist.

### Role-Based Redirect

```php
if ($user['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
} else {
    header('Location: ../user/dashboard.php');
}
exit;
```

Explanation:

- Admins go to admin dashboard.
- Customers go to user dashboard.

## 2.4 Design Decisions for Task 6

### Why use `password_verify()`?

Because passwords are hashed. Direct comparison would not work and would be insecure.

### Why regenerate session ID?

To prevent session fixation. A new authenticated session ID is safer after login.

### Why hash remember-me token in database?

If the database is exposed, raw usable tokens are not revealed.

### Why rotate remember-me token?

Token rotation reduces risk if an older token is stolen.

### Why role-based redirect?

The system has different dashboards for admins and users.

## 2.5 Alternatives for Task 6

### Alternative: Store User ID in Cookie

Why not chosen:

- User IDs are predictable.
- Anyone could fake another user ID.

### Alternative: Store Plain Remember Token in Database

Why not chosen:

- If database leaks, attackers can use tokens immediately.

### Alternative: Use PHP Basic Auth

Why not chosen:

- It does not support roles, remember-me, or custom UI well.

### Alternative: Framework Auth

Why not chosen:

- Frameworks are forbidden.
- Manual Core PHP auth is required for learning and defense.

## 2.6 Dependencies for Task 6

Task 6 depends on:

- Task 1 for `users` table and `remember_token` column.
- Task 2 for database connection.
- Task 4 for auth helpers, CSRF, header, footer, and flash messages.
- Task 5 because users must exist before login.

Task 6 supports:

- All admin pages.
- All user pages.
- Role-based access control.

---

## 3. Task 15: User Dashboard and Menu Page

## 3.1 Task Objective

Task 15 creates the customer-facing authenticated pages:

```text
user/dashboard.php
user/menu.php
```

The feature must:

- Protect user pages.
- Redirect admins away from customer flow.
- Show user dashboard.
- Show user order count.
- Link to menu, place order, and my orders.
- Display full available menu.
- Group menu items by category.
- Show item images, names, descriptions, and prices.
- Link to order page.

## 3.2 `user/dashboard.php` Deep-Dive

### Setup

```php
define('APP_RUNNING', true);
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- Defines app context.
- Sets dashboard title.
- Loads shared header.
- Loads database connection.

### User Protection

```php
requireLogin();

if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}
```

Explanation:

- Guests are sent to login.
- Admins are redirected to admin dashboard.
- This page is for customers only.

Defense point:

- User and admin flows are intentionally separated.

### User Order Count

```php
$stmtOrderCount = $conn->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmtOrderCount->bind_param('i', $_SESSION['user_id']);
$stmtOrderCount->execute();
$stmtOrderCount->bind_result($myOrderCount);
$stmtOrderCount->fetch();
$stmtOrderCount->close();
```

Explanation:

- Counts only orders for the logged-in user.
- Uses `$_SESSION['user_id']`, not POST or GET.
- Prepared statement binds the user ID.

Why this matters:

- A user cannot request another user's count.
- The session is the trusted identity source.

### PDF Menu Check

```php
$pdfPath   = __DIR__ . '/../uploads/pdfs/menu.pdf';
$pdfExists = file_exists($pdfPath);
```

Explanation:

- Checks if admin uploaded a PDF menu.
- If it exists, dashboard shows download button.

### Safe User Name

```php
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Guest', ENT_QUOTES, 'UTF-8');
```

Explanation:

- User name comes from session.
- It is escaped before output.

### Dashboard UI

The dashboard displays:

- Welcome message.
- Date.
- My Orders count.
- PDF menu link if available.
- Quick actions:
  - Browse Menu.
  - Place an Order.
  - My Orders.
- Getting Started panel if user has no orders.

## 3.3 `user/menu.php` Deep-Dive

### Setup and Authorization

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireUser();
```

Explanation:

- Starts session before role checks.
- Loads auth and database.
- `requireUser()` ensures logged-in customer access and redirects admins away.

### Menu Query

```php
$stmtMenu = $conn->prepare(
    'SELECT id, name, description, price, category, image_path
     FROM menu_items
     WHERE is_available = 1
     ORDER BY category ASC, name ASC'
);
```

Explanation:

- Selects only available menu items.
- Orders by category, then name.
- Uses prepared statement.

Why `is_available = 1`:

- Admins can hide items without deleting them.
- Users should not order hidden items.

### Fetching Menu Items

```php
$stmtMenu->execute();
$resultMenu = $stmtMenu->get_result();
$allItems   = $resultMenu->fetch_all(MYSQLI_ASSOC);
$stmtMenu->close();
```

Explanation:

- Executes query.
- Fetches all available items as arrays.

### Grouping by Category

```php
$itemsByCategory = [];
foreach ($allItems as $item) {
    $itemsByCategory[$item['category']][] = $item;
}
```

Explanation:

- Creates an array where each category contains its items.
- This supports category sections in the UI.

### PDF Link

```php
$pdfPath   = __DIR__ . '/../uploads/pdfs/menu.pdf';
$pdfExists = file_exists($pdfPath);
```

Explanation:

- Shows download link only if PDF exists.

### Rendering Categories

Each category section has:

- Section heading.
- Menu grid.
- Menu cards.

For each item, the system displays:

- Image or placeholder.
- Category.
- Name.
- Description.
- Price.
- Order This button.

### Output Escaping

Examples:

```php
htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8')
htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8')
```

Explanation:

- Prevents stored XSS from menu item fields.

### Description Truncation

```php
$displayDesc = mb_strlen($desc) > 100
    ? htmlspecialchars(mb_substr($desc, 0, 100), ENT_QUOTES, 'UTF-8') . '&hellip;'
    : htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
```

Explanation:

- Long descriptions are shortened.
- Keeps menu cards visually balanced.
- Text is escaped before display.

### Order Link

```php
<a href="place_order.php?item_id=<?= (int) $item['id'] ?>" class="btn btn-primary btn-sm">Order This</a>
```

Explanation:

- Passes selected item ID to order form.
- Casts ID to integer for safe output.

## 3.4 Design Decisions for Task 15

### Why separate dashboard and menu?

Dashboard gives quick actions and user summary. Menu focuses on browsing items.

### Why group by category?

It matches restaurant menu behavior and makes browsing easier.

### Why show only available items?

Unavailable items should not be ordered.

### Why truncate descriptions?

It keeps cards clean and readable.

### Why escape every dynamic value?

Menu data can be entered by admins. Escaping prevents stored XSS.

## 3.5 Alternatives for Task 15

### Alternative: Public Full Menu

Why not chosen:

- The project requires users to log in to place orders.
- Public page already shows a preview.

### Alternative: Category Table

Why not chosen:

- The database is limited to 3 tables.
- A text category field is enough for this project.

### Alternative: Cart System

Why not chosen:

- It would require more complex order structure.
- The specification uses one menu item per order row.

## 3.6 Dependencies for Task 15

Task 15 depends on:

- Task 1 for `menu_items` and `orders`.
- Task 2 for database connection.
- Task 3 for menu card styling.
- Task 4 for auth helpers and layout.
- Task 6 for login session.
- Task 10 because admins create menu items.
- Task 13 because PDF link depends on uploaded PDF.

Task 15 supports:

- Task 16 because users browse items before ordering.

## 4. Ziad Walid Defense Questions and Answers

### Q1: Why use `password_verify()` instead of comparing strings?

Stored passwords are hashes, not plain text. `password_verify()` safely checks raw password against hash.

### Q2: Why regenerate the session ID?

It prevents session fixation after login.

### Q3: Why hash remember-me tokens?

So database leaks do not expose raw usable login tokens.

### Q4: Why redirect by role?

Admins and users have different dashboards and permissions.

### Q5: Why does the menu show only available items?

Admin can hide items without deleting them. Users should order only available items.

### Q6: Why use `$_SESSION['user_id']` for order count?

Session identity is trusted after login. User ID must not come from request parameters.

## 5. Ziad Walid Summary

Ziad Walid handled login and customer browsing.

Task 6 delivered:

- Secure credential validation.
- Password verification.
- Session creation.
- Session regeneration.
- Remember-me token logic.
- Role-based redirects.
- CSRF-protected login.

Task 15 delivered:

- User dashboard.
- User order count.
- PDF menu link.
- Full menu page.
- Category grouping.
- Safe output escaping.
- Order links.

In defense, Ziad Walid should emphasize that authentication is the gateway to the whole system and that menu browsing is safely connected to the ordering flow.


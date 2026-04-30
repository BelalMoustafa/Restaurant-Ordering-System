# Ziad Marzouk Defense Notes

## 1. Assigned Tasks

Ziad Marzouk was responsible for:

- Task 7: Logout.
- Task 16: Place an Order.

These tasks are both critical to user session and user action flow:

- Logout safely ends authentication.
- Place Order creates the main business transaction in the system.

## 2. Task 7: Logout

## 2.1 Task Objective

The objective of Task 7 was to securely log out a user or admin.

The logout flow had to:

- Require an active session.
- Clear remember-me token from the database.
- Clear all session variables.
- Destroy the session cookie.
- Destroy the server-side session.
- Clear the remember-me cookie.
- Redirect to login with a logout confirmation.

## 2.2 File Responsible

```text
auth/logout.php
```

## 2.3 Code Deep-Dive

### Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- Defines application context.
- Starts session.
- Loads auth helpers.
- Loads database connection.

Why database is needed:

- Logout clears the remember token stored in the `users` table.

### Require Logged-In User

```php
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
```

Explanation:

- If no user is logged in, there is nothing to log out.
- Redirects guest to login.

### Get User ID

```php
$logoutUserId = (int) ($_SESSION['user_id'] ?? 0);
```

Explanation:

- Reads logged-in user's ID from session.
- Casts it to integer.

### Clear Remember Token in Database

```php
if ($logoutUserId > 0) {
    $emptyToken  = null;
    $stmtClear   = $conn->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
    $stmtClear->bind_param('si', $emptyToken, $logoutUserId);
    $stmtClear->execute();
    $stmtClear->close();
}
```

Explanation:

- Sets `remember_token` to NULL for this user.
- Uses prepared statement.
- Prevents old remember-me cookie from being valid after logout.

Why this matters:

- Logout should end both session login and remembered login.

### Clear Session Array

```php
$_SESSION = [];
```

Explanation:

- Removes all session variables in PHP memory.
- Clears `user_id`, `role`, `user_name`, and CSRF token.

### Destroy Session Cookie

```php
if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}
```

Explanation:

- Checks if PHP sessions use cookies.
- Gets current session cookie settings.
- Expires the session cookie in the browser.

Why use existing cookie params:

- It matches the cookie path/domain used when session was created.

### Destroy Server-Side Session

```php
session_destroy();
```

Explanation:

- Destroys session data on the server.
- Completes logout cleanup.

### Clear Remember-Me Cookie

```php
setcookie('remember_user', '', time() - 3600, '/');
```

Explanation:

- Expires remember-me cookie.
- Browser should remove it.

### Redirect

```php
header('Location: login.php?logged_out=1');
exit;
```

Explanation:

- Sends user back to login.
- Query string allows login page to show logout success message.

## 2.4 Design Decisions for Task 7

### Why clear database remember token?

If only the cookie was cleared, a copied old token could still work. Clearing the database token invalidates remembered login server-side.

### Why clear both session array and session cookie?

Session data exists in two places:

- Server-side session storage.
- Browser cookie containing session ID.

Both should be cleared.

### Why redirect after logout?

Logout has no UI. It performs an action and redirects to login.

## 2.5 Alternatives for Task 7

### Alternative: Only Call `session_destroy()`

Why not chosen:

- It would not clear remember-me token.
- It may leave browser cookie until expiry.

### Alternative: Logout by GET Link With No Server Cleanup

Why not chosen:

- Server-side cleanup is necessary.
- Remember-me tokens must be invalidated.

### Alternative: Keep Remember-Me After Logout

Why not chosen:

- User intentionally logged out.
- Auto-login after logout would be confusing and insecure.

## 2.6 Dependencies for Task 7

Task 7 depends on:

- Task 1 for `users.remember_token`.
- Task 2 for database connection.
- Task 4 for `isLoggedIn()`.
- Task 6 because login creates sessions and remember-me tokens.

Task 7 supports:

- Security.
- Session lifecycle.
- Testing logout and remember-me clearing.

---

## 3. Task 16: Place an Order

## 3.1 Task Objective

Task 16 implements the main customer business action: placing an order.

The order page must:

- Be accessible only to normal users.
- Redirect admins away from customer order flow.
- Show available menu items.
- Allow optional pre-selected item from menu page.
- Validate CSRF on POST.
- Validate selected item exists and is available.
- Validate quantity.
- Calculate total price server-side.
- Insert order into `orders`.
- Store user ID from session.
- Redirect to My Orders after success.

## 3.2 File Responsible

```text
user/place_order.php
```

## 3.3 Code Deep-Dive

### Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- Defines app context.
- Starts session.
- Loads auth helpers.
- Loads database connection.

### User-Only Access

```php
requireUser();
```

Explanation:

- Requires login.
- Blocks guests.
- Redirects admins to admin dashboard.

Why not allow admins:

- Admins manage orders; they should not create customer orders through the user flow.

### CSRF Validation

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('place_order.php');
}
```

Explanation:

- Any POST order submission must include valid CSRF token.
- Invalid requests are rejected before order validation and insert.

Why this matters:

- Placing an order changes database state.

### Fetch Available Items

```php
$stmtItems = $conn->prepare(
    'SELECT id, name, price FROM menu_items WHERE is_available = 1 ORDER BY name ASC'
);
$stmtItems->execute();
$resultItems    = $stmtItems->get_result();
$availableItems = $resultItems->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();
```

Explanation:

- Fetches only available items.
- Provides dropdown options.
- Includes item price for client-side preview only.

Important:

- The price in dropdown is not trusted for final total.

### Form State

```php
$errors         = [];
$selectedItemId = 0;
$formQuantity   = 1;
$formNotes      = '';
$preSelectError = '';
```

Explanation:

- Stores validation errors.
- Tracks selected item.
- Defaults quantity to 1.
- Preserves notes after validation failure.
- Stores preselection error.

### GET Preselection

```php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['item_id'])) {
    $requestedId = (int) $_GET['item_id'];
```

Explanation:

- User may click `Order This` from menu page.
- The selected item ID is passed in URL.

### Verify Preselected Item

```php
$stmtCheck = $conn->prepare('SELECT id FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1');
$stmtCheck->bind_param('i', $requestedId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
```

Explanation:

- Checks that item exists and is available.
- Prevents preselecting hidden or invalid items.

If valid:

```php
$selectedItemId = $requestedId;
```

If invalid:

```php
$preSelectError = 'The selected item is no longer available. Please choose from the menu below.';
```

### Reading POST Values

```php
$rawItemId    = trim($_POST['menu_item_id'] ?? '');
$rawQuantity  = trim($_POST['quantity']     ?? '');
$rawNotes     = trim($_POST['notes']        ?? '');
$verifiedItem = null;
```

Explanation:

- Reads item, quantity, and notes.
- Notes are optional.
- `$verifiedItem` will store trusted item data from database.

### Preserving Form Values

```php
$selectedItemId = (int) $rawItemId;
$formQuantity   = (int) $rawQuantity;
$formNotes      = htmlspecialchars($rawNotes, ENT_QUOTES, 'UTF-8');
```

Explanation:

- Keeps form filled after validation error.
- Escapes notes before printing back into textarea.

### Item Validation

```php
if ($rawItemId === '' || !is_numeric($rawItemId) || (int) $rawItemId <= 0) {
    $errors['item'] = 'Please select a menu item.';
} else {
    $stmtVerify = $conn->prepare('SELECT id, name, price FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1');
    $stmtVerify->bind_param('i', $selectedItemId);
    $stmtVerify->execute();
    $resultVerify = $stmtVerify->get_result();
    $verifiedItem = $resultVerify->fetch_assoc();
    $stmtVerify->close();
    if (!$verifiedItem) {
        $errors['item'] = 'The selected item is not available. Please choose a different item.';
    }
}
```

Explanation:

- Item ID must be present and numeric.
- System verifies the item exists and is available.
- Fetches trusted price from database.

Why fetch price here:

- Client-submitted prices can be manipulated.
- Database price is authoritative.

### Quantity Validation

```php
if ($rawQuantity === '' || !ctype_digit($rawQuantity)) {
    $errors['quantity'] = 'Please enter a valid quantity.';
} else {
    $qty = (int) $rawQuantity;
    if ($qty < 1 || $qty > 20) {
        $errors['quantity'] = 'Quantity must be between 1 and 20.';
    }
}
```

Explanation:

- Quantity must contain digits only.
- Minimum is 1.
- Maximum is 20.

Why limit quantity:

- Prevents invalid and excessive orders.
- Keeps demo business logic realistic.

### Total Price Calculation

```php
$quantity   = (int) $rawQuantity;
$totalPrice = round((float) $verifiedItem['price'] * $quantity, 2);
```

Explanation:

- Converts quantity to integer.
- Uses database item price.
- Rounds total to 2 decimals.

Defense point:

- This protects against client-side price manipulation.

### Trusted Insert Values

```php
$notes      = $rawNotes !== '' ? $rawNotes : null;
$userId     = (int) $_SESSION['user_id'];
$itemId     = (int) $verifiedItem['id'];
$status     = 'pending';
```

Explanation:

- Notes may be null.
- User ID comes from session.
- Item ID comes from verified database item.
- Status always starts as pending.

Why not accept user ID from POST:

- Users could order as someone else.
- Session is the trusted identity source.

Why status starts pending:

- Admin must process it later.

### Insert Order

```php
$stmtInsert = $conn->prepare(
    'INSERT INTO orders (user_id, menu_item_id, quantity, total_price, status, notes)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmtInsert->bind_param('iiidss', $userId, $itemId, $quantity, $totalPrice, $status, $notes);
$stmtInsert->execute();
$stmtInsert->close();
```

Explanation:

- Inserts order using prepared statement.
- Binding types:
  - `i`: user ID.
  - `i`: item ID.
  - `i`: quantity.
  - `d`: total price.
  - `s`: status.
  - `s`: notes.

Why prepared statement:

- Prevents SQL injection through notes or IDs.

### Success Redirect

```php
setFlashMessage('success', 'Your order has been placed successfully!');
header('Location: my_orders.php');
exit;
```

Explanation:

- Shows success message on order history page.
- Prevents duplicate order on refresh.

### Form Rendering

The form includes:

- CSRF token.
- Menu item dropdown.
- Quantity input.
- Price preview.
- Notes textarea.
- Submit button.

Important:

- If no items are available, submit button is disabled.

### Price Preview

The dropdown includes `data-price`:

```php
data-price="<?= number_format((float) $menuItem['price'], 2, '.', '') ?>"
```

Explanation:

- JavaScript uses this for estimated total.
- Server does not trust it for final price.

## 3.4 Design Decisions for Task 16

### Why item must be verified on POST?

The item could become unavailable after page load. The system must re-check before inserting order.

### Why total calculated server-side?

Client-side values can be changed. Server-side calculation protects price integrity.

### Why status is always pending?

New orders need admin review. Users cannot confirm their own orders.

### Why quantity maximum is 20?

It prevents unrealistic large orders and keeps validation simple.

### Why notes are optional?

Special instructions are useful but should not be required.

## 3.5 Alternatives for Task 16

### Alternative: Shopping Cart

A cart could allow multiple items per order.

Why not chosen:

- The schema uses one `menu_item_id` per order.
- Cart would require more complex database design.

### Alternative: Client Sends Total Price

Why not chosen:

- User can manipulate browser values.
- Server must calculate trusted totals.

### Alternative: Allow Guest Checkout

Why not chosen:

- Orders must link to `users.id`.
- User history requires authenticated identity.

### Alternative: Allow Admins to Place Orders

Why not chosen:

- Admins and users have separate roles.
- Admin order creation is outside the requirement.

## 3.6 Dependencies for Task 16

Task 16 depends on:

- Task 1 for `orders` and `menu_items`.
- Task 2 for database connection.
- Task 3 for form styling and price preview JavaScript.
- Task 4 for `requireUser()`, CSRF, flash messages, header, footer.
- Task 6 for user login.
- Task 10 for menu item creation.
- Task 15 because users browse menu before ordering.

Task 16 supports:

- Task 17 because My Orders displays created orders.
- Task 14 because Admin Orders displays and processes created orders.

## 4. Ziad Marzouk Defense Questions and Answers

### Q1: Why clear remember token on logout?

Because logout should invalidate automatic login, not just the current PHP session.

### Q2: Why destroy both session data and session cookie?

Session data exists on the server, while the browser stores the session ID cookie. Both must be cleared.

### Q3: Why does order placement use `requireUser()`?

Only normal authenticated users should place orders. Admins are redirected to the admin dashboard.

### Q4: Why fetch item price from database during POST?

Because prices in HTML or JavaScript can be manipulated. The database is trusted.

### Q5: Why validate item availability again during POST?

An item might become unavailable after the page loads. The server must check current availability.

### Q6: Why is order status set to pending?

A new order requires admin processing. Users should not decide final status.

### Q7: Why use CSRF protection on order placement?

Placing an order changes database state. CSRF prevents another website from forcing a logged-in user to place an order.

## 5. Ziad Marzouk Summary

Ziad Marzouk handled the end of the session lifecycle and the main user transaction.

Task 7 delivered:

- Secure logout.
- Remember token clearing.
- Session cleanup.
- Cookie cleanup.
- Login redirect.

Task 16 delivered:

- User-only order form.
- CSRF-protected order creation.
- Item availability verification.
- Quantity validation.
- Server-side total calculation.
- Prepared statement insert.
- Redirect to order history.

In defense, Ziad Marzouk should emphasize that logout protects session security, and order placement protects business integrity by trusting only server-side session and database values.


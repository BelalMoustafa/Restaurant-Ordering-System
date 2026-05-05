# Hassan Defense Notes

## 1. Assigned Tasks

Hassan joined the team as the 11th member with the role:

```text
Security & Backend Developer
```

Hassan was responsible for:

- QA & Security Implementation.
- CSRF helper logic in `includes/auth.php`.
- XSS protection review and `htmlspecialchars()` fixes across the project.
- Designing the End-to-End testing flow in `docs/QA_Testing_Flow.md`.
- Assisting on Task 16: Place Order.

For Task 16, Hassan co-developed the backend validation with Ziad Marzouk:

- Ziad Marzouk handled the UI flow and session-based user flow.
- Hassan handled the critical backend validation that protects order integrity.

Hassan's most important backend contribution was making sure the order price and total are trusted server-side values, not values that can be manipulated from the browser.

---

## 2. QA & Security Implementation

## 2.1 Task Objective

The objective of Hassan's QA and security work was to make sure the system is safe against common beginner-level web vulnerabilities and ready for a university project defense.

His work focused on:

- CSRF protection.
- XSS protection.
- Secure backend validation.
- End-to-end manual testing documentation.
- Making sure every critical user journey can be demonstrated and verified.

The main files connected to this responsibility are:

```text
includes/auth.php
docs/QA_Testing_Flow.md
user/place_order.php
```

Other affected files include pages that output dynamic data, such as:

```text
auth/register.php
auth/login.php
admin/menu_items/index.php
admin/menu_items/create.php
admin/menu_items/edit.php
admin/orders/index.php
admin/orders/view.php
index.php
user/menu.php
user/my_orders.php
user/place_order.php
```

## 2.2 Security Problem: CSRF

CSRF means Cross-Site Request Forgery.

It happens when a malicious website tricks a logged-in user into submitting an unwanted request to the real application.

Example:

- An admin is logged in.
- A malicious website secretly submits a POST request to delete a menu item.
- If the application does not check a CSRF token, the request may succeed.

State-changing actions in this project include:

- Registering an account.
- Logging in.
- Creating a menu item.
- Editing a menu item.
- Deleting a menu item.
- Uploading a PDF.
- Placing an order.
- Updating order status.

Hassan's CSRF work protects these actions.

## 2.3 `csrfToken()` Code Deep-Dive

File:

```text
includes/auth.php
```

Function:

```php
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}
```

### Logical Block 1: Function Return Type

```php
function csrfToken(): string
```

Explanation:

- The function name is `csrfToken`.
- It returns a string.
- The return type makes the function contract clear.

Defense point:

- A CSRF token must be text because it is placed inside an HTML hidden input.

### Logical Block 2: Check Session Token

```php
if (empty($_SESSION['csrf_token'])) {
```

Explanation:

- The function checks whether a CSRF token already exists in the current session.
- If it exists, the same token can be reused.
- If it does not exist, a new token is generated.

Why session storage:

- The server must remember the real token.
- The browser sends a copy back through the form.
- The server compares both values.

### Logical Block 3: Generate Secure Token

```php
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

Explanation:

- `random_bytes(32)` creates 32 cryptographically secure random bytes.
- `bin2hex()` converts those bytes into a safe hexadecimal string.
- 32 bytes becomes 64 hex characters.

Why not use `rand()`:

- `rand()` is not cryptographically secure.
- CSRF tokens must be unpredictable.

Why `bin2hex()`:

- Binary data may contain characters that are unsafe in HTML.
- Hexadecimal output is safe to store and print.

### Logical Block 4: Return Token

```php
return $_SESSION['csrf_token'];
```

Explanation:

- Returns the existing or newly generated token.
- Forms use this value in a hidden field.

Example form usage:

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Why escape token output:

- Even generated values should be escaped before HTML output.
- It keeps output rules consistent.

## 2.4 `requireValidCsrf()` Code Deep-Dive

File:

```text
includes/auth.php
```

Function:

```php
function requireValidCsrf(string $redirectTo): void
{
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && (
            empty($_POST['csrf_token'])
            || !hash_equals(csrfToken(), $_POST['csrf_token'])
        )
    ) {
        setFlashMessage('danger', 'Invalid request token. Please try again.');
        header('Location: ' . $redirectTo);
        exit;
    }
}
```

### Logical Block 1: Redirect Parameter

```php
function requireValidCsrf(string $redirectTo): void
```

Explanation:

- The function accepts a redirect path.
- If token validation fails, the user is redirected to that path.
- It returns nothing because it either allows execution to continue or stops the request.

Why redirect path is flexible:

- Different pages need different fallback locations.
- Example: create page redirects to `create.php`.
- Order view redirects to `view.php?id=...`.

### Logical Block 2: POST-Only Validation

```php
$_SERVER['REQUEST_METHOD'] === 'POST'
```

Explanation:

- CSRF validation is required for POST requests.
- GET requests should not change state.

Defense point:

- State-changing forms use POST, so CSRF checks focus on POST actions.

### Logical Block 3: Missing Token Check

```php
empty($_POST['csrf_token'])
```

Explanation:

- If the submitted form has no CSRF token, the request is invalid.
- This catches forged requests and broken forms.

### Logical Block 4: Token Comparison

```php
!hash_equals(csrfToken(), $_POST['csrf_token'])
```

Explanation:

- `csrfToken()` returns the real session token.
- `$_POST['csrf_token']` is the submitted token.
- `hash_equals()` safely compares both.

Why `hash_equals()`:

- It is safer than `===` for secret token comparison.
- It avoids timing-based comparison weaknesses.

### Logical Block 5: Reject Invalid Request

```php
setFlashMessage('danger', 'Invalid request token. Please try again.');
header('Location: ' . $redirectTo);
exit;
```

Explanation:

- Stores a user-friendly error message.
- Redirects back to a safe page.
- Stops execution immediately.

Why `exit` is critical:

- Without `exit`, the protected action might continue after redirect header.
- Security functions must stop execution after failure.

## 2.5 Where CSRF Was Applied

Hassan's CSRF helper logic is used by pages such as:

```text
auth/register.php
auth/login.php
admin/menu_items/create.php
admin/menu_items/edit.php
admin/upload_menu_pdf.php
admin/orders/view.php
user/place_order.php
```

Delete flow also uses CSRF validation in:

```text
admin/menu_items/delete.php
```

Example hidden input:

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Example backend validation:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('place_order.php');
}
```

Defense point:

- The hidden input proves the form came from the application session.
- The backend validation proves the submitted token matches the session token.

---

## 3. XSS Protection and `htmlspecialchars()` Fixes

## 3.1 Security Problem: XSS

XSS means Cross-Site Scripting.

It happens when user-controlled text is printed into the page as executable HTML or JavaScript.

Example dangerous input:

```html
<script>alert('xss')</script>
```

If printed directly, the browser may execute it.

Hassan helped review and apply escaping rules so dynamic values are displayed safely as text.

## 3.2 Main Protection Function

The project uses:

```php
htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
```

Explanation:

- Converts `<` into `&lt;`.
- Converts `>` into `&gt;`.
- Converts quotes into safe HTML entities.
- Uses UTF-8 encoding.

Why `ENT_QUOTES`:

- Escapes both single and double quotes.
- Important for values inside HTML attributes.

Why `UTF-8`:

- Matches the project/database character set.
- Prevents encoding confusion.

## 3.3 Examples of Escaped Output

### Escaping User Name

```php
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Guest', ENT_QUOTES, 'UTF-8');
```

Purpose:

- Prevents unsafe names from breaking HTML.

### Escaping Menu Item Names

```php
<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
```

Purpose:

- Menu item names are dynamic database values.
- They must be safe before display.

### Escaping Menu Descriptions

```php
$displayDesc = mb_strlen($desc) > 100
    ? htmlspecialchars(mb_substr($desc, 0, 100), ENT_QUOTES, 'UTF-8') . '&hellip;'
    : htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
```

Purpose:

- Prevents stored XSS through menu descriptions.
- Keeps long descriptions short.

### Escaping Order Notes

```php
<?= htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8') ?>
```

Purpose:

- Users can type order notes.
- Notes must never be printed raw.

### Escaping Attribute Values

```php
title="<?= htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8') ?>"
```

Purpose:

- Attribute contexts are sensitive.
- Quotes must be escaped.

## 3.4 Defense Explanation for XSS Fixes

The project uses a simple rule:

```text
Any dynamic value printed into HTML must be escaped.
```

This includes values from:

- Database.
- Session.
- Form input.
- Uploaded file paths.
- Query results.

Defense point:

- We do not assume admin-entered data is safe.
- Even admin-created descriptions are escaped because stored XSS can affect other users.

---

## 4. End-to-End Testing Flow

## 4.1 Task Objective

Hassan designed the End-to-End testing flow to help the team verify the complete system before defense.

File:

```text
docs/QA_Testing_Flow.md
```

The document covers:

- Setup pre-requisites.
- Default seeded accounts.
- Agile user stories.
- Guest browsing.
- Registration.
- Login.
- Remember Me.
- Admin menu management.
- Image uploads.
- PDF uploads.
- User order placement.
- Admin order processing.
- CSRF testing.
- XSS testing.
- Authorization testing.
- Logout testing.

## 4.2 Why QA Flow Was Needed

The project has many connected features.

Example chain:

```text
Admin creates item -> User sees item -> User places order -> Admin processes order -> User sees updated status
```

If one part breaks, the full system demo fails.

The QA flow gives testers a repeatable checklist.

## 4.3 Golden Flow Design

Hassan organized the QA document into scenarios:

1. Guest Browsing, Registration, and Login.
2. Admin Menu Management.
3. User Browsing and Order Placement.
4. Admin Order Processing.
5. Security and Edge Case Testing.

Each step uses:

```text
Action:
Expected Result:
```

Why this format:

- It is clear for manual testers.
- It helps the defense team demonstrate expected behavior.
- It separates what the tester does from what the system must do.

## 4.4 Security Test Coverage

The QA flow includes tests for:

- User trying to access admin pages.
- Guest trying to access protected pages.
- Admin trying to access user ordering flow.
- XSS input in descriptions and notes.
- SQL injection attempts.
- Missing CSRF token.
- Modified CSRF token.
- Invalid upload files.
- Logout session clearing.
- Remember-me cookie clearing.

Defense point:

- The QA document proves the team did not only build features; the team also tested security and edge cases.

---

## 5. Assisting on Task 16: Place Order

## 5.1 Task Objective

Task 16 is one of the most important backend features because it creates an order.

File:

```text
user/place_order.php
```

Ziad Marzouk handled:

- User interface flow.
- Session/user access logic.
- Form display.
- Redirect to My Orders.

Hassan handled the critical backend validation:

- Validating CSRF before processing POST.
- Verifying selected item exists.
- Verifying selected item is available.
- Fetching true item price directly from the database.
- Validating quantity boundaries.
- Calculating total price securely server-side.
- Ensuring the database insert uses trusted values.

## 5.2 Why Task 16 Is Security-Critical

Order placement involves money-like values.

The user sees prices in the browser, but browser data cannot be trusted.

A malicious user could try to:

- Edit HTML in Inspect Element.
- Change a hidden input.
- Change dropdown `data-price`.
- Submit a fake item ID.
- Submit quantity `0`, negative values, or huge values.
- Submit a manipulated total price.

Hassan's backend validation prevents these attacks.

## 5.3 CSRF Validation in Place Order

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('place_order.php');
}
```

Explanation:

- Order creation only happens through POST.
- CSRF token must be valid before order processing continues.

Why this matters:

- A forged website should not be able to force a logged-in user to place an order.

## 5.4 Fetching Available Items for the Form

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

- The form dropdown contains only available items.
- The price is included for client-side preview.
- This query is not trusted for final order total after POST.

Defense point:

- The frontend price preview is for convenience only.
- Backend validation still fetches the real price again.

## 5.5 Reading Submitted Item and Quantity

```php
$rawItemId    = trim($_POST['menu_item_id'] ?? '');
$rawQuantity  = trim($_POST['quantity']     ?? '');
$rawNotes     = trim($_POST['notes']        ?? '');
$verifiedItem = null;
```

Explanation:

- Reads submitted item ID, quantity, and notes.
- `$verifiedItem` starts as null until the database confirms the item.

Security point:

- Values from `$_POST` are not trusted.
- They must be validated before use.

## 5.6 Validating Item ID

```php
if ($rawItemId === '' || !is_numeric($rawItemId) || (int) $rawItemId <= 0) {
    $errors['item'] = 'Please select a menu item.';
}
```

Explanation:

- Item must be present.
- Item must be numeric.
- Item must be positive.

Why:

- Prevents invalid IDs.
- Prevents string injection attempts like `1 OR 1=1`.

## 5.7 Fetching True Price From Database

```php
$stmtVerify = $conn->prepare('SELECT id, name, price FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1');
$stmtVerify->bind_param('i', $selectedItemId);
$stmtVerify->execute();
$resultVerify = $stmtVerify->get_result();
$verifiedItem = $resultVerify->fetch_assoc();
$stmtVerify->close();
```

Explanation:

- The system fetches the selected item directly from the database.
- It also checks `is_available = 1`.
- It retrieves the trusted price from the database.

Why this is critical:

- The user cannot control the database price.
- Even if the user changes the DOM price, the backend ignores it.
- This prevents Inspect Element price manipulation.

Defense example:

If the browser says:

```text
data-price="0.01"
```

but the database says:

```text
15.75
```

the backend uses:

```text
15.75
```

## 5.8 Rejecting Unavailable Items

```php
if (!$verifiedItem) {
    $errors['item'] = 'The selected item is not available. Please choose a different item.';
}
```

Explanation:

- If no item is found, the order is rejected.
- This covers deleted, hidden, or invalid items.

Why:

- An item could become unavailable after the page loads.
- Server must check current database state.

## 5.9 Validating Quantity Boundaries

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

- Quantity must not be empty.
- Quantity must contain digits only.
- Quantity must be at least 1.
- Quantity must not exceed 20.

Why `ctype_digit()`:

- Rejects decimals.
- Rejects negative values.
- Rejects text.

Why max 20:

- Prevents unrealistic or abusive quantities.
- Keeps order size controlled.

## 5.10 Secure Total Price Calculation

```php
$quantity   = (int) $rawQuantity;
$totalPrice = round((float) $verifiedItem['price'] * $quantity, 2);
```

Explanation:

- Quantity comes from validated input.
- Price comes from trusted database result.
- Total is calculated on the server.
- Result is rounded to 2 decimals.

Why this protects the system:

- User cannot submit fake total price.
- User cannot reduce item price through DOM changes.
- User cannot skip server calculation.

Defense point:

- This is one of the strongest examples of backend trust boundaries in the project.

## 5.11 Trusted Insert Values

```php
$notes      = $rawNotes !== '' ? $rawNotes : null;
$userId     = (int) $_SESSION['user_id'];
$itemId     = (int) $verifiedItem['id'];
$status     = 'pending';
```

Explanation:

- Notes are optional.
- User ID comes from session, not from POST.
- Item ID comes from verified database row.
- Status starts as `pending`.

Why user ID from session:

- Prevents users from placing orders for another user.

Why status fixed:

- Users should not control order processing status.

## 5.12 Database Insert

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

- Inserts trusted values into the `orders` table.
- Uses MySQLi OO prepared statement.
- Binding types:
  - `i`: user ID.
  - `i`: item ID.
  - `i`: quantity.
  - `d`: total price.
  - `s`: status.
  - `s`: notes.

Why prepared statements:

- Prevent SQL injection through notes or manipulated form values.

## 5.13 Success Flow

```php
setFlashMessage('success', 'Your order has been placed successfully!');
header('Location: my_orders.php');
exit;
```

Explanation:

- Shows success message.
- Redirects to order history.
- Prevents duplicate insertion on refresh.

---

## 6. Design Decisions

## 6.1 Why Central CSRF Helpers?

Central helpers are better than writing token logic separately on every page.

Benefits:

- Consistency.
- Less duplicated code.
- Easier review.
- Easier defense explanation.

## 6.2 Why `random_bytes()`?

CSRF tokens must be unpredictable.

`random_bytes()` is cryptographically secure and suitable for security tokens.

## 6.3 Why `hash_equals()`?

It compares secret values safely.

This is better than normal string comparison for security-sensitive tokens.

## 6.4 Why Escape on Output Instead of Input?

The project mainly escapes when printing values.

Reason:

- The same data may be used in different contexts.
- Escaping at output ensures the value is safe for the specific HTML context.

## 6.5 Why Fetch Price From Database on POST?

The browser cannot be trusted.

The database is the source of truth for item prices.

This prevents:

- DOM manipulation.
- Fake totals.
- Hidden input tampering.
- JavaScript modification.

## 6.6 Why Validate Quantity Server-Side?

HTML `min` and `max` can be bypassed.

Server-side validation ensures the rule is always enforced.

---

## 7. Alternatives

## 7.1 Alternative: Generate CSRF Token Per Form

Possible approach:

- Every form gets a unique token.

Why not chosen:

- More complex.
- A session-level token is enough for this project.
- Easier for team members to understand and explain.

## 7.2 Alternative: Use JavaScript Only for Price Calculation

Why not chosen:

- JavaScript can be changed by the user.
- Backend calculation is required for security.

## 7.3 Alternative: Send Price as Hidden Input

Why not chosen:

- Hidden inputs are still user-controlled.
- Inspect Element can change them.

## 7.4 Alternative: Allow Any Quantity

Why not chosen:

- Could create unrealistic orders.
- Could cause huge totals.
- Quantity boundaries make the business rule clear.

## 7.5 Alternative: Store Raw HTML and Trust Admin/User Input

Why not chosen:

- Leads to XSS risk.
- All dynamic output must be escaped.

## 7.6 Alternative: Use Framework Security Middleware

Why not chosen:

- Frameworks are forbidden by the project requirement.
- The team needed to implement security manually in Core PHP.

---

## 8. Dependencies

## 8.1 Depends on Task 1: Database Schema

Hassan's backend validation depends on:

```text
menu_items.price
menu_items.is_available
orders.total_price
orders.quantity
users.id
```

Without the schema:

- The backend cannot verify item price.
- Orders cannot be inserted.
- User identity cannot connect to orders.

## 8.2 Depends on Task 2: Database Connection

The backend validation uses:

```php
$conn->prepare(...)
```

This depends on:

```text
config/db.php
```

## 8.3 Depends on Task 4: Shared Auth Helpers

Hassan's CSRF functions live in:

```text
includes/auth.php
```

They are used by:

- Registration.
- Login.
- Menu item create/edit/delete.
- PDF upload.
- Place order.
- Order status update.

## 8.4 Depends on Task 10 and Task 15

Task 16 depends on menu items created by admins and displayed to users.

Flow:

```text
Admin creates menu item -> User sees item -> User places order
```

## 8.5 Supports Task 14 and Task 17

Hassan's secure order insert supports:

- Admin order processing in Task 14.
- User order history in Task 17.

If order creation is wrong, both later views show wrong data.

## 8.6 Supports Belal's Final Review

Hassan's QA testing flow supports the team leader's final sign-off by documenting exactly how to test:

- Features.
- Security.
- Edge cases.
- End-to-end journeys.

---

## 9. Hassan Defense Questions and Answers

### Q1: What was Hassan's main role?

Hassan was the Security & Backend Developer. He focused on CSRF protection, XSS fixes, secure order backend validation, and the QA testing flow.

### Q2: Why is CSRF protection needed?

Because logged-in users and admins can be tricked into submitting unwanted POST requests from another website. CSRF tokens prove the form came from the real application session.

### Q3: Why use `random_bytes(32)` for CSRF?

It creates cryptographically secure random data, making the token unpredictable.

### Q4: Why use `hash_equals()`?

It safely compares the session token with the submitted token.

### Q5: What is the most important security fix in Place Order?

Fetching the real item price from the database during POST and calculating the total server-side.

### Q6: Why not trust the price shown in the browser?

The browser can be modified with Inspect Element. Any price in HTML or JavaScript is user-controlled.

### Q7: How does the system prevent quantity manipulation?

It validates quantity server-side with `ctype_digit()` and enforces the range 1 to 20.

### Q8: How does the system prevent XSS?

It escapes dynamic output with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

### Q9: Why did Hassan create the QA testing flow?

To give the team a repeatable defense checklist that proves the whole system works from guest browsing through admin order processing and security edge cases.

### Q10: How does Hassan's work connect to other developers?

His CSRF helpers are used by multiple developers' pages. His order validation supports Ziad Marzouk's Task 16 and feeds correct data into Soud Karim's My Orders page and Ziad Yasser's Admin Orders page.

---

## 10. Hassan Summary

Hassan's contribution strengthened the system's security and backend correctness.

He implemented or helped implement:

- `csrfToken()`.
- `requireValidCsrf()`.
- XSS escaping through `htmlspecialchars()`.
- End-to-End QA testing documentation.
- Secure item verification in Task 16.
- Secure database price fetching.
- Quantity boundary validation.
- Server-side total price calculation.
- Trusted order insert values.

In defense, Hassan should emphasize one key principle:

```text
Never trust the browser. Trust the session and the database.
```

This principle explains why CSRF tokens, `htmlspecialchars()`, prepared statements, database price fetching, and server-side total calculation are all necessary.

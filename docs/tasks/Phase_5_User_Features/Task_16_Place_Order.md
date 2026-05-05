# Task 16 - Place an Order

## Assignment

| Field | Detail |
|-------|--------|
| **Assigned To** | Ziad Marzouk & Hassan |
| **Reviewed By** | Belal Moustafa |
| **Phase** | Phase 5 - User Features |
| **Status** | Completed |
| **Depends On** | Task 15 (menu must be browsable) |
| **Blocks** | Task 17 (orders must exist before order history can display data) |

---

## Responsibility Split

| Developer | Responsibility |
|-----------|----------------|
| **Ziad Marzouk** | Built the order page UI, menu selection flow, session-based user flow, pre-selected item behavior, and form display logic. |
| **Hassan** | Built the critical backend validation: CSRF protection, true database price fetching, quantity boundary validation, secure total price calculation, database insertion, and security fixes. |

---

## Objective

Build the order placement page at:

```text
user/place_order.php
```

Users select an available menu item, choose a quantity, add optional notes, and submit the order. The order is inserted into the `orders` table with trusted server-side values.

---

## Page Requirements

## Access Control

- Use `requireUser()` so only regular users can place orders.
- Guests must be redirected to login.
- Admins must not use the user ordering flow.

## GET Request - Show the Form

The page must:

- Fetch all available menu items.
- Display a dropdown of available items.
- Support a pre-selected item from `menu.php`.
- Show item names and prices for user convenience.
- Include a CSRF hidden token.

If `item_id` is provided in the query string:

- Validate that the item exists.
- Validate that the item is available.
- Pre-select it in the dropdown if valid.
- Fall back safely if invalid or unavailable.

## Form Fields

| Field | Input Type | Details |
|-------|------------|---------|
| Select Item | select | Available menu items only |
| Quantity | number | Required, integer, min 1, max 20 |
| Special Notes | textarea | Optional |
| CSRF Token | hidden | Value from `csrfToken()` |

---

## Ziad Marzouk Scope

Ziad Marzouk handled the user-facing and session-flow parts:

- Page layout.
- Form structure.
- Dropdown display.
- Optional pre-selected menu item flow.
- Integration with shared header and footer.
- Redirect flow after successful order.
- User experience around selecting an item and quantity.

He ensured the page fits the normal user journey:

```text
User menu -> Place order -> My orders
```

---

## Hassan Scope

Hassan handled the backend validation and security-sensitive order logic:

- Validate CSRF before processing POST.
- Validate item ID.
- Verify the item exists and is available.
- Fetch the true item price from the database.
- Ignore any browser-submitted or DOM-modified price.
- Validate quantity boundaries.
- Calculate total price server-side.
- Use `$_SESSION['user_id']` for the order owner.
- Insert the order with MySQLi OO prepared statements.
- Apply XSS-safe output handling for dynamic values.

Security principle:

```text
Never trust the browser. Trust the session and the database.
```

---

## POST Handler Requirements

## Validation Order

1. Validate CSRF token.
2. Read submitted item ID, quantity, and notes.
3. Validate item ID is numeric and positive.
4. Fetch the selected item from the database where `is_available = 1`.
5. Reject the request if the item does not exist or is unavailable.
6. Validate quantity is an integer between 1 and 20.
7. Fetch the trusted item price from the database result.
8. Calculate total price server-side.
9. Insert the order using MySQLi OO prepared statement.
10. Redirect to `my_orders.php` with a success message.

---

## Critical Backend Price Validation

The order form may show prices in the browser for preview, but the backend must never trust browser values.

Attack example:

```text
User opens Inspect Element and changes data-price from 15.00 to 0.01.
```

Required defense:

```php
$stmtVerify = $conn->prepare(
    'SELECT id, name, price FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1'
);
$stmtVerify->bind_param('i', $selectedItemId);
$stmtVerify->execute();
$resultVerify = $stmtVerify->get_result();
$verifiedItem = $resultVerify->fetch_assoc();
$stmtVerify->close();
```

The backend must calculate:

```php
$totalPrice = round((float) $verifiedItem['price'] * $quantity, 2);
```

Reason:

- The database is the source of truth.
- DOM values are user-controlled.
- Hidden fields and JavaScript values can be changed.

---

## Quantity Validation

Quantity rules:

- Required.
- Digits only.
- Minimum 1.
- Maximum 20.
- No decimals.
- No negative values.
- No zero.

Example validation:

```php
if ($rawQuantity === '' || !ctype_digit($rawQuantity)) {
    $errors['quantity'] = 'Please enter a valid quantity.';
} else {
    $quantity = (int) $rawQuantity;
    if ($quantity < 1 || $quantity > 20) {
        $errors['quantity'] = 'Quantity must be between 1 and 20.';
    }
}
```

---

## Database Insert Requirement

Use Object-Oriented MySQLi prepared statements:

```php
$stmtInsert = $conn->prepare(
    'INSERT INTO orders (user_id, menu_item_id, quantity, total_price, status, notes)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmtInsert->bind_param('iiidss', $userId, $itemId, $quantity, $totalPrice, $status, $notes);
$stmtInsert->execute();
```

Required trusted values:

- `user_id` must come from `$_SESSION['user_id']`.
- `menu_item_id` must come from the verified database item.
- `quantity` must come from server-side validation.
- `total_price` must be calculated server-side.
- `status` must default to `pending`.
- `notes` must be inserted through prepared statement binding.

---

## Live Price Preview

The page may use JavaScript to show:

```text
Estimated Total: $X.XX
```

Important:

- This preview is for user experience only.
- The backend calculation is the real order total.

---

## Acceptance Criteria

- [x] `requireUser()` is used.
- [x] Guests cannot place orders.
- [x] Admins cannot use the user ordering flow.
- [x] CSRF token is present in the form.
- [x] CSRF token is validated before POST processing.
- [x] Available items are fetched with MySQLi OO prepared statements.
- [x] Item ID is validated.
- [x] Item availability is verified server-side.
- [x] True price is fetched from the database during POST.
- [x] Browser price values are not trusted.
- [x] Quantity is validated between 1 and 20.
- [x] Total price is calculated server-side.
- [x] `user_id` comes from session, not POST.
- [x] Insert uses MySQLi OO prepared statement.
- [x] Notes are safely handled through bound parameters.
- [x] Redirects to `my_orders.php` with a success message.
- [x] Belal Moustafa reviewed and approved the feature.

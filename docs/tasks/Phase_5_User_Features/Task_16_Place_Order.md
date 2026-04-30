# Task 16 — Place an Order

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Marzouk                        |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 5 — User Features             |
| **Status**         | Completed                           |
| **Depends On**     | Task 15 (menu must be browsable)    |
| **Blocks**         | Task 17 (orders must exist)         |

---

## Objective
Build the order placement page at `user/place_order.php`. Users select a menu item (or arrive with a pre-selected item from the menu page), choose a quantity, add optional notes, and submit the order. The order is inserted into the `orders` table.

---

## Deliverable
`user/place_order.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` at the top.

### GET Request — Show the Form
- If `$_GET['item_id']` is present:
  - Fetch that specific item from `menu_items` WHERE `id = ? AND is_available = 1` using a MySQLi prepared statement
  - Pre-select it in the item dropdown
  - If not found or unavailable, show an error and display the full dropdown
- Fetch all available items for the dropdown using a MySQLi prepared statement: `SELECT id, name, price FROM menu_items WHERE is_available = 1 ORDER BY name`

### Form Fields

| Field          | Input Type | Details                                                  |
|----------------|------------|----------------------------------------------------------|
| Select Item    | select     | Dropdown of all available items showing "Name — $Price"  |
| Quantity       | number     | Required, min 1, max 20, default 1                       |
| Special Notes  | textarea   | Optional, placeholder "Any special requests or allergies?" |

- Submit button: "Place Order" (`.btn .btn-primary`)
- Cancel link: "Back to Menu" → `menu.php`

### Live Price Preview (JavaScript)
Using `main.js`:
- When the user changes the item or quantity, calculate and display the estimated total:
  - Embed item prices as `data-price` attributes on each `<option>`
  - JS reads the selected option's `data-price`, multiplies by quantity, displays "Estimated Total: $X.XX"

### POST Handler — Validation
1. `item_id`: present, numeric, item exists in DB and is available — verified with a MySQLi prepared statement
2. `quantity`: present, integer, between 1 and 20

### POST Handler — Insert Order
1. Re-fetch the item price from DB using a MySQLi prepared statement (never trust the client-submitted price)
2. Calculate `total_price = $item['price'] * $quantity`
3. INSERT into `orders` using a MySQLi prepared statement:
   ```php
   $stmt = $conn->prepare(
       'INSERT INTO orders (user_id, menu_item_id, quantity, total_price, status, notes)
        VALUES (?, ?, ?, ?, ?, ?)'
   );
   $stmt->bind_param('iiidss', $userId, $itemId, $quantity, $totalPrice, $status, $notes);
   $stmt->execute();
   ```
   - `user_id` = `$_SESSION['user_id']` — never from POST data
   - `menu_item_id` = validated `$itemId`
   - `quantity` = validated `$quantity`
   - `total_price` = calculated server-side
   - `status` = `'pending'`
   - `notes` = sanitized notes (or null)
4. On success: `setFlashMessage('success', 'Your order has been placed successfully!')` and redirect to `my_orders.php`

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` called
- [ ] All DB queries use MySQLi prepared statements — not string interpolation
- [ ] Item price fetched from DB server-side — never from POST data
- [ ] `total_price` calculated server-side
- [ ] `user_id` taken from `$_SESSION` — never from POST data
- [ ] INSERT uses MySQLi prepared statement
- [ ] Quantity validated between 1 and 20
- [ ] Live price preview works via JS
- [ ] Redirect to `my_orders.php` with flash message on success
- [ ] Page uses `includes/header.php` and `includes/footer.php`

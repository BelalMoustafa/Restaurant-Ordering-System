# Task 14 — Admin: View All Orders

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Yasser                         |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Pending                             |
| **Depends On**     | Tasks 1–7 (Phase 5 Task 16 for real data) |
| **Blocks**         | Nothing directly                    |

---

## Objective
Build two admin order pages:
- `admin/orders/index.php` — lists all orders from all users
- `admin/orders/view.php` — shows the full detail of a single order

---

## Deliverables
- `admin/orders/index.php`
- `admin/orders/view.php`

---

## `admin/orders/index.php` Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.

### Data Query
Fetch all orders with a JOIN to get user name and menu item name:
```sql
SELECT orders.*, users.name AS user_name, menu_items.name AS item_name
FROM orders
JOIN users ON orders.user_id = users.id
JOIN menu_items ON orders.menu_item_id = menu_items.id
ORDER BY orders.created_at DESC
```
Use a MySQLi OO prepared statement.

### Table Columns

| Column       | Data                                                    |
|--------------|---------------------------------------------------------|
| Order #      | `orders.id`                                             |
| Customer     | `users.name`                                            |
| Item Ordered | `menu_items.name`                                       |
| Qty          | `orders.quantity`                                       |
| Total        | `orders.total_price` formatted as `$X.XX`               |
| Status       | Badge showing status — use CSS classes for pending/confirmed/cancelled |
| Date         | `orders.created_at` formatted as `d M Y, H:i`           |
| Actions      | "View Details" button → `view.php?id=X`                 |

### Status Badges
- `pending` → `.alert-info` style (black border)
- `confirmed` → `.alert-success` style (green)
- `cancelled` → `.alert-danger` style (red)

### Empty State
If no orders exist: "No orders have been placed yet."

---

## `admin/orders/view.php` Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.

### GET Request
- Read `$_GET['id']` — validate numeric, redirect to `index.php` if invalid
- Fetch the order with full JOIN (same query as above, plus `orders.notes`)
- If not found: flash error and redirect to `index.php`

### Display
Show all order details in a `.card`:
- Order ID, Customer Name, Customer Email (join to users table)
- Item Ordered, Quantity, Unit Price, Total Price
- Special Notes (or "None" if empty)
- Order Status (with badge)
- Order Date

### Status Update Form
Below the details, provide a small form to update the order status:
- `<select name="status">` with options: pending, confirmed, cancelled
- Pre-selected to current status
- Submit button: "Update Status" (`.btn .btn-primary .btn-sm`)
- Form POSTs to the same page (`view.php?id=X`)
- On POST: UPDATE `orders` SET `status = ?` WHERE `id = ?` using MySQLi OO prepared statement
- Flash success and redirect back to `view.php?id=X`

### Back Link
"← Back to All Orders" link at the top.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` on both pages
- [ ] JOIN query fetches user name and item name correctly
- [ ] Status badges styled correctly
- [ ] `view.php` shows all order fields including notes
- [ ] Status update uses MySQLi OO prepared statement
- [ ] Invalid ID handled gracefully on `view.php`
- [ ] Both pages use `includes/header.php` and `includes/footer.php`

# Task 17 — My Orders (Order History)

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Soud Karim                          |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 5 — User Features             |
| **Status**         | Pending                             |
| **Depends On**     | Task 16 (orders must exist)         |
| **Blocks**         | Nothing directly                    |

---

## Objective
Build the order history page at `user/my_orders.php`. Users can see all orders they have personally placed, with item names, quantities, totals, statuses, and dates. Users must ONLY see their own orders — never other users' orders.

---

## Deliverable
`user/my_orders.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` at the top.

### Data Query
Fetch only the logged-in user's orders using `$_SESSION['user_id']`:
```sql
SELECT orders.*, menu_items.name AS item_name, menu_items.image_path
FROM orders
JOIN menu_items ON orders.menu_item_id = menu_items.id
WHERE orders.user_id = ?
ORDER BY orders.created_at DESC
```
Use a MySQLi OO prepared statement with `$_SESSION['user_id']` as the bound parameter.

### Table Display

| Column       | Data                                                    |
|--------------|---------------------------------------------------------|
| Order #      | `orders.id`                                             |
| Item         | `menu_items.name`                                       |
| Qty          | `orders.quantity`                                       |
| Total        | `orders.total_price` formatted as `$X.XX`               |
| Status       | Status badge (same styling as admin orders view)        |
| Notes        | `orders.notes` or "—" if empty                          |
| Date         | `orders.created_at` formatted as `d M Y, H:i`           |

### Summary Row
Below the table, show the total amount spent:
```sql
SELECT SUM(total_price) FROM orders WHERE user_id = ?
```
Display as: "Total Spent: $X.XX"

### Empty State
If no orders: "You haven't placed any orders yet." with a "Browse Menu" button → `menu.php`

### Flash Messages
Display any flash message (e.g., the success message from Task 16) at the top.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` called
- [ ] Query uses `WHERE orders.user_id = ?` with `$_SESSION['user_id']` — never shows other users' orders
- [ ] MySQLi OO prepared statement used with bound parameter
- [ ] Status badges styled correctly
- [ ] Total spent calculated and displayed
- [ ] Empty state shown when no orders exist
- [ ] Flash message displayed
- [ ] Page uses `includes/header.php` and `includes/footer.php`

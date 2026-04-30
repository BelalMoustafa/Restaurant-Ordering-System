# Task 15 — User Dashboard & Menu Page

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Walid                          |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 5 — User Features             |
| **Status**         | Pending                             |
| **Depends On**     | Tasks 1–14                          |
| **Blocks**         | Task 16                             |

---

## Objective
Build two user-facing pages:
- `user/dashboard.php` — the user's home page after login
- `user/menu.php` — the full menu display with item cards, images, and prices

---

## Deliverables
- `user/dashboard.php`
- `user/menu.php`

---

## `user/dashboard.php` Requirements

### Access Control
- Call `requireLogin()` at the top. (Regular users AND admins can access this.)

### Content
- Welcome heading: `"Welcome, [User Name]"` using `$_SESSION['user_name']`
- A summary card showing the user's total number of orders: `SELECT COUNT(*) FROM orders WHERE user_id = ?`
- Quick action buttons:
  - "Browse Menu" → `menu.php`
  - "Place an Order" → `place_order.php`
  - "My Orders" → `my_orders.php`
- If a PDF menu exists at `uploads/pdfs/menu.pdf`, show a "Download PDF Menu" link

---

## `user/menu.php` Requirements

### Access Control
- Call `requireLogin()` at the top.

### Data Query
Fetch all available menu items grouped by category:
```sql
SELECT * FROM menu_items WHERE is_available = 1 ORDER BY category, name
```
Use a MySQLi OO prepared statement.

### Display
- Page heading: "Our Menu"
- Group items by `category` — display each category as a section with an `<h2>` heading
- Render items in a `.menu-grid` layout using `.menu-card` components
- Each card shows:
  - Item image (grayscale via CSS) or a placeholder div if no image
  - Category label (`.menu-card-category`)
  - Item name (`.menu-card-title`)
  - Description (truncated to 100 chars if long)
  - Price (`.menu-card-price`)
  - "Order This" button → `place_order.php?item_id=X` (`.btn .btn-primary .btn-sm`)

### PDF Download Link
If `uploads/pdfs/menu.pdf` exists, show a prominent link at the top: "Download Full Menu (PDF)"

### Empty State
If no items are available: "Our menu is currently being updated. Please check back soon."

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` called on both pages
- [ ] Dashboard shows correct order count for the logged-in user (uses `user_id` from session)
- [ ] Menu query filters by `is_available = 1`
- [ ] Items grouped by category with section headings
- [ ] `.menu-grid` and `.menu-card` CSS classes used correctly
- [ ] Images rendered with grayscale CSS filter
- [ ] "Order This" button passes `item_id` in the URL
- [ ] PDF download link shown conditionally
- [ ] Both pages use `includes/header.php` and `includes/footer.php`

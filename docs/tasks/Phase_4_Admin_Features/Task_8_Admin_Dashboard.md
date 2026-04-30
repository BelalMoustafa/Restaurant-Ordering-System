# Task 8 — Admin Dashboard

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Soud Karim                          |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Pending                             |
| **Depends On**     | Tasks 1–7                           |
| **Blocks**         | Nothing directly                    |

---

## Objective
Build the admin home page at `admin/dashboard.php`. This is the first page an admin sees after login. It displays summary statistics pulled live from the database and provides quick navigation links to all admin sections.

---

## Deliverable
`admin/dashboard.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the very top of the file.

### Statistics Section
Query the database for the following counts and display them in `.stats-grid` cards:
- **Total Menu Items** — `SELECT COUNT(*) FROM menu_items`
- **Available Items** — `SELECT COUNT(*) FROM menu_items WHERE is_available = 1`
- **Total Orders** — `SELECT COUNT(*) FROM orders`
- **Pending Orders** — `SELECT COUNT(*) FROM orders WHERE status = 'pending'`

Display each stat in a `.stat-card` with a `.stat-number` and `.stat-label`.

### Quick Links Section
Below the stats, render a `.card` with a "Quick Actions" title and the following links as `.btn` buttons:
- "Manage Menu Items" → `menu_items/index.php`
- "Add New Item" → `menu_items/create.php`
- "View All Orders" → `orders/index.php`
- "Upload PDF Menu" → `upload_menu_pdf.php`

### Welcome Message
Display a heading: `"Welcome back, [Admin Name]"` using `$_SESSION['user_name']`.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` are both called
- [ ] All 4 stat counts are fetched with separate MySQLi OO prepared statements
- [ ] Stats display correctly in the `.stats-grid` layout
- [ ] Quick action links are present and point to correct paths
- [ ] Admin name from session is displayed in the welcome message
- [ ] Page uses `includes/header.php` and `includes/footer.php`

# Task 9 — Menu Items: List & Read

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Abd El-Rahman Yasser                |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Pending                             |
| **Depends On**     | Tasks 1–7                           |
| **Blocks**         | Tasks 10, 11, 12 (they link back here) |

---

## Objective
Build the menu items listing page at `admin/menu_items/index.php`. This is the central hub for menu management — it shows all items in a table with thumbnail images, key details, and action buttons for Edit and Delete.

---

## Deliverable
`admin/menu_items/index.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.

### Page Header
- Heading: "Menu Items"
- A `.btn .btn-primary` button: "Add New Item" → links to `create.php`

### Data Table
Fetch all rows from `menu_items` ordered by `created_at DESC`. Display in a `.table`:

| Column Header | Data Source                                      |
|---------------|--------------------------------------------------|
| #             | Row number (not the DB id)                       |
| Image         | `<img>` tag using `image_path`, class `.img-thumbnail`. Show "No Image" text if null |
| Name          | `menu_items.name`                                |
| Category      | `menu_items.category`                            |
| Price         | `menu_items.price` formatted as `$X.XX`          |
| Available     | Green "Yes" or Red "No" based on `is_available`  |
| Actions       | "Edit" button → `edit.php?id=X`, "Delete" button → triggers delete form POST to `delete.php` |

### Delete Button
The Delete action must be a `<form>` with `method="POST"` pointing to `delete.php`, containing a hidden `<input name="id">` with the item's ID. The submit button has class `.btn .btn-danger .btn-sm`. The JS confirm dialog from `main.js` will intercept this.

### Empty State
If no menu items exist, show a centered message: "No menu items found. Add your first item." with a link to `create.php`.

### Flash Messages
Display any flash message from `getFlashMessage()` at the top of the content area.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` are both called
- [ ] All items fetched with a MySQLi OO prepared statement (use `$conn->prepare()` and `execute()` even without a WHERE clause)
- [ ] Thumbnail images render correctly; null image_path shows fallback text
- [ ] Delete is a POST form — not a GET link
- [ ] Empty state message is shown when table is empty
- [ ] Flash messages are displayed
- [ ] Page uses `includes/header.php` and `includes/footer.php`

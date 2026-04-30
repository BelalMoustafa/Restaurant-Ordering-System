# Task 12 — Menu Items: Delete

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Habiba                              |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Pending                             |
| **Depends On**     | Task 10 (items must exist to delete) |
| **Blocks**         | Nothing directly                    |

---

## Objective
Build the delete handler at `admin/menu_items/delete.php`. This is a POST-only action file with no visible UI. It deletes a menu item from the database and removes its associated image file from disk.

---

## Deliverable
`admin/menu_items/delete.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.

### POST-Only Enforcement
- If the request method is NOT POST, redirect to `index.php` immediately. This prevents accidental deletion via direct URL access.

### Delete Sequence
1. Read `$_POST['id']` — validate it is present and numeric
2. Fetch the item from `menu_items` WHERE `id = ?` using a MySQLi OO prepared statement
3. If item not found: set flash error "Item not found." and redirect to `index.php`
4. If item has an `image_path`:
   - Build the full server path: `$_SERVER['DOCUMENT_ROOT'] . '/restaurant_system/' . $image_path`
   - Check if the file exists with `file_exists()`
   - Delete it with `unlink()` if it exists
5. Execute DELETE from `menu_items` WHERE `id = ?` using a MySQLi OO prepared statement
6. Set flash message: `setFlashMessage('success', 'Menu item deleted successfully.')`
7. Redirect to `index.php`

### Error Handling
- Wrap the delete operation in a try/catch for database errors
- If deletion fails, set flash error and redirect to `index.php`

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` are both called
- [ ] GET requests are rejected — POST only
- [ ] Item fetched before deletion to retrieve `image_path`
- [ ] Image file deleted from disk using `unlink()` if it exists
- [ ] DELETE query uses MySQLi OO prepared statement
- [ ] Wrapped in try/catch for error handling
- [ ] Redirect to `index.php` with appropriate flash message
- [ ] No HTML output — pure action file

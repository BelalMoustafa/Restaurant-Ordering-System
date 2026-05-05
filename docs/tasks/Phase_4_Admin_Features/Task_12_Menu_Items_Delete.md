# Task 12 - Menu Items: Delete

## Assignment

| Field | Detail |
|-------|--------|
| **Assigned To** | Hamza |
| **Reviewed By** | Belal Moustafa |
| **Phase** | Phase 4 - Admin Features |
| **Status** | Completed |
| **Depends On** | Task 10 (items must exist before deletion) |
| **Blocks** | Nothing directly |

---

## Objective

Build the delete handler at:

```text
admin/menu_items/delete.php
```

This is a POST-only action file with no visible UI. It deletes a menu item only when deletion is safe and removes the associated image file from disk.

---

## Page Requirements

## Access Control

- Call `requireLogin()` at the top.
- Call `requireAdmin()` at the top.
- Guests and regular users must not be able to delete menu items.

## POST-Only Enforcement

- If the request method is not POST, redirect to `index.php`.
- Delete must never happen through a GET request.

## CSRF Protection

- Validate the submitted CSRF token before processing deletion.
- Reject missing or invalid tokens.
- Redirect with a flash error message if CSRF validation fails.

---

## Delete Sequence

1. Read `$_POST['id']`.
2. Validate that the ID is a positive integer.
3. Fetch the menu item using a MySQLi OO prepared statement.
4. If the item does not exist, redirect with an error message.
5. Count existing orders that reference the item.
6. If existing orders are found, block deletion and tell the admin to mark the item unavailable instead.
7. Delete the row from `menu_items` using a MySQLi OO prepared statement.
8. If deletion succeeds and an image exists, remove the image file from disk.
9. Redirect to `index.php` with a success message.

---

## Required Existing Orders Check

Before deleting a menu item, check:

```sql
SELECT COUNT(*) FROM orders WHERE menu_item_id = ?
```

If the count is greater than zero:

- Do not delete the item.
- Show a flash error.
- Redirect to the menu item list.

Reason:

- Existing orders are historical records.
- Deleting the item would damage order history.
- The database also protects this rule with `ON DELETE RESTRICT`.

---

## Database Requirements

All database actions must use Object-Oriented MySQLi prepared statements.

Required queries:

- Fetch item by ID.
- Count related orders.
- Delete item by ID.

Do not use:

- Raw SQL string interpolation.
- User-controlled values directly inside SQL.

---

## File Cleanup Requirement

If the menu item has an `image_path`:

1. Build the absolute path from the project root.
2. Check that the file exists.
3. Delete it with `unlink()`.

Image deletion must happen only after the database row is successfully deleted.

---

## Step-by-Step Instructions for Hamza

1. Create or update `admin/menu_items/delete.php`.
2. Start the session and include auth/database files before output.
3. Protect the action with `requireLogin()` and `requireAdmin()`.
4. Reject non-POST requests.
5. Validate CSRF token.
6. Validate the submitted item ID.
7. Fetch the menu item before deletion.
8. Count existing orders for that menu item.
9. Block deletion when orders exist.
10. Delete the menu item only when safe.
11. Delete the uploaded image file if it exists.
12. Redirect with an appropriate flash message.
13. Hand the completed handler to Belal Moustafa for review.

---

## Acceptance Criteria

- [x] `requireLogin()` is called.
- [x] `requireAdmin()` is called.
- [x] GET requests are rejected.
- [x] CSRF token is validated.
- [x] Invalid item IDs are rejected.
- [x] Item is fetched before deletion.
- [x] Existing orders are checked before deletion.
- [x] Deletion is blocked when orders exist.
- [x] Delete query uses MySQLi OO prepared statement.
- [x] Uploaded image file is deleted after successful database deletion.
- [x] Redirects with flash messages.
- [x] No HTML output is produced.
- [x] Belal Moustafa reviewed and approved the handler.

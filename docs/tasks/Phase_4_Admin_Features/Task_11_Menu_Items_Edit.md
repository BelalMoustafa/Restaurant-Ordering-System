# Task 11 — Menu Items: Edit & Update

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Sameh                          |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Pending                             |
| **Depends On**     | Task 10 (items must exist to edit)  |
| **Blocks**         | Nothing directly                    |

---

## Objective
Build the edit menu item page at `admin/menu_items/edit.php`. The form is pre-populated with the existing item's data. The admin can update any field and optionally replace the image. If no new image is uploaded, the existing image is kept.

---

## Deliverable
`admin/menu_items/edit.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.

### GET Request — Load and Display the Form
- Read `$_GET['id']` — if missing or not numeric, redirect to `index.php` with an error flash
- Fetch the item from `menu_items` WHERE `id = ?` using a MySQLi OO prepared statement
- If no item found, redirect to `index.php` with error flash: "Item not found."
- Pre-populate all form fields with the existing data

### Form Fields
Same fields as Task 10 (Create), with these additions:
- Show the current image as a thumbnail (`.img-thumbnail`) above the file input with label "Current Image"
- If no image exists, show "No current image"
- File input label: "Replace Image (leave blank to keep current)"

### POST Handler — Validation
Same validation rules as Task 10. Additionally:
- Read the hidden `<input name="id">` from the POST data
- Re-fetch the item from DB to get the current `image_path` before processing

### POST Handler — Image Replacement Logic
```
IF new image uploaded AND valid:
    → Upload new image (same process as Task 10)
    → Delete the OLD image file from disk using unlink()
    → Set $image_path to the new file path

ELSE IF no new image uploaded:
    → Keep $image_path = existing image_path from DB (no change)
```

### POST Handler — Database Update
Use a MySQLi OO prepared statement to UPDATE `menu_items`:
- `name`, `description`, `price`, `category`, `image_path`, `is_available`
- WHERE `id = ?`

On success:
- `setFlashMessage('success', 'Menu item updated successfully.')`
- Redirect to `index.php`

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` are both called
- [ ] Item fetched by ID using MySQLi OO prepared statement on GET
- [ ] Invalid or missing ID redirects gracefully
- [ ] All fields pre-populated correctly
- [ ] Current image thumbnail shown
- [ ] Old image deleted from disk when replaced
- [ ] Existing image kept when no new file is uploaded
- [ ] UPDATE uses MySQLi OO prepared statement
- [ ] Redirect to `index.php` with flash message on success

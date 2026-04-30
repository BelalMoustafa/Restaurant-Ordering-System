# Task 10 — Menu Items: Create (with Image Upload)

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Hamza                               |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Completed                           |
| **Depends On**     | Tasks 1–7                           |
| **Blocks**         | Tasks 11, 12 (need items to exist)  |

---

## Objective
Build the create menu item page at `admin/menu_items/create.php`. Admins fill in a form to add a new dish to the menu, including uploading an image. The image must be validated for type and size before being saved.

---

## Deliverable
`admin/menu_items/create.php`

---

## Page Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.
- Generate a CSRF token with `bin2hex(random_bytes(32))` if one does not already exist in `$_SESSION['csrf_token']`.

### Form Fields
The form must use `method="POST"` and `enctype="multipart/form-data"`:

| Field            | Input Type | Validation                                      |
|------------------|------------|-------------------------------------------------|
| Item Name        | text       | Required, max 150 chars                         |
| Description      | textarea   | Optional                                        |
| Price            | number     | Required, min 0 (zero is valid), step 0.01      |
| Category         | text       | Required, max 100 chars                         |
| Available        | checkbox   | Defaults to checked (available)                 |
| Item Image       | file       | Optional, PNG/JPG only, max 2MB                 |
| CSRF Token       | hidden     | Value from `$_SESSION['csrf_token']`            |

- Submit button: "Add Item" (`.btn .btn-primary`)
- Cancel link: "Cancel" → back to `index.php`

### POST Handler — CSRF Validation
Before any other processing, validate the CSRF token:
```php
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Invalid request token. Please try again.');
    header('Location: create.php');
    exit;
}
```

### POST Handler — Field Validation
Validate in this order, collecting all errors:
1. Name: not empty, max 150 chars
2. Price: not empty, is numeric, greater than or equal to 0 (zero is valid for complimentary items)
3. Category: not empty, max 100 chars
4. Image (if uploaded):
   - Check `$_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE`
   - Allowed MIME types: `image/jpeg`, `image/png`
   - Allowed extensions: `.jpg`, `.jpeg`, `.png`
   - Max size: **2MB** (2 * 1024 * 1024 bytes)
   - Validate MIME type using `mime_content_type()` — do NOT trust the browser-supplied MIME type alone

### POST Handler — File Upload (if image provided and valid)
1. Generate a unique filename: `uniqid('item_', true) . '.' . $extension`
2. Define destination: `../../uploads/images/` (relative to `admin/menu_items/`)
3. Move the file: `move_uploaded_file($_FILES['image']['tmp_name'], $destination)`
4. Store the relative path in DB as: `uploads/images/filename.jpg`

### POST Handler — Database Insert
Use a MySQLi prepared statement to INSERT into `menu_items`:
```php
$stmt = $conn->prepare(
    'INSERT INTO menu_items (name, description, price, category, image_path, is_available)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssdssi', $name, $description, $price, $category, $imagePath, $isAvailable);
$stmt->execute();
```

On success:
- `setFlashMessage('success', 'Menu item added successfully.')`
- Redirect to `index.php`

On failure (validation errors):
- Re-render the form with errors and re-populate all fields

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` are both called
- [ ] CSRF token validated with `hash_equals()` before any processing
- [ ] CSRF hidden field present in the form
- [ ] Form has `enctype="multipart/form-data"`
- [ ] Image MIME type validated with `mime_content_type()` — not just extension
- [ ] Image size validated against 2MB limit
- [ ] Price validation allows 0.00 (rejects only negative values)
- [ ] Unique filename generated with `uniqid()`
- [ ] `move_uploaded_file()` used to save the file
- [ ] `image_path` stored as a relative path from project root
- [ ] INSERT uses MySQLi prepared statement — not string interpolation
- [ ] `is_available` correctly maps checkbox to 1 or 0
- [ ] Redirect to `index.php` with flash message on success
- [ ] Form re-populates on validation failure

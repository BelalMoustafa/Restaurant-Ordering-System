# Task 10 - Menu Items: Create (with Image Upload)

## Assignment

| Field | Detail |
|-------|--------|
| **Assigned To** | Habiba |
| **Reviewed By** | Belal Moustafa |
| **Phase** | Phase 4 - Admin Features |
| **Status** | Completed |
| **Depends On** | Tasks 1-7 |
| **Blocks** | Tasks 11 and 12 (items must exist before edit/delete) |

---

## Objective

Build the create menu item page at:

```text
admin/menu_items/create.php
```

Admins use this page to add new dishes to the menu, including optional image upload.

---

## Page Requirements

## Access Control

- Call `requireLogin()` at the top.
- Call `requireAdmin()` at the top.
- Include shared auth and database files before any HTML output.

## CSRF Protection

- Include a hidden CSRF token in the form.
- Validate the CSRF token before any file processing or database insert.
- Use the shared helper `requireValidCsrf()`.

## Form Fields

The form must use:

```text
method="POST"
enctype="multipart/form-data"
```

| Field | Input Type | Validation |
|-------|------------|------------|
| Item Name | text | Required, max 150 characters |
| Description | textarea | Optional |
| Price | number | Required, numeric, minimum 0, step 0.01 |
| Category | text | Required, max 100 characters |
| Available | checkbox | Defaults to checked |
| Item Image | file | Optional, JPG/PNG only, max 2MB |
| CSRF Token | hidden | Value from `csrfToken()` |

---

## POST Handler Requirements

## Validation Order

1. Validate CSRF token.
2. Read and trim form inputs.
3. Validate item name.
4. Validate price.
5. Validate category.
6. Validate image if uploaded.
7. Insert the item using a MySQLi OO prepared statement.
8. Redirect to the menu item list with a flash message.

## Price Validation

Price rules:

- Empty value is invalid.
- Non-numeric value is invalid.
- Negative value is invalid.
- `0` and `0.00` are valid.

Reason:

- The system supports complimentary menu items.

## Image Validation

If an image is uploaded:

- Check `$_FILES['image']['error']`.
- Allow only `image/jpeg` and `image/png`.
- Allow only `.jpg`, `.jpeg`, and `.png`.
- Enforce maximum size of 2MB.
- Validate real MIME type using `mime_content_type()`.
- Generate a safe unique filename.
- Save the image into `uploads/images/`.
- Store a relative path in the database.

---

## Database Insert Requirement

Use Object-Oriented MySQLi prepared statements:

```php
$stmt = $conn->prepare(
    'INSERT INTO menu_items (name, description, price, category, image_path, is_available)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssdssi', $name, $description, $price, $category, $imagePath, $isAvailable);
$stmt->execute();
```

Rules:

- Do not concatenate form input into SQL.
- Do not use raw `mysqli_query()` for user-controlled data.

---

## Step-by-Step Instructions for Habiba

1. Create `admin/menu_items/create.php`.
2. Load session, auth helpers, and database connection before output.
3. Protect the page with `requireLogin()` and `requireAdmin()`.
4. Define upload size, MIME type, extension, and directory rules.
5. Create form state variables and error collection.
6. On POST, validate CSRF first.
7. Validate name, price, category, and optional image.
8. Save the uploaded image only after validation succeeds.
9. Insert the menu item using `$conn->prepare()` and `bind_param()`.
10. Redirect to `index.php` with a flash success message.
11. Re-render the form with errors and preserved values on validation failure.
12. Hand the completed page to Belal Moustafa for review.

---

## Acceptance Criteria

- [x] `requireLogin()` is called.
- [x] `requireAdmin()` is called.
- [x] Header is included only after redirect-capable processing.
- [x] CSRF token is present in the form.
- [x] CSRF token is validated before processing.
- [x] Form has `enctype="multipart/form-data"`.
- [x] Image MIME type is validated with `mime_content_type()`.
- [x] Image extension is validated.
- [x] Image size is limited to 2MB.
- [x] Price validation allows `0.00`.
- [x] Negative prices are rejected.
- [x] Unique filename is generated for uploads.
- [x] `move_uploaded_file()` is used.
- [x] `image_path` is stored as a relative path.
- [x] Insert uses MySQLi OO prepared statement.
- [x] Form re-populates on validation failure.
- [x] Success redirects to `index.php`.
- [x] Belal Moustafa reviewed and approved the feature.

# Task 13 — PDF Menu Upload

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Alaa                                |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 4 — Admin Features            |
| **Status**         | Pending                             |
| **Depends On**     | Tasks 1–7                           |
| **Blocks**         | Nothing directly                    |

---

## Objective
Build the PDF menu upload page at `admin/upload_menu_pdf.php`. The admin can upload a PDF version of the full restaurant menu. Only one PDF is active at a time — uploading a new one replaces the old one. The file path is stored in the database.

---

## Deliverable
`admin/upload_menu_pdf.php`

---

## Database Consideration
The PDF path needs to be stored somewhere persistent. Since there is no dedicated `settings` table, store it in a dedicated row in the `menu_items` table is NOT appropriate. Instead:
- Add a `menu_pdf_path` column to the `menu_items` table — **No**, this is wrong.
- **Correct approach:** Store the PDF path in a simple flat file OR add a `settings` table.
- **Recommended for this project:** Create a `settings` table with `key` and `value` columns, and store `menu_pdf_path` as a key-value pair. Coordinate with Hamza (Task 1 owner) to add this table to `schema.sql`, OR store the path in a PHP constant file.
- **Simplest acceptable approach:** Store the path in `uploads/pdfs/` with a fixed filename `menu.pdf` — always overwrite. No DB entry needed. Display a link to the current PDF if it exists.

Use the **simplest approach** (fixed filename `menu.pdf`) unless Belal approves the settings table approach.

---

## Page Requirements

### Access Control
- Call `requireLogin()` and `requireAdmin()` at the top.

### GET Request — Show the Form
- Page heading: "Upload PDF Menu"
- If `uploads/pdfs/menu.pdf` exists:
  - Show: "Current Menu PDF: [View PDF]" with a link to the file
  - Show: "Last updated: [file modification date using `filemtime()`]"
- Upload form with `enctype="multipart/form-data"`:
  - File input: `name="menu_pdf"`, accept `.pdf`
  - Submit button: "Upload PDF" (`.btn .btn-primary`)
  - Warning text: "Uploading a new PDF will replace the current one."

### POST Handler — Validation
1. Check `$_FILES['menu_pdf']['error'] === UPLOAD_ERR_OK`
2. Validate MIME type using `mime_content_type()`: must be `application/pdf`
3. Validate file extension: must be `.pdf`
4. Max file size: **5MB** (5 * 1024 * 1024 bytes)

### POST Handler — File Save
1. If an old `menu.pdf` exists in `uploads/pdfs/`, delete it with `unlink()`
2. Save the new file as `uploads/pdfs/menu.pdf` using `move_uploaded_file()`
3. Set flash message: `setFlashMessage('success', 'PDF menu uploaded successfully.')`
4. Redirect to the same page (POST-Redirect-GET pattern)

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `requireLogin()` and `requireAdmin()` are both called
- [ ] MIME type validated with `mime_content_type()` — not just extension
- [ ] File size validated against 5MB limit
- [ ] Old PDF deleted before saving new one
- [ ] Fixed filename `menu.pdf` used
- [ ] Current PDF link shown if file exists
- [ ] POST-Redirect-GET pattern used after upload
- [ ] Flash message shown on success

# Alaa Defense Notes

## 1. Assigned Tasks

Alaa was responsible for:

- Task 4: Shared Includes, Header, Footer, and Auth Helpers.
- Task 13: PDF Menu Upload.

These tasks are important because Task 4 creates reusable code used by almost every page, and Task 13 gives admins the ability to upload the restaurant's full PDF menu.

## 2. Task 4: Shared Includes, Header, Footer, and Auth Helpers

## 2.1 Task Objective

The objective of Task 4 was to create shared infrastructure files:

```text
includes/auth.php
includes/header.php
includes/footer.php
```

These files avoid duplicated logic across the project.

Task 4 had to provide:

- Login checks.
- Admin checks.
- User role checks.
- Guest checks.
- CSRF helpers.
- Flash messages.
- Shared navigation.
- Shared HTML header.
- Shared footer and JavaScript loading.

## 2.2 `includes/auth.php` Deep-Dive

### Direct Access Protection

```php
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}
```

Explanation:

- This prevents direct browser access to `includes/auth.php`.
- The file should only be loaded by application pages.
- If `APP_RUNNING` is not defined, the request is blocked.

Why this matters:

- Include files are not meant to be standalone pages.
- It reduces accidental exposure.

### `isLoggedIn()`

```php
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
```

Explanation:

- Checks whether session contains a user ID.
- Returns true for authenticated sessions.

Dependency:

- Login page sets `$_SESSION['user_id']`.

### `isAdmin()`

```php
function isAdmin(): bool
{
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
```

Explanation:

- First confirms user is logged in.
- Then checks session role equals `admin`.

Why both checks:

- Role alone should not be trusted if user is not logged in.
- It prevents undefined or fake session state from being treated as admin.

### `requireLogin()`

```php
function requireLogin(string $redirectTo = '../auth/login.php'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
```

Explanation:

- Protects pages that require authentication.
- Redirects guests to login.
- `exit` prevents protected page code from continuing.

Why redirect path is a parameter:

- Pages live at different folder depths.
- Custom redirect paths avoid broken links.

### `requireAdmin()`

```php
function requireAdmin(string $redirectTo = '../user/dashboard.php'): void
{
    if (!isAdmin()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
```

Explanation:

- Protects admin pages.
- Non-admins are redirected away.

### `requireUser()`

```php
function requireUser(string $redirectTo = '../auth/login.php'): void
{
    requireLogin($redirectTo);

    if (isAdmin()) {
        header('Location: ../admin/dashboard.php');
        exit;
    }
}
```

Explanation:

- Ensures user is logged in.
- Redirects admins away from customer ordering pages.

Why this exists:

- Admin and customer flows are separate.
- Admins manage orders but should not place customer orders.

### `requireGuest()`

```php
function requireGuest(): void
{
    if (isLoggedIn()) {
        if (isAdmin()) {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../user/dashboard.php');
        }
        exit;
    }
}
```

Explanation:

- Used on login and registration pages.
- Already logged-in users should not see login/register forms again.
- Redirects by role.

### `csrfToken()`

```php
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}
```

Explanation:

- Creates a CSRF token if one does not exist.
- Stores token in session.
- Returns token for hidden form fields.

Why `random_bytes(32)`:

- Cryptographically secure random data.

Why `bin2hex()`:

- Converts binary bytes into safe printable text.

### `requireValidCsrf()`

```php
function requireValidCsrf(string $redirectTo): void
{
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && (
            empty($_POST['csrf_token'])
            || !hash_equals(csrfToken(), $_POST['csrf_token'])
        )
    ) {
        setFlashMessage('danger', 'Invalid request token. Please try again.');
        header('Location: ' . $redirectTo);
        exit;
    }
}
```

Explanation:

- Runs validation only for POST requests.
- Rejects missing token.
- Rejects mismatched token.
- Uses `hash_equals()` for safe comparison.
- Redirects with flash message.

Why centralize CSRF validation:

- Every form uses the same logic.
- Less duplicated code.
- Easier to audit.

### Flash Messages

```php
function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}
```

Explanation:

- Stores a one-time message in session.
- Used before redirects.

```php
function getFlashMessage(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
```

Explanation:

- Reads flash message.
- Deletes it after reading.
- Ensures message appears once.

## 2.3 `includes/header.php` Deep-Dive

### Application Context and Session

```php
if (!defined('APP_RUNNING')) {
    define('APP_RUNNING', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

Explanation:

- Ensures application constant exists.
- Starts session if page did not already start it.

### Loading Auth Helpers

```php
require_once __DIR__ . '/auth.php';
```

Explanation:

- Makes role helpers available to navbar.
- Allows header to show different links based on login state.

### Base Path Calculation

```php
$_depth   = substr_count($_SERVER['PHP_SELF'], '/') - 2;
$basePath = $_depth > 0 ? str_repeat('../', $_depth) : '';
```

Explanation:

- Calculates how deep the current page is in folders.
- Builds a relative path back to project root.

Why needed:

- `index.php` and `admin/menu_items/index.php` are at different folder depths.
- CSS, JS, and links must still work from every page.

### Active Link Helper

```php
function navActive(string $segment): string
{
    global $_currentPath;
    return (strpos($_currentPath, $segment) !== false) ? ' active' : '';
}
```

Explanation:

- Checks current path.
- Returns `active` class for matching navigation link.

### Flash Message Rendering

```php
$_flash = getFlashMessage();
```

Later:

```php
<?php if ($_flash !== null): ?>
    <div class="alert alert-<?= $alertType ?>" role="alert">
        <?= $alertMessage ?>
    </div>
<?php endif; ?>
```

Explanation:

- Pulls one-time message from session.
- Displays it at the top of main content.

### Role-Based Navigation

Header shows different links depending on role:

Guest:

- Menu.
- Login.
- Register.

Admin:

- Dashboard.
- Menu Items.
- Orders.
- Upload PDF.
- Logout.

User:

- Menu.
- Place Order.
- My Orders.
- Logout.

Why this matters:

- The interface guides each role to the correct features.
- Security still happens server-side through `requireAdmin()` and `requireUser()`.

Defense point:

- Hiding links is not the only security. Server-side checks enforce the real access control.

## 2.4 `includes/footer.php` Deep-Dive

Footer responsibilities:

- Close container and main tags.
- Render copyright footer.
- Load global JavaScript.
- Close body and HTML tags.

Important script line:

```php
<script src="<?= htmlspecialchars($basePath ?? '') ?>assets/js/main.js"></script>
```

Explanation:

- Uses `$basePath` so JavaScript loads correctly from any folder depth.
- Escapes path before output.

## 2.5 Design Decisions for Task 4

### Why shared includes?

Without shared includes, every page would duplicate:

- HTML head.
- Navbar.
- Footer.
- Script tag.
- Flash rendering.
- Auth helper logic.

Shared includes reduce duplication and make the project maintainable.

### Why auth helpers instead of repeating checks?

Central helpers:

- Keep code readable.
- Avoid inconsistent access control.
- Make review easier.

### Why role-based navbar?

Each role has different features. The navbar should match what the user can do.

### Why flash messages?

Most actions redirect after POST. Flash messages allow the next page to show success or error feedback.

## 2.6 Alternatives for Task 4

### Alternative: Copy Header HTML Into Every Page

Why not chosen:

- Hard to maintain.
- Any navbar change would require editing many files.

### Alternative: Use a Template Engine

Examples:

- Twig.
- Blade.

Why not chosen:

- Would introduce framework or package dependency.
- Core PHP includes are enough.

### Alternative: Separate Auth Classes

The project could use classes like `AuthService`.

Why not chosen:

- The project is small.
- Function helpers are simpler for a university defense.

## 2.7 Dependencies for Task 4

Task 4 depends on:

- Task 2 for folder structure.
- Task 3 because header links CSS and footer loads JS.

Task 4 supports:

- Every protected page.
- Login/register redirects.
- Admin authorization.
- User authorization.
- CSRF protection.
- Flash messages.

---

## 3. Task 13: PDF Menu Upload

## 3.1 Task Objective

Task 13 allows an admin to upload a PDF version of the restaurant menu.

The feature must:

- Be admin-only.
- Use POST for upload.
- Validate CSRF token.
- Accept PDF files only.
- Enforce maximum file size.
- Replace the existing PDF menu.
- Show current PDF link if one exists.

## 3.2 File Responsible

```text
admin/upload_menu_pdf.php
```

## 3.3 Code Deep-Dive

### Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- Starts application context.
- Starts session.
- Loads auth helpers.
- Loads database connection.

Note:

- This page does not store PDF path in database, but it still loads the DB connection because the shared admin flow and auth infrastructure expect the normal application environment.

### Authorization

```php
requireLogin();
requireAdmin();
```

Explanation:

- Guest users cannot upload PDFs.
- Normal users cannot upload PDFs.
- Only admins can continue.

### PDF Constants

```php
define('MAX_PDF_SIZE',   5 * 1024 * 1024);
define('PDF_UPLOAD_DIR', __DIR__ . '/../uploads/pdfs/');
define('PDF_FIXED_NAME', 'menu.pdf');
define('PDF_FULL_PATH',  PDF_UPLOAD_DIR . PDF_FIXED_NAME);
```

Explanation:

- `MAX_PDF_SIZE` limits PDF to 5MB.
- `PDF_UPLOAD_DIR` points to `uploads/pdfs/`.
- `PDF_FIXED_NAME` means only one active PDF exists.
- `PDF_FULL_PATH` is the absolute save path.

Why fixed filename:

- The project is limited to 3 database tables.
- A settings table for PDF path would break the schema requirement.
- A fixed file is simple and reliable.

### POST and CSRF Validation

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('upload_menu_pdf.php');
```

Explanation:

- Upload is processed only on POST.
- CSRF token is validated before file handling.

Why CSRF matters:

- Uploading or replacing a PDF changes system state.
- Another website must not be able to force an admin to upload/replace a file.

### Upload Error Handling

```php
$fileError = $_FILES['menu_pdf']['error'] ?? UPLOAD_ERR_NO_FILE;

if ($fileError === UPLOAD_ERR_NO_FILE) {
    $pdfError = 'Please select a PDF file to upload.';
} elseif ($fileError !== UPLOAD_ERR_OK) {
    $pdfError = 'Upload failed. Please try again (error code: ' . $fileError . ').';
}
```

Explanation:

- Handles missing file.
- Handles PHP upload errors.
- Avoids assuming upload succeeded.

### File Information

```php
$tmpPath      = $_FILES['menu_pdf']['tmp_name'];
$originalName = $_FILES['menu_pdf']['name'];
$fileSize     = $_FILES['menu_pdf']['size'];
$extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
```

Explanation:

- Reads temporary upload path.
- Reads original filename.
- Reads file size.
- Extracts extension.

Security note:

- Original filename is not used as final filename.

### Size Validation

```php
if ($fileSize > MAX_PDF_SIZE) {
    $pdfError = 'File is too large. Maximum allowed size is 5MB.';
}
```

Explanation:

- Rejects files larger than 5MB.
- Prevents oversized uploads.

### MIME Validation

```php
$detectedMime = mime_content_type($tmpPath);
if ($detectedMime !== 'application/pdf') {
    $pdfError = 'Invalid file type. Only PDF files are accepted.';
}
```

Explanation:

- Checks the actual uploaded file content.
- Rejects non-PDF files renamed to `.pdf`.

### Extension Validation

```php
if ($extension !== 'pdf') {
    $pdfError = 'Invalid file extension. Only .pdf files are accepted.';
}
```

Explanation:

- Enforces filename policy.
- PDF file must end in `.pdf`.

### Replacing Existing PDF

```php
if (file_exists(PDF_FULL_PATH)) {
    unlink(PDF_FULL_PATH);
}
if (!move_uploaded_file($tmpPath, PDF_FULL_PATH)) {
    $pdfError = 'Failed to save the PDF. Please check directory permissions on uploads/pdfs/.';
}
```

Explanation:

- Removes existing menu PDF if present.
- Saves new upload as `menu.pdf`.
- Shows error if save fails.

Why replace instead of versioning:

- Only one active restaurant PDF menu is needed.
- Simpler for users and admins.

### Success Redirect

```php
setFlashMessage('success', 'PDF menu uploaded successfully.');
header('Location: upload_menu_pdf.php');
exit;
```

Explanation:

- Shows success message after redirect.
- Prevents duplicate upload if admin refreshes.

### Current PDF Display

```php
$pdfExists       = file_exists(PDF_FULL_PATH);
$pdfLastModified = $pdfExists ? date('d M Y, H:i', filemtime(PDF_FULL_PATH)) : null;
```

Explanation:

- Checks if a PDF exists.
- Gets last modified date.

UI behavior:

- If PDF exists, show file link and last updated date.
- If PDF does not exist, show informational alert.

### Upload Form CSRF Token

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Explanation:

- Adds CSRF token to PDF upload form.
- Token is escaped before output.

## 3.4 Design Decisions for Task 13

### Why PDF upload is admin-only?

The PDF menu represents official restaurant content. Customers should not be able to modify it.

### Why fixed `menu.pdf`?

The project requires exactly 3 tables. A database-backed settings feature would require more schema complexity. A fixed filename satisfies the requirement without adding tables.

### Why validate MIME and extension?

Both checks help prevent unsafe uploads:

- MIME checks content.
- Extension checks filename policy.

### Why store in `uploads/pdfs/`?

It keeps uploaded PDFs separate from menu item images and makes `.htaccess` protection easier.

### Why no database insert?

There is no required table column for PDF path, and the fixed filename makes a database record unnecessary.

## 3.5 Alternatives for Task 13

### Alternative: Store PDF Path in Database

Possible approaches:

- `settings` table.
- `menu_pdf` table.

Why not chosen:

- Would violate or complicate the strict 3-table requirement.

### Alternative: Allow Multiple PDFs

The system could store versions like:

- `menu_2026_01.pdf`
- `menu_2026_02.pdf`

Why not chosen:

- Not required.
- Users only need the current menu.

### Alternative: Accept DOCX or Images

Why not chosen:

- The specification requires PDF only.
- PDF is reliable for menu downloads.

## 3.6 Dependencies for Task 13

Task 13 depends on:

- Task 2 for folder structure.
- Task 4 for auth helpers, CSRF, header, and footer.
- Task 6 because admin login creates the session role.
- Task 19 because uploads security prevents script execution.

Task 13 supports:

- Public landing page PDF download.
- User dashboard PDF download.
- User menu PDF download.

## 4. Alaa Defense Questions and Answers

### Q1: Why use shared includes?

Shared includes prevent duplicated header, footer, navigation, and auth logic across many pages.

### Q2: Why is `requireAdmin()` not enough without `requireLogin()`?

`requireAdmin()` calls `isAdmin()`, which depends on login state, but many pages still call both for clarity: first require a valid session, then require admin role.

### Q3: Why create `requireUser()`?

It prevents admins from using customer-only pages like order placement, keeping role flows separate.

### Q4: Why use `hash_equals()` for CSRF?

It compares tokens safely and avoids timing-based comparison weaknesses.

### Q5: Why does the header calculate `$basePath`?

Pages exist in different folder depths. `$basePath` keeps CSS links, JS links, and navigation links correct everywhere.

### Q6: Why is PDF not stored in the database?

The project requires only 3 core tables. A fixed PDF path avoids adding another table or schema field.

### Q7: Why is PDF upload protected by CSRF?

Replacing the menu PDF is a state-changing admin action, so it must reject forged requests.

### Q8: Why check both MIME type and extension for PDF?

Extension can be faked, and MIME alone does not enforce naming. Checking both is safer.

## 5. Alaa Summary

Alaa's work created shared project infrastructure and an admin PDF upload feature.

Task 4 delivered:

- Auth helpers.
- Role-based access helpers.
- CSRF helpers.
- Flash messages.
- Dynamic navbar.
- Shared header/footer.
- Global asset loading.

Task 13 delivered:

- Admin-only PDF upload.
- CSRF validation.
- File type validation.
- File size validation.
- Fixed active menu PDF.
- PDF replacement and display.

In defense, Alaa should emphasize that shared infrastructure reduces duplication and that PDF upload is secured through role checks, CSRF validation, MIME validation, extension validation, and upload folder protections.


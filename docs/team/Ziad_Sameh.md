# Ziad Sameh Defense Notes

## 1. Assigned Tasks

Ziad Sameh was responsible for:

- Task 2: Project Skeleton and Database Connection.
- Task 11: Menu Items Edit and Update.

These two tasks are connected because Task 2 creates the project foundation and database connection, while Task 11 uses that foundation to update menu item records through an admin-only feature.

## 2. Task 2: Project Skeleton and Database Connection

## 2.1 Task Objective

The objective of Task 2 was to create the base project structure and implement the database connection file.

The task had two major goals:

- Ensure the project folders match the required architecture.
- Create `config/db.php` using Core PHP and Object-Oriented MySQLi.

This task is foundational because every later feature depends on a correct folder layout and a working `$conn` database connection.

## 2.2 Files and Folders Responsible

Main file:

```text
config/db.php
```

Important folders created or required:

```text
admin/
assets/
auth/
config/
database/
docs/
includes/
uploads/
user/
```

## 2.3 `config/db.php` Code Deep-Dive

### Database Constants

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Explanation:

- `DB_HOST` is `localhost` because the system runs locally on XAMPP/MAMP.
- `DB_NAME` is `restaurant_db`, matching the database created in `database/schema.sql`.
- `DB_USER` is `root`, the common default local MySQL user.
- `DB_PASS` is an empty string, which is common in default XAMPP MySQL setups.

Why constants are used:

- They make configuration easy to locate.
- They avoid repeating database credentials across many files.
- If the local setup changes, only one file needs updating.

Defense point:

- This is local-only configuration, not production deployment configuration.

### Creating the MySQLi Connection

```php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

Explanation:

- `new mysqli(...)` creates an Object-Oriented MySQLi connection.
- The created connection object is stored in `$conn`.
- Other pages include `config/db.php` and use `$conn` to prepare SQL statements.

Why `$conn` is global:

- In a simple Core PHP project, included files share variables.
- Every page can access the same connection object after requiring `db.php`.
- It keeps the project simple and defense-friendly.

### Connection Error Handling

```php
if ($conn->connect_error) {
    error_log('[DB Connection Error] ' . $conn->connect_error);
    die(
        '<div style="font-family:sans-serif;padding:40px;text-align:center;">'
      . '<h2>Service Unavailable</h2>'
      . '<p>We could not connect to the database. Please try again later.</p>'
      . '</div>'
    );
}
```

Explanation:

- `$conn->connect_error` checks whether MySQL connection failed.
- `error_log()` records the real technical error for debugging.
- The browser receives a generic user-friendly message.
- `die()` stops execution because the application cannot continue without the database.

Why not show raw database error:

- Raw errors may reveal usernames, paths, ports, or database names.
- Even in a university project, hiding sensitive internals is a good security habit.

Defense point:

- This balances debugging needs and user-facing security.

### Character Set

```php
$conn->set_charset('utf8mb4');
```

Explanation:

- Sets the connection encoding to `utf8mb4`.
- Ensures PHP and MySQL communicate using the same character set.
- Supports full Unicode text in names, descriptions, notes, and menu items.

Why this matters:

- Without setting charset, text may display incorrectly.
- It also avoids some historical encoding-related security problems.

## 2.4 Project Skeleton Deep-Dive

### `admin/`

Purpose:

- Contains admin-only pages.
- Protected with `requireAdmin()`.

Important subfolders:

```text
admin/menu_items/
admin/orders/
```

Why separated:

- Menu management and order management are different admin responsibilities.
- Subfolders keep related pages together.

### `auth/`

Purpose:

- Contains authentication pages:
  - Register.
  - Login.
  - Logout.

Why separated:

- Authentication is a separate concern from admin and user features.

### `config/`

Purpose:

- Stores configuration files such as database connection.

Security note:

- Root `.htaccess` blocks direct access to this directory.

### `includes/`

Purpose:

- Stores reusable layout and auth helper files.

Examples:

- `auth.php`
- `header.php`
- `footer.php`

Why separated:

- Shared code should not be copied into every page.
- Changes to navbar or auth helpers can be made in one place.

### `assets/`

Purpose:

- Stores CSS and JavaScript.

Why separated:

- Keeps static frontend files away from PHP logic.

### `uploads/`

Purpose:

- Stores user-uploaded images and PDFs.

Security note:

- Has its own `.htaccess` to prevent PHP execution.

### `database/`

Purpose:

- Stores schema creation SQL.

Why separated:

- The schema is part of the development setup, not runtime PHP.

### `user/`

Purpose:

- Contains authenticated customer pages.

Examples:

- Dashboard.
- Menu.
- Place order.
- My orders.

## 2.5 Design Decisions for Task 2

### Why Core PHP?

The university requirement explicitly says no frameworks.

Core PHP was used so the team can demonstrate:

- Manual routing through files.
- Manual database connection.
- Manual session handling.
- Manual authentication helpers.
- Manual CSRF implementation.

### Why MySQLi OO?

The project rule requires MySQLi OO.

It is used through:

```php
new mysqli(...)
$conn->prepare(...)
$stmt->bind_param(...)
$stmt->get_result()
```

Why not procedural MySQLi:

- OO style is cleaner and matches the requirement.
- It keeps connection and statement behavior attached to objects.

### Why one central `db.php`?

Benefits:

- Reduces duplication.
- Makes credentials easy to update.
- Gives every page the same connection behavior.
- Standardizes charset and error handling.

### Why local XAMPP/MAMP settings?

The project is local-only.

Using:

```text
localhost
root
empty password
```

is acceptable for local university demonstration but would not be used as-is for production hosting.

## 2.6 Alternatives for Task 2

### Alternative: Use Environment Variables

Production projects often store credentials in `.env` files or server environment variables.

Why not chosen:

- The project is local-only.
- `.env` parsing would add complexity.
- The specification asks for a simple local XAMPP/MAMP setup.

### Alternative: Use PDO

PDO can connect to different database engines.

Why not chosen:

- The project requires Object-Oriented MySQLi.
- The system only targets MySQL.

### Alternative: Use a Framework Structure

Frameworks use folders such as:

- `controllers/`
- `models/`
- `views/`
- `routes/`

Why not chosen:

- Frameworks are forbidden.
- The required structure is already specified in the project specs.

## 2.7 Dependencies for Task 2

Task 2 depends on Task 1:

- Task 1 creates `restaurant_db`.
- Task 2 connects to `restaurant_db`.

If Task 1 is not imported:

- `config/db.php` cannot connect successfully.
- Later PHP pages cannot query data.

Task 2 supports all later tasks because:

- Registration needs the database connection.
- Login needs the database connection.
- Admin CRUD needs the database connection.
- User ordering needs the database connection.
- Order history needs the database connection.

---

## 3. Task 11: Menu Items Edit and Update

## 3.1 Task Objective

Task 11 allows admins to edit an existing menu item.

The feature must:

- Be admin-only.
- Load existing item data by ID.
- Show a pre-filled edit form.
- Validate CSRF token before update.
- Validate name, price, and category.
- Allow price `0.00`.
- Reject negative prices.
- Optionally replace the current image.
- Delete old image when replaced.
- Update the `menu_items` row using MySQLi OO prepared statements.

## 3.2 File Responsible

```text
admin/menu_items/edit.php
```

## 3.3 Code Deep-Dive

### Bootstrap and Includes

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
```

Explanation:

- Defines application context.
- Starts session so role and CSRF checks work.
- Loads auth helpers.
- Loads database connection.

Why this happens before HTML:

- This page may redirect if item ID is invalid, user is unauthorized, item is missing, or update succeeds.
- Redirects must happen before output.

### Authorization

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- Guests are redirected to login.
- Regular users are redirected away.
- Only admins can edit menu items.

Defense point:

- This enforces role-based access control on the server side, not just in the navbar.

### Upload Constants

```php
define('MAX_IMAGE_SIZE',    2 * 1024 * 1024);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('UPLOAD_DIR',         __DIR__ . '/../../uploads/images/');
```

Explanation:

- Reuses the same upload rules as create.
- Image replacement must be just as secure as first upload.

Why duplicate constants here:

- This file is a separate page-controller.
- It can validate uploads independently.
- The logic is explicit and easy to defend.

### Form State Variables

```php
$errors           = [];
$formName         = '';
$formDescription  = '';
$formPrice        = '';
$formCategory     = '';
$formAvailable    = true;
$currentImagePath = null;
$itemId           = 0;
```

Explanation:

- `$errors` stores validation messages.
- Form variables preserve values.
- `$currentImagePath` tracks the existing image.
- `$itemId` identifies which menu item is being edited.

### Reading Item ID and Validating CSRF Timing

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int) ($_POST['id'] ?? 0);
    requireValidCsrf($itemId > 0 ? 'edit.php?id=' . $itemId : 'index.php');
} else {
    $itemId = (int) ($_GET['id'] ?? 0);
}
```

Explanation:

- On GET, item ID comes from URL.
- On POST, item ID comes from hidden form input.
- CSRF is validated immediately on POST before update processing.

Why this order matters:

- State-changing requests must be validated before any update logic.
- This prevents forged update requests.

### Invalid ID Handling

```php
if ($itemId <= 0) {
    setFlashMessage('danger', 'Invalid item ID. Please select an item to edit.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- IDs must be positive integers.
- Invalid requests are redirected to the list page.
- Flash message explains the problem.

### Fetch Existing Item

```php
$stmtFetch = $conn->prepare(
    'SELECT id, name, description, price, category, image_path, is_available
     FROM menu_items WHERE id = ? LIMIT 1'
);
$stmtFetch->bind_param('i', $itemId);
$stmtFetch->execute();
$resultFetch = $stmtFetch->get_result();
$item        = $resultFetch->fetch_assoc();
$stmtFetch->close();
```

Explanation:

- Prepared statement fetches the item by ID.
- `bind_param('i', $itemId)` binds integer ID.
- `LIMIT 1` confirms only one row is needed.
- `fetch_assoc()` returns the item as an associative array.

Why prepared statement:

- Even numeric IDs should be bound safely.
- It keeps all database access consistent.

### Missing Item Handling

```php
if (!$item) {
    setFlashMessage('danger', 'Menu item not found.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- If the ID does not exist, admin returns to list page.
- Prevents rendering an edit form for missing data.

### Pre-Filling Form on GET

```php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $formName        = htmlspecialchars($item['name'],              ENT_QUOTES, 'UTF-8');
    $formDescription = htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8');
    $formPrice       = htmlspecialchars((string) $item['price'],    ENT_QUOTES, 'UTF-8');
    $formCategory    = htmlspecialchars($item['category'],          ENT_QUOTES, 'UTF-8');
    $formAvailable   = (bool) $item['is_available'];
}
```

Explanation:

- Existing database values are escaped before being printed into form fields.
- This prevents XSS if stored data contains special characters.
- Availability is converted to boolean for checkbox rendering.

### Reading Submitted Values

```php
$rawName        = trim($_POST['name']        ?? '');
$rawDescription = trim($_POST['description'] ?? '');
$rawPrice       = trim($_POST['price']       ?? '');
$rawCategory    = trim($_POST['category']    ?? '');
$rawAvailable   = isset($_POST['is_available']) ? 1 : 0;
```

Explanation:

- Reads submitted values.
- Trims whitespace.
- Checkbox is converted to integer.

### Validation

Validation rules:

- Name is required and max 150 characters.
- Price is required.
- Price must be numeric.
- Price cannot be negative.
- Category is required and max 100 characters.

Important detail:

- Price `0` is accepted.
- That matches the complimentary item requirement.

### Image Replacement Logic

```php
$newImagePath = $currentImagePath;
$fileError    = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
```

Explanation:

- By default, the item keeps its current image.
- If no new file is uploaded, image path remains unchanged.

### New Image Validation

The file is validated by:

- Upload error status.
- Maximum size.
- MIME type.
- Extension.

This is the same security model as Task 10.

### Replacing Old Image

```php
if (!empty($currentImagePath)) {
    $oldFilePath = __DIR__ . '/../../' . $currentImagePath;
    if (file_exists($oldFilePath)) {
        unlink($oldFilePath);
    }
}
$newImagePath = 'uploads/images/' . $newFilename;
```

Explanation:

- If a new image is uploaded successfully, the old image is removed.
- `file_exists()` prevents errors if the old file is already missing.
- `unlink()` deletes the old file.

Why delete old image:

- Prevents unused files accumulating in `uploads/images/`.
- Keeps file storage consistent with database state.

### Update Query

```php
$stmtUpdate = $conn->prepare(
    'UPDATE menu_items
     SET name = ?, description = ?, price = ?,
         category = ?, image_path = ?, is_available = ?
     WHERE id = ?'
);
$stmtUpdate->bind_param('ssdssii', $rawName, $descValue, $priceValue, $rawCategory, $imageValue, $rawAvailable, $itemId);
$stmtUpdate->execute();
$stmtUpdate->close();
```

Explanation:

- Updates only the selected item.
- Uses placeholders for all dynamic values.
- `ssdssii` means:
  - string name.
  - string description.
  - decimal/double price.
  - string category.
  - string image path.
  - integer availability.
  - integer item ID.

Defense point:

- The `WHERE id = ?` prevents accidentally updating all menu items.

### Success Redirect

```php
setFlashMessage('success', 'Menu item "' . $rawName . '" updated successfully.');
header('Location: index.php');
exit;
```

Explanation:

- Admin sees success message on menu list.
- Redirect prevents duplicate update on refresh.

### Rendering the Form

The form includes:

- Hidden item ID.
- Hidden CSRF token.
- Name field.
- Description field.
- Price field.
- Category field.
- Availability checkbox.
- Current image preview.
- Replacement image upload.

Important line:

```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

Purpose:

- Adds the CSRF token required for secure POST handling.

## 3.4 Design Decisions for Task 11

### Why fetch item before showing form?

The edit page needs existing data so the admin knows what they are changing.

### Why allow optional image replacement?

Admins may want to update text or price without changing the image.

### Why delete old image only after new upload succeeds?

If the new upload fails, the system should not remove the existing valid image.

### Why use the same validation as create?

Edit must be as secure as create. Otherwise, an invalid image or price could enter through update even if create blocks it.

### Why use POST for updates?

Updating a menu item changes state. GET requests should not change system data.

## 3.5 Alternatives for Task 11

### Alternative: Separate Image Delete Button

The system could allow admins to remove an image without replacing it.

Why not chosen:

- Not required by the specification.
- Replacement upload is enough for this project.

### Alternative: Store Old Images Forever

Why not chosen:

- It wastes storage.
- Database would point to only the newest image, leaving orphan files.

### Alternative: Use AJAX Update

Why not chosen:

- Standard POST is simpler.
- Easier to explain in defense.
- Works without JavaScript.

## 3.6 Dependencies for Task 11

### Depends on Task 1

Needs the `menu_items` table.

### Depends on Task 2

Needs `config/db.php` for `$conn`.

### Depends on Task 4

Needs auth helpers and shared layout.

### Depends on Task 10

Task 11 edits menu items created by Task 10.

### Supports Task 15 and Task 16

Updated items appear in user menu and order flow if available.

## 4. Ziad Sameh Defense Questions and Answers

### Q1: Why does `db.php` use constants?

Constants keep database configuration centralized and prevent repeated credentials across the project.

### Q2: Why is `$conn->set_charset('utf8mb4')` needed?

It ensures PHP and MySQL use full Unicode encoding, preventing text display problems.

### Q3: Why is the edit page protected by both `requireLogin()` and `requireAdmin()`?

Because only logged-in admins should edit menu data. A logged-in regular user must still be blocked.

### Q4: Why validate CSRF before update logic?

Because update is a state-changing action. CSRF must be verified before accepting submitted data.

### Q5: Why use `move_uploaded_file()`?

It is PHP's safe function for moving uploaded files and confirms the source is an actual uploaded file.

### Q6: Why is the item fetched by ID before update?

The system needs to verify the item exists and know the current image path before replacing it.

### Q7: Why use prepared statements for the update?

Prepared statements prevent SQL injection and safely bind form values.

## 5. Ziad Sameh Summary

Ziad Sameh contributed to both project foundation and admin menu management.

Task 2 established:

- Folder structure.
- Central database connection.
- MySQLi OO connection pattern.
- Safe connection error handling.
- UTF-8 database communication.

Task 11 implemented:

- Admin-only item editing.
- Existing item loading.
- Secure CSRF timing.
- Field validation.
- Optional image replacement.
- Old image cleanup.
- MySQLi prepared update.

In defense, Ziad Sameh should emphasize that his work connects low-level infrastructure with a real CRUD update feature.


# Habiba Defense Notes

## 1. Assigned Tasks

Habiba was responsible for:

- Task 3: Global Assets, CSS and JavaScript.
- Task 12: Menu Items Delete.

These tasks cover both user interface quality and backend safety. Task 3 gives the system its consistent monochrome design and client-side behaviors. Task 12 implements secure deletion of menu items while preserving order history.

## 2. Task 3: Global Assets, CSS and JavaScript

## 2.1 Task Objective

The objective of Task 3 was to create the global frontend foundation.

The task required:

- A complete monochrome CSS design system.
- Clean layout styles.
- Form styling.
- Table styling.
- Button styling.
- Menu card styling.
- Responsive behavior.
- JavaScript helpers for validation and UI behavior.

Files responsible:

```text
assets/css/style.css
assets/js/main.js
```

## 2.2 CSS Architecture Deep-Dive

### Reset and Base Styles

The stylesheet begins with a reset:

```css
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
```

Explanation:

- Removes default browser margin and padding.
- Makes layout easier to control.
- `box-sizing: border-box` makes widths include padding and border.

Why this matters:

- Different browsers apply different default styles.
- Resetting creates a predictable base for the whole project.

### HTML and Body

```css
html {
    font-size: 16px;
    scroll-behavior: smooth;
}

body {
    font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
    font-size: 1rem;
    line-height: 1.6;
    background-color: #ffffff;
    color: #000000;
}
```

Explanation:

- Sets readable base font size.
- Uses smooth scrolling for anchor links.
- Uses black text on white background.
- Applies the required monochrome theme.

Defense point:

- The UI follows the project rule: black, white, and gray tones only.

### Typography

Headings use Georgia:

```css
h1,
h2,
h3,
h4 {
    font-family: 'Georgia', 'Times New Roman', Times, serif;
    font-weight: 700;
}
```

Explanation:

- Serif headings give the restaurant brand a classic feel.
- Body text remains clean with sans-serif fonts.

Why this is a design decision:

- It creates hierarchy without using bright colors.
- It fits the monochrome requirement while still feeling styled.

### Layout Containers

```css
.container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 20px;
    width: 100%;
}
```

Explanation:

- Keeps content centered.
- Prevents text from stretching too wide.
- Provides horizontal spacing on small screens.

### Navigation

The navbar uses black background and white text:

```css
.navbar {
    background-color: #000000;
    color: #ffffff;
}
```

Explanation:

- Strong contrast.
- Clear role-based navigation.
- Matches monochrome design.

The `.active` link uses underline styling:

```css
.navbar-nav a.active {
    border-bottom-color: #ffffff;
    font-weight: 600;
}
```

Why this matters:

- Users can understand which section they are in without extra colors.

### Buttons

Buttons are based on one `.btn` class, then variants:

- `.btn-primary`
- `.btn-secondary`
- `.btn-danger`
- `.btn-sm`

Primary buttons:

```css
.btn-primary {
    background-color: #000000;
    color: #ffffff;
}
```

Explanation:

- Primary action is black background with white text.
- Hover inverts the colors.

Why no bright danger color:

- The strict project rule requires monochrome UI.
- Danger is shown using stronger border/font weight instead of red.

### Forms

Form fields share one style:

```css
input[type="text"],
input[type="email"],
input[type="password"],
input[type="number"],
select,
textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #000000;
}
```

Explanation:

- All inputs look consistent.
- Borders are black.
- Focus state adds stronger border and subtle shadow.

Error messages:

```css
.form-error {
    color: #000000;
    font-weight: 700;
    border-left: 3px solid #000000;
}
```

Why this works:

- Errors are visible without using red.
- The left border creates a clear visual signal.

### Tables

Tables are used heavily in admin pages.

Key design:

- Black header row.
- White text in headers.
- Light gray alternate rows.
- Horizontal overflow wrapper for small screens.

Purpose:

- Admin pages are data-heavy.
- Tables need to be scannable and clean.

### Cards

Cards are used for:

- Dashboard panels.
- Forms.
- Order details.
- Menu item display.

Design:

- Border only.
- White background.
- No flashy colors.

### Menu Cards

Menu cards show:

- Image.
- Category.
- Name.
- Description.
- Price.
- Order action.

Images use:

```css
filter: grayscale(100%);
```

Explanation:

- Even uploaded color images are displayed in monochrome style.
- This enforces visual consistency.

### Alerts and Badges

Alerts use monochrome borders and backgrounds.

Badges show status:

- Pending: light gray background.
- Confirmed: black background.
- Cancelled: white with line-through.

Why this matters:

- Status is visually clear without violating monochrome rules.

### Responsive Design

Media query:

```css
@media (max-width: 640px) {
    ...
}
```

Responsibilities:

- Stack navbar content.
- Reduce hero heading size.
- Make menu grid one column.
- Make action buttons full width where needed.

Defense point:

- The project is usable on smaller screens, not only desktop.

## 2.3 JavaScript Deep-Dive

### Strict Mode

```javascript
'use strict';
```

Explanation:

- Enables stricter JavaScript parsing.
- Helps catch common mistakes.

### Required Field Validation

```javascript
function validateRequired(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    if (!field || !error) return true;
    const value = field.value.trim();
    if (value === '') {
        error.textContent = 'This field is required.';
        field.style.borderColor = '#000000';
        field.style.borderWidth = '2px';
        return false;
    }
    error.textContent = '';
    field.style.borderColor = '';
    field.style.borderWidth = '';
    return true;
}
```

Explanation:

- Finds the field and error span by ID.
- If missing, it returns true so pages without that field do not break.
- Trims value and checks empty state.
- Shows error and strengthens border.
- Clears error when valid.

Why defensive DOM checks:

- The same JS file runs on every page.
- Not every page has every form.

### Email Validation

```javascript
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
```

Explanation:

- Checks simple email format.
- Prevents obvious invalid emails before submitting.

Defense point:

- JavaScript validation improves UX, but PHP still validates server-side.

### File Validation

Functions:

- `validateFileType()`
- `validateFileSize()`

Purpose:

- Warn user before invalid file reaches server.
- Reduce unnecessary upload attempts.

Important:

- Server-side PHP remains the real security validation.

### DOMContentLoaded

```javascript
document.addEventListener('DOMContentLoaded', function () {
    ...
});
```

Explanation:

- Runs JS after the HTML is ready.
- Prevents querying elements before they exist.

### Register Form Validation

The script checks:

- Name required.
- Email valid.
- Password required.
- Password minimum length.
- Confirm password matches.

Why:

- Improves user experience.
- Reduces avoidable server submissions.

### Login Form Validation

The script checks:

- Email valid.
- Password required.

### Menu Item Form Validation

The script checks:

- Name required.
- Price required.
- Category required.
- Image type and size if image selected.

Important:

- It supports both create and edit forms because both use `menu-item-form`.

### PDF Upload Validation

The script checks:

- A PDF is selected.
- Extension is `pdf`.
- Size is under 5MB.

### Order Form Validation

The script checks:

- Quantity is between 1 and 20.

### Flash Message Auto-Dismiss

Alerts disappear after 4 seconds.

Purpose:

- Keeps the interface clean.
- Gives feedback without requiring manual close buttons.

### Delete Confirmation

Danger buttons trigger a browser confirm dialog.

Purpose:

- Prevent accidental deletion.

Important:

- This is only a UX feature.
- The server still validates POST, CSRF, admin role, and order dependencies.

### Price Preview

The order form reads:

- Selected item `data-price`.
- Quantity input.

Then shows:

```text
Estimated Total: $...
```

Important:

- This is only a preview.
- Final total is calculated server-side from the database.

### Image Preview

When admin selects an image file, JavaScript displays a preview.

Purpose:

- Helps admin confirm they chose the correct image.

## 2.4 Design Decisions for Task 3

### Why one global CSS file?

The project is small. One file is simpler to maintain and avoids complex build tools.

### Why one global JS file?

The project does not require bundlers or modules. One JS file is easy to include in the shared footer.

### Why monochrome?

The project specification requires a black-and-white design with clean spacing.

### Why JavaScript is not trusted for security?

Browser code can be disabled or modified. PHP validation is the final authority.

## 2.5 Alternatives for Task 3

### Alternative: Bootstrap

Bootstrap could provide ready-made components.

Why not chosen:

- It would add external framework dependency.
- It may conflict with strict monochrome custom design.

### Alternative: Tailwind CSS

Tailwind could provide utility classes.

Why not chosen:

- It requires build/config setup.
- The project requirement favors simple Core PHP and local files.

### Alternative: Multiple JS Files

Separate scripts per page could reduce unused JS.

Why not chosen:

- One small global file is easier for the team to explain.
- The script checks whether elements exist before acting.

## 2.6 Dependencies for Task 3

Task 3 depends on Task 2:

- Asset folders must exist.

Task 3 supports Task 4:

- `header.php` links to `assets/css/style.css`.
- `footer.php` loads `assets/js/main.js`.

Task 3 supports all UI tasks:

- Admin pages.
- Auth pages.
- User pages.
- Landing page.

---

## 3. Task 12: Menu Items Delete

## 3.1 Task Objective

Task 12 allows admins to delete menu items safely.

The feature must:

- Be admin-only.
- Accept POST only.
- Validate CSRF token.
- Validate item ID.
- Fetch item before deletion.
- Block deletion if existing orders reference the item.
- Delete the database row if safe.
- Delete the associated image file if it exists.
- Redirect with flash messages.

## 3.2 File Responsible

```text
admin/menu_items/delete.php
```

## 3.3 Code Deep-Dive

### Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
```

Explanation:

- Starts app context.
- Starts session.
- Loads auth helpers.
- Loads database connection.

This file outputs no HTML. It is a pure action handler.

### Authorization

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- Guests cannot delete items.
- Users cannot delete items.
- Only admins can continue.

### POST-Only Check

```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
```

Explanation:

- Delete must not happen through GET links.
- Direct URL access redirects back to list.

Why POST only:

- GET should be safe and not change data.
- Delete is destructive, so it requires form submission.

### CSRF Validation

```php
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Invalid request token. Please try again.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- Checks session token exists.
- Compares submitted token with `hash_equals()`.
- Invalid request is rejected.

Why `hash_equals()`:

- It performs timing-safe comparison.
- This is a secure standard for comparing tokens.

### Item ID Validation

```php
$itemId = (int) ($_POST['id'] ?? 0);

if ($itemId <= 0) {
    setFlashMessage('danger', 'Invalid item ID. No item was deleted.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- Converts submitted ID to integer.
- Rejects missing or invalid IDs.

### Fetch Item Before Delete

```php
$stmtFetch = $conn->prepare('SELECT id, name, image_path FROM menu_items WHERE id = ? LIMIT 1');
$stmtFetch->bind_param('i', $itemId);
$stmtFetch->execute();
$resultFetch = $stmtFetch->get_result();
$item        = $resultFetch->fetch_assoc();
$stmtFetch->close();
```

Explanation:

- Fetches item data safely.
- Needs item name for message.
- Needs image path for file deletion.

Why fetch before delete:

- After deletion, image path would no longer be available from the database.

### Missing Item Handling

```php
if (!$item) {
    setFlashMessage('danger', 'Item not found. It may have already been deleted.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- If the item does not exist, deletion stops.
- Admin gets a clear message.

### Existing Orders Check

```php
$stmtCount = $conn->prepare('SELECT COUNT(*) FROM orders WHERE menu_item_id = ?');
$stmtCount->bind_param('i', $itemId);
$stmtCount->execute();
$stmtCount->bind_result($orderCount);
$stmtCount->fetch();
$stmtCount->close();

if ((int) $orderCount > 0) {
    setFlashMessage('danger', 'This item has existing orders. Mark it unavailable instead of deleting it.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- Counts how many orders reference this item.
- If count is greater than zero, deletion is blocked.
- Admin is told to mark item unavailable instead.

Why this business rule exists:

- Orders are historical records.
- Deleting referenced menu items would damage order history.
- The database also enforces `ON DELETE RESTRICT`.

Defense point:

- This is application-level protection plus database-level referential integrity.

### Delete Query

```php
$stmtDelete = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
$stmtDelete->bind_param('i', $itemId);
$stmtDelete->execute();
```

Explanation:

- Deletes only the selected item.
- Uses prepared statement.
- Prevents SQL injection.

### Affected Rows Check

```php
if ($stmtDelete->affected_rows === 0) {
    $stmtDelete->close();
    setFlashMessage('danger', 'No item was deleted. It may have already been removed.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- Confirms that a row was actually deleted.
- Handles race conditions or unexpected database state.

### Image File Cleanup

```php
if (!empty($item['image_path'])) {
    $absoluteImagePath = __DIR__ . '/../../' . $item['image_path'];
    if (file_exists($absoluteImagePath)) {
        unlink($absoluteImagePath);
    }
}
```

Explanation:

- If item had an image path, build absolute file path.
- Check whether file exists.
- Delete file from disk.

Why delete image:

- Prevent orphan files.
- Keep upload folder clean.

### Success Message

```php
$deletedName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
setFlashMessage('success', 'Menu item "' . $deletedName . '" was deleted successfully.');
header('Location: index.php');
exit;
```

Explanation:

- Escapes item name before putting it into the message.
- Redirects back to menu item list.

## 3.4 Design Decisions for Task 12

### Why no HTML output?

Delete is an action, not a page. It should process and redirect.

### Why block deletion when orders exist?

To preserve order history and satisfy data integrity.

### Why delete the image file?

The database row and image file should remain synchronized.

### Why use both JS confirm and server checks?

JavaScript confirm prevents accidents, but server checks enforce real security.

## 3.5 Alternatives for Task 12

### Alternative: Soft Delete

Instead of deleting, add `deleted_at` column.

Why not chosen:

- The schema is restricted.
- Existing `is_available` already supports hiding items.

### Alternative: Allow Cascade Delete

Deleting a menu item could delete all related orders.

Why not chosen:

- It causes data loss.
- It is bad for business history.

### Alternative: Delete Through GET Link

Why not chosen:

- GET should not change data.
- Search engines, previews, or accidental clicks could trigger deletion.

## 3.6 Dependencies for Task 12

Task 12 depends on:

- Task 1 for `menu_items` and `orders`.
- Task 2 for database connection.
- Task 4 for auth and flash helpers.
- Task 9 because delete buttons are shown in the menu item list.
- Task 10 because items must exist before deletion.

Task 12 supports:

- Data cleanup.
- Admin menu management.
- Security hardening through POST and CSRF.

## 4. Habiba Defense Questions and Answers

### Q1: Why use a monochrome design?

Because the project specification requires a black-and-white clean UI. The design uses contrast, spacing, typography, and borders instead of flashy colors.

### Q2: Why use JavaScript if PHP validates everything?

JavaScript improves user experience by giving immediate feedback, but PHP remains the security authority.

### Q3: Why is delete POST only?

Because delete changes data. GET requests should be safe and should not perform destructive actions.

### Q4: Why block deletion when orders exist?

Because orders are historical records and must not lose their menu item reference.

### Q5: Why delete the image file after deleting the item?

Because otherwise the upload directory would contain unused files not connected to any database row.

### Q6: Why use grayscale images?

Uploaded images may be colorful, but the UI must remain monochrome. CSS grayscale keeps visual consistency.

## 5. Habiba Summary

Habiba's work improved both presentation and safety.

Task 3 delivered:

- Global CSS.
- Monochrome design.
- Responsive layout.
- Form/table/card styling.
- Client-side validation.
- UI helpers like alerts, confirmations, image preview, and price preview.

Task 12 delivered:

- Secure delete action.
- Admin-only protection.
- POST-only enforcement.
- CSRF validation.
- Order dependency check.
- Image cleanup.

In defense, Habiba should emphasize that UI polish and backend safety both matter: the system must look consistent and also protect important data.


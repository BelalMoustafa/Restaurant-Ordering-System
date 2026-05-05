# Hamza Defense Notes

## 1. Assigned Tasks

Hamza was responsible for:

- Task 3: Global Assets, CSS and JavaScript.
- Task 12: Menu Items Delete.

These tasks cover two important parts of the system:

- Task 3 creates the shared visual and JavaScript layer used across the full application.
- Task 12 creates the secure admin action for deleting menu items without damaging order history.

Hamza's work connects frontend consistency with backend safety. The CSS and JavaScript make the system clear and usable, while the delete handler protects the database from unsafe deletion.

---

## 2. Task 3: Global Assets, CSS and JavaScript

## 2.1 Task Objective

The objective of Task 3 was to create the global frontend foundation for the Restaurant Ordering System.

The task required:

- A complete monochrome CSS design system.
- Clean page layout styles.
- Form styling.
- Button styling.
- Table styling.
- Menu card styling.
- Dashboard and panel styling.
- Responsive layout behavior.
- JavaScript helpers for client-side validation and UI behavior.

Main files:

```text
assets/css/style.css
assets/js/main.js
```

## 2.2 CSS Architecture Deep-Dive

### Reset and Base Styles

The stylesheet starts with a universal reset:

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

- `box-sizing: border-box` makes width calculations predictable.
- `margin: 0` removes browser default spacing.
- `padding: 0` removes browser default internal spacing.
- Applying the reset to pseudo-elements prevents layout inconsistencies.

Why this matters:

- Different browsers apply different default styles.
- A reset gives the project a consistent starting point.

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

- `font-size: 16px` creates a readable base.
- `scroll-behavior: smooth` improves anchor navigation.
- The body uses a clean sans-serif font stack.
- The background is white and text is black.

Defense point:

- This directly supports the project rule that the interface must remain monochrome and clean.

### Typography

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

- Headings use a serif font for a restaurant-style identity.
- Body text uses a sans-serif font for readability.
- The project creates visual hierarchy without depending on colorful design.

Why this approach works:

- It keeps the UI black and white.
- It still gives headings a distinct visual personality.

### Container and Layout

```css
.container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 20px;
    width: 100%;
}
```

Explanation:

- `max-width` prevents content from stretching too wide.
- `margin: 0 auto` centers the content.
- `padding: 0 20px` keeps content away from screen edges.
- `width: 100%` lets the container shrink on small screens.

The layout also uses wrappers such as:

```text
page-wrapper
main-content
```

Purpose:

- Keep the footer at the bottom.
- Give all pages consistent spacing.

### Navigation

The navigation uses a black background with white text:

```css
.navbar {
    background-color: #000000;
    color: #ffffff;
}
```

Explanation:

- The navbar has strong contrast.
- It clearly separates navigation from page content.
- It matches the monochrome rule.

Active links are shown using underline/border styling rather than color.

Defense point:

- The design communicates state using layout, weight, borders, and spacing instead of flashy colors.

### Buttons

Buttons are based on a shared `.btn` class and variants:

```text
btn
btn-primary
btn-secondary
btn-danger
btn-sm
```

Primary buttons use:

```css
.btn-primary {
    background-color: #000000;
    color: #ffffff;
}
```

Explanation:

- Primary actions are black with white text.
- Hover states invert or strengthen the visual style.
- Buttons remain consistent across admin, auth, and user pages.

Why danger buttons are still monochrome:

- The UI rule does not allow flashy colors.
- Destructive actions are shown through border weight, label, and confirmation behavior.

### Forms

Form controls share one visual style:

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

- Inputs fill their container width.
- Padding makes fields comfortable to use.
- Borders make fields visible on a white background.

Focus states use stronger borders and subtle shadow.

Defense point:

- The form design improves usability while keeping the project visually minimal.

### Tables

Admin pages rely on tables for:

- Menu item lists.
- Order lists.
- Summary data.

The table design uses:

- Black header rows.
- White header text.
- Light gray alternating rows.
- Clear cell padding.
- Border lines for separation.

Why this matters:

- Admin pages are data-heavy.
- Tables need to be scannable during repeated use.

### Cards and Panels

Cards are used for:

- Dashboard stat blocks.
- Form containers.
- Order details.
- Menu item panels.

The style is intentionally simple:

- White background.
- Black border.
- Clean spacing.
- No gradients or decorative colors.

### Menu Cards

Menu cards show:

- Item image.
- Category.
- Name.
- Description.
- Price.
- Action button.

Images are displayed with grayscale filtering:

```css
filter: grayscale(100%);
```

Explanation:

- Uploaded images may contain color.
- The CSS filter keeps the public menu aligned with the monochrome design rule.

### Alerts and Badges

Alerts communicate:

- Success messages.
- Error messages.
- Informational messages.

Badges communicate order status:

- Pending.
- Confirmed.
- Cancelled.

The design uses borders, background contrast, and text weight instead of bright colors.

### Responsive Design

The CSS includes media queries for smaller screens.

Responsibilities:

- Stack navigation items.
- Reduce large heading sizes.
- Convert grids into single-column layouts.
- Prevent tables and buttons from overflowing.

Defense point:

- The application is not limited to desktop display. It remains usable on smaller screens.

## 2.3 JavaScript Deep-Dive

### Strict Mode

```javascript
'use strict';
```

Explanation:

- Enables stricter JavaScript parsing.
- Helps catch accidental global variables.
- Makes the JavaScript safer and more predictable.

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

- Finds the input field by ID.
- Finds the error message container by ID.
- If either element is missing, the function returns true so other pages do not break.
- Trims whitespace from the field value.
- Displays an error when the field is empty.
- Clears the error when the field is valid.

Why the missing-element check matters:

- `main.js` is loaded globally.
- Not every page has every form.
- Defensive checks prevent JavaScript errors on unrelated pages.

### Email Validation

The script validates email format with a regular expression:

```javascript
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
```

Explanation:

- Requires text before `@`.
- Requires a domain after `@`.
- Requires a final extension.

Defense point:

- JavaScript validation improves user experience, but PHP validation remains the real security layer.

### Minimum Length Validation

Password and other length-sensitive fields use a minimum length helper.

Purpose:

- Prevent obviously weak or incomplete input.
- Give feedback before the form reaches PHP.

### File Validation

The JavaScript file includes helpers for:

```text
validateFileType()
validateFileSize()
```

Purpose:

- Warn admins before uploading invalid files.
- Improve user experience for image and PDF uploads.
- Reduce unnecessary server requests.

Important defense point:

- Client-side file validation is not trusted for security.
- PHP still validates MIME type, extension, and size server-side.

### DOMContentLoaded

```javascript
document.addEventListener('DOMContentLoaded', function () {
    ...
});
```

Explanation:

- Runs JavaScript only after the page HTML is loaded.
- Prevents the script from trying to access elements before they exist.

### Registration Form Validation

The script checks:

- Name is required.
- Email format is valid.
- Password is required.
- Password meets minimum length.
- Confirm password matches password.

### Login Form Validation

The script checks:

- Email format is valid.
- Password is required.

### Menu Item Form Validation

The script checks:

- Name is required.
- Price is required.
- Category is required.
- Image type is valid.
- Image size is valid.

This supports both:

```text
admin/menu_items/create.php
admin/menu_items/edit.php
```

### PDF Upload Validation

The script checks:

- A PDF was selected.
- The extension is `.pdf`.
- File size does not exceed the allowed limit.

### Order Form Validation

The script checks:

- A menu item is selected.
- Quantity is between 1 and 20.

### Live Price Preview

The order form uses JavaScript to display an estimated total.

The script reads:

- Selected option `data-price`.
- Quantity input value.

Then it displays:

```text
Estimated Total: $X.XX
```

Important:

- This is only a preview.
- The backend recalculates the real total from the database.

### Flash Message Auto-Dismiss

Alerts are automatically faded out after a few seconds.

Purpose:

- Keeps the UI clean.
- Avoids old messages staying on screen too long.

### Delete Confirmation

Danger buttons trigger a confirmation prompt before submission.

Purpose:

- Reduces accidental destructive actions.

Important:

- This is only a usability feature.
- Real delete protection is enforced by PHP, CSRF, admin checks, and database rules.

## 2.4 Design Decisions for Task 3

### Why one global CSS file?

The project is small and does not need a CSS build pipeline.

Benefits:

- Easy to include.
- Easy for the team to explain.
- No external dependency.
- Consistent design across every page.

### Why one global JS file?

The application does not require modules, bundlers, or page-specific scripts.

Benefits:

- Simple local execution.
- Works directly inside XAMPP/MAMP.
- Shared helper functions are available everywhere.

### Why monochrome?

The project specification requires a black-and-white UI with clean spacing.

Hamza's CSS uses:

- Borders.
- Spacing.
- Typography.
- Contrast.
- Hover states.

instead of colorful effects.

### Why not trust JavaScript for security?

Users can:

- Disable JavaScript.
- Edit JavaScript in the browser.
- Modify HTML with Inspect Element.
- Submit requests manually.

Therefore:

- JavaScript helps the user.
- PHP protects the system.

## 2.5 Alternatives for Task 3

### Alternative: Bootstrap

Bootstrap could provide ready-made styling and components.

Why not chosen:

- It is an external CSS framework.
- The project requires Core PHP and custom local assets.
- Bootstrap's default look may conflict with the strict monochrome design.

### Alternative: Tailwind CSS

Tailwind could provide utility classes.

Why not chosen:

- It usually requires configuration or a build step.
- The project is intended to run locally without extra tooling.

### Alternative: Multiple CSS Files

Possible structure:

```text
forms.css
tables.css
buttons.css
layout.css
```

Why not chosen:

- The project is small enough for one stylesheet.
- One file makes it easier for every page to share the same design.

### Alternative: Multiple JS Files

Possible structure:

```text
auth.js
menu.js
orders.js
uploads.js
```

Why not chosen:

- A single global file is simpler for this university project.
- Defensive DOM checks prevent errors on pages that do not use every function.

## 2.6 Dependencies for Task 3

### Depends on Task 2: Project Skeleton

Task 3 depends on the folders created in Task 2:

```text
assets/css/
assets/js/
```

Without these directories, the CSS and JavaScript files would not have a correct location.

### Supports Task 4: Shared Includes

Task 4 links the assets:

```text
includes/header.php
includes/footer.php
```

The header links to:

```text
assets/css/style.css
```

The footer loads:

```text
assets/js/main.js
```

### Supports All Feature Tasks

Task 3 supports:

- Authentication pages.
- Admin dashboard.
- Menu item CRUD.
- PDF upload.
- User menu.
- Place order.
- My orders.
- Landing page.

Defense point:

- Once Task 3 is complete, every later page can use the same polished design and shared JavaScript helpers.

---

## 3. Task 12: Menu Items Delete

## 3.1 Task Objective

The objective of Task 12 was to allow admins to delete menu items safely.

The feature had to:

- Be accessible only to logged-in admins.
- Accept POST requests only.
- Validate CSRF token.
- Validate the submitted menu item ID.
- Fetch the item before deletion.
- Block deletion if orders already reference the item.
- Delete the database row only when safe.
- Delete the uploaded image file when the item is deleted.
- Redirect with flash messages.

Main file:

```text
admin/menu_items/delete.php
```

## 3.2 Why This Task Is Sensitive

Deleting a menu item is dangerous because orders may reference that item.

If deletion is handled incorrectly:

- Existing order history can lose meaning.
- Admin order records may break.
- User order history may point to missing data.
- Uploaded image files may remain unused.

Hamza's delete logic protects against these problems.

## 3.3 Code Deep-Dive

### Application Bootstrap

```php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
```

Explanation:

- `APP_RUNNING` marks the file as part of the application.
- `session_start()` allows access to login, role, flash, and CSRF session data.
- `auth.php` provides authentication, authorization, CSRF, and flash helpers.
- `db.php` provides the MySQLi connection.

Defense point:

- This file must run all security checks before performing the delete.

### Authorization

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- `requireLogin()` blocks guests.
- `requireAdmin()` blocks regular users.
- Only admins can delete menu items.

Why this matters:

- Normal users should only browse and place orders.
- Menu management is an admin-only business operation.

### POST-Only Enforcement

```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
```

Explanation:

- Direct URL access uses GET and is rejected.
- Delete must happen only through a submitted form.
- The script redirects back to the menu item list.

Why POST only:

- GET requests should be safe.
- Delete changes data.
- A delete action should never be triggered by simply visiting a URL.

### CSRF Validation

```php
requireValidCsrf('index.php');
```

Explanation:

- The submitted CSRF token must match the session token.
- If the token is missing or invalid, the request is rejected.
- The user is redirected back to the list with an error message.

Why this matters:

- A malicious site should not be able to force a logged-in admin to delete an item.

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

- Reads the item ID from POST.
- Converts it to an integer.
- Rejects missing, zero, or invalid IDs.

Why this is needed:

- The delete handler should not run a database delete with an invalid identifier.

### Fetch Item Before Delete

```php
$stmtFetch = $conn->prepare('SELECT id, name, image_path FROM menu_items WHERE id = ? LIMIT 1');
$stmtFetch->bind_param('i', $itemId);
$stmtFetch->execute();
$resultFetch = $stmtFetch->get_result();
$item = $resultFetch->fetch_assoc();
$stmtFetch->close();
```

Explanation:

- Uses a MySQLi prepared statement.
- Fetches the item name and image path.
- The image path is needed before the row is deleted.

Why prepared statements:

- The submitted ID comes from the request.
- Binding it as an integer prevents SQL injection.

### Missing Item Handling

```php
if (!$item) {
    setFlashMessage('danger', 'Item not found. It may have already been deleted.');
    header('Location: index.php');
    exit;
}
```

Explanation:

- If no item exists for that ID, deletion stops.
- The admin receives a clear message.

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

- Counts orders linked to the menu item.
- If the item has orders, deletion is blocked.
- Admin is told to mark the item unavailable instead.

Why this is important:

- Orders are historical records.
- Deleting ordered items would damage order history.
- The database also protects this through `ON DELETE RESTRICT`.

Defense point:

- The project protects data integrity at both the application level and the database level.

### Delete Query

```php
$stmtDelete = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
$stmtDelete->bind_param('i', $itemId);
$stmtDelete->execute();
```

Explanation:

- Deletes only the requested item.
- Uses a prepared statement.
- Does not concatenate user input into SQL.

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

- Confirms that the database actually deleted a row.
- Handles unexpected cases such as the row being removed by another request.

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

- Checks whether the item had an uploaded image.
- Builds the absolute file path.
- Deletes the file if it exists.

Why this matters:

- Prevents unused image files from accumulating.
- Keeps the upload folder consistent with the database.

### Success Message and Redirect

```php
$deletedName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
setFlashMessage('success', 'Menu item "' . $deletedName . '" was deleted successfully.');
header('Location: index.php');
exit;
```

Explanation:

- Escapes the item name before displaying it in a flash message.
- Redirects back to the menu item list.
- Stops script execution.

Why escape the name:

- Menu item names come from the database.
- Escaping prevents stored XSS in messages.

## 3.4 Design Decisions for Task 12

### Why no visible HTML page?

Delete is an action handler, not a display page.

It should:

- Validate.
- Process.
- Redirect.

This follows the POST-Redirect-GET pattern.

### Why block deletion when orders exist?

Order history must remain understandable.

If a menu item was ordered before, the item should remain in the database so:

- Admins can still understand old orders.
- Users can still understand their own order history.
- Foreign keys remain valid.

### Why recommend marking unavailable?

The `menu_items.is_available` column already supports hiding items from users.

This is safer than deleting because:

- New users cannot order the item.
- Old orders remain valid.

### Why delete image files?

If the database row is deleted but the file remains:

- Storage becomes messy.
- Old files no longer have meaning.
- The upload directory fills with unused files.

### Why both JavaScript confirmation and server validation?

JavaScript confirmation prevents accidental clicks.

Server validation enforces:

- Admin access.
- POST-only request.
- CSRF protection.
- Existing order rules.

## 3.5 Alternatives for Task 12

### Alternative: Delete Through GET Link

Why not chosen:

- GET should not change state.
- Direct links could accidentally delete data.
- Browser previews or crawlers could trigger GET links.

### Alternative: Cascade Delete Orders

Possible database rule:

```text
ON DELETE CASCADE
```

Why not chosen:

- It would delete order history.
- It creates data loss.
- It is not acceptable for an ordering system.

### Alternative: Soft Delete With `deleted_at`

Possible design:

```text
deleted_at TIMESTAMP NULL
```

Why not chosen:

- The schema is restricted to the current 3-table design.
- `is_available` already gives the team a simpler way to hide items.

### Alternative: Leave Image Files on Disk

Why not chosen:

- It causes unused files.
- It makes the project harder to maintain.
- File storage should match database state.

## 3.6 Dependencies for Task 12

### Depends on Task 1: SQL Schema

Task 12 uses:

```text
menu_items
orders
```

The order check depends on `orders.menu_item_id`.

### Depends on Task 2: Database Connection

The delete handler requires:

```text
config/db.php
```

which provides:

```php
$conn
```

### Depends on Task 4: Auth Helpers

Task 12 depends on:

```text
includes/auth.php
```

for:

- `requireLogin()`
- `requireAdmin()`
- `requireValidCsrf()`
- `setFlashMessage()`

### Depends on Task 9: Menu Items List

The delete button is shown on the menu items list page.

Task 12 receives its POST submission from that list.

### Depends on Task 10: Menu Items Create

Items must exist before they can be deleted.

Task 10 creates those records.

### Supports Task 14 and Task 17

By preventing deletion of ordered menu items, Task 12 protects:

- Admin order processing.
- User order history.

## 4. Hamza Defense Questions and Answers

### Q1: Why did you create one global CSS file?

Because the project is small, uses no frontend build tools, and needs one consistent design system across every page.

### Q2: Why is the UI monochrome?

The project specification requires a strict black-and-white design. The CSS uses spacing, borders, typography, and contrast instead of flashy colors.

### Q3: Why is JavaScript validation not enough?

Because users can disable or modify JavaScript. Server-side PHP validation is the real security authority.

### Q4: Why make images grayscale?

Uploaded images may contain many colors. The grayscale filter keeps the public menu visually consistent with the monochrome requirement.

### Q5: Why is delete POST only?

Because delete changes data. GET requests should be safe and should not perform destructive actions.

### Q6: Why check existing orders before deleting a menu item?

Because existing orders are historical records. Deleting the menu item would damage order history and break the meaning of previous orders.

### Q7: Why use CSRF protection on delete?

Because delete is a state-changing admin action. CSRF protection prevents another website from forcing a logged-in admin to submit a delete request.

### Q8: Why delete the uploaded image file too?

Because once the menu item is deleted, its uploaded image is no longer needed. Removing it keeps the upload folder clean.

## 5. Hamza Summary

Hamza's updated responsibilities are Task 3 and Task 12.

Task 3 delivered:

- Global CSS.
- Monochrome visual system.
- Responsive layouts.
- Reusable buttons, forms, tables, cards, and alerts.
- JavaScript validation helpers.
- Flash message behavior.
- Delete confirmation behavior.
- Price preview and image preview helpers.

Task 12 delivered:

- Secure admin-only delete handling.
- POST-only enforcement.
- CSRF validation.
- Menu item existence checks.
- Existing order protection.
- MySQLi prepared delete query.
- Uploaded image cleanup.
- Flash messages and redirects.

In defense, Hamza should emphasize:

```text
Task 3 makes the system consistent and usable.
Task 12 makes destructive admin actions safe and data-aware.
```

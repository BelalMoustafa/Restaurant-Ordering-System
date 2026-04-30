# Abd El-Rahman Yasser Defense Notes

## 1. Assigned Tasks

Abd El-Rahman Yasser was responsible for:

- Task 9: Menu Items List and Read.
- Task 18: Public Landing Page.

These tasks focus on displaying menu data:

- Task 9 gives admins a full management list.
- Task 18 gives guests and users a public-facing menu preview.

## 2. Task 9: Menu Items List and Read

## 2.1 Task Objective

The objective of Task 9 was to create the admin menu items listing page.

The page had to:

- Be admin-only.
- Fetch all menu items.
- Display each item in a table.
- Show image thumbnails.
- Show price and availability.
- Provide Edit and Delete actions.
- Generate CSRF token for delete forms.
- Escape all dynamic output.

## 2.2 File Responsible

```text
admin/menu_items/index.php
```

## 2.3 Code Deep-Dive

### Page Setup

```php
define('APP_RUNNING', true);
$pageTitle = 'Menu Items';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db.php';
```

Explanation:

- Defines app context.
- Sets page title.
- Loads shared header.
- Loads database connection.

### Admin Protection

```php
requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');
```

Explanation:

- Guests are redirected to login.
- Normal users are redirected away.
- Only admins can view the management list.

### CSRF Token Creation

```php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

Explanation:

- Ensures delete forms have a CSRF token.
- The token is later submitted to `delete.php`.

Why list page creates token:

- Delete buttons are rendered on this page.
- Each delete form must include a valid token.

### Fetch Menu Items Query

```php
$stmtItems = $conn->prepare(
    'SELECT id, name, category, price, image_path, is_available, created_at
     FROM menu_items
     ORDER BY created_at DESC'
);
```

Explanation:

- Fetches all menu items.
- Includes only columns needed by the list page.
- Orders newest items first.

Why all items:

- Admins need to see both available and hidden items.

### Execute and Fetch

```php
$stmtItems->execute();
$result    = $stmtItems->get_result();
$menuItems = $result->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();
```

Explanation:

- Executes the query.
- Gets result set.
- Fetches all rows into `$menuItems`.
- Closes statement.

### Page Header

```php
<h1>Menu Items</h1>
<a href="create.php" class="btn btn-primary">+ Add New Item</a>
```

Explanation:

- Shows page purpose.
- Provides direct link to Task 10 create page.

### Empty State

If no items exist:

```text
No menu items found.
Add Your First Item
```

Purpose:

- Avoids an empty table.
- Guides admin to create the first item.

### Menu Items Table

The table columns are:

- Row number.
- Image.
- Name.
- Category.
- Price.
- Available.
- Actions.

This layout gives admins all important item information at a glance.

### Image Display

```php
<?php if (!empty($item['image_path'])): ?>
    <img src="<?= htmlspecialchars($basePath . $item['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" class="img-thumbnail">
<?php else: ?>
    <span class="text-muted" style="font-size:0.8rem;">No Image</span>
<?php endif; ?>
```

Explanation:

- If image path exists, show thumbnail.
- If not, show `No Image`.
- `src` and `alt` are escaped.

Why escape image path:

- File paths come from database.
- Escaping prevents malformed HTML.

### Price Formatting

```php
$<?= number_format((float) $item['price'], 2) ?>
```

Explanation:

- Casts price to float.
- Formats with two decimals.
- Displays consistent currency format.

### Availability Badge

```php
<?php if ($item['is_available']): ?>
    <span class="badge badge-confirmed">Yes</span>
<?php else: ?>
    <span class="badge badge-cancelled">No</span>
<?php endif; ?>
```

Explanation:

- Shows whether item is visible to users.
- Uses CSS badge styling.

Business meaning:

- Available items appear on public/user menu.
- Unavailable items are hidden but preserved.

### Edit Action

```php
<a href="edit.php?id=<?= (int) $item['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
```

Explanation:

- Links to Task 11 edit page.
- Casts ID to integer.

### Delete Action

```php
<form method="POST" action="delete.php" style="display:inline;">
    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Are you sure you want to delete ...">Delete</button>
</form>
```

Explanation:

- Delete uses POST, not GET.
- Sends item ID.
- Sends CSRF token.
- Uses confirmation prompt.

Why POST:

- Delete changes data.
- GET must not perform destructive actions.

Why CSRF:

- Prevents forged delete requests.

## 2.4 Design Decisions for Task 9

### Why table layout?

Admin management pages need dense, scannable data. Tables are ideal for comparing multiple records.

### Why show hidden items?

Admins need to manage all records, not just customer-visible ones.

### Why generate CSRF token here?

The delete forms are rendered here, so the token must be available here.

### Why show thumbnails?

Images help admins identify items quickly.

## 2.5 Alternatives for Task 9

### Alternative: Card Layout

Why not chosen:

- Cards are better for customers.
- Admins need tabular management controls.

### Alternative: Delete Link With GET

Why not chosen:

- It would be unsafe.
- Deletion must be POST with CSRF token.

### Alternative: Pagination

Why not chosen:

- The demo dataset is small.
- Pagination is not required by the specification.

## 2.6 Dependencies for Task 9

Task 9 depends on:

- Task 1 for `menu_items`.
- Task 2 for database connection.
- Task 3 for table and badge styling.
- Task 4 for auth, header, footer, and CSRF session.
- Task 6 for admin login.

Task 9 supports:

- Task 10 create navigation.
- Task 11 edit navigation.
- Task 12 delete forms.

---

## 3. Task 18: Public Landing Page

## 3.1 Task Objective

The objective of Task 18 was to create the public landing page.

The page had to:

- Be accessible to guests.
- Show restaurant branding.
- Show menu preview.
- Link guests to login/register.
- Link logged-in users to dashboard.
- Show PDF menu download if uploaded.
- Escape dynamic menu output.

## 3.2 File Responsible

```text
index.php
```

## 3.3 Code Deep-Dive

### Page Setup

```php
define('APP_RUNNING', true);
$pageTitle = 'Welcome';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';
```

Explanation:

- Defines app context.
- Sets page title.
- Loads shared header.
- Loads database connection.

Important:

- This page does not call `requireLogin()` because it is public.

### Menu Preview Query

```php
$stmtPreview = $conn->prepare(
    'SELECT id, name, description, price, category, image_path
     FROM menu_items
     WHERE is_available = 1
     ORDER BY created_at DESC
     LIMIT 6'
);
```

Explanation:

- Selects available menu items only.
- Orders newest first.
- Limits preview to 6 items.

Why limit:

- Landing page should be concise.
- Full menu is available after login.

### Execute and Fetch

```php
$stmtPreview->execute();
$resultPreview = $stmtPreview->get_result();
$previewItems  = $resultPreview->fetch_all(MYSQLI_ASSOC);
$stmtPreview->close();
```

Explanation:

- Executes prepared query.
- Gets result.
- Fetches preview items.

### PDF and Dashboard Logic

```php
$pdfPath      = __DIR__ . '/uploads/pdfs/menu.pdf';
$pdfExists    = file_exists($pdfPath);
$dashboardUrl = isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php';
```

Explanation:

- Checks whether admin uploaded PDF menu.
- Chooses dashboard URL based on role.

Why role check on public page:

- Logged-in visitors should get a quick return path to their correct dashboard.

### Hero Section

The hero displays:

- Restaurant name.
- Short tagline.
- Guest actions or logged-in actions.

Guest actions:

- View Full Menu section.
- Login/Register.

Logged-in actions:

- Go to Dashboard.
- View Full Menu.

### Menu Section

If no preview items exist:

```text
Our menu is currently being updated. Please check back soon.
```

If preview items exist:

- Render menu cards.
- Show image or placeholder.
- Show category.
- Show name.
- Show short description.
- Show price.
- Show order/login action.

### Description Truncation

```php
$displayDesc = mb_strlen($desc) > 100
    ? htmlspecialchars(mb_substr($desc, 0, 100), ENT_QUOTES, 'UTF-8') . '&hellip;'
    : htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
```

Explanation:

- Keeps cards short.
- Escapes text before output.
- Uses ellipsis for long descriptions.

### Order/Login Button Logic

If logged in:

```php
<a href="user/place_order.php?item_id=<?= (int) $item['id'] ?>" class="btn btn-primary btn-sm">Order This</a>
```

If guest:

```php
<a href="auth/login.php" class="btn btn-secondary btn-sm">Login to Order</a>
```

Explanation:

- Guests are guided to login.
- Logged-in users can start ordering.

### Full Menu CTA

If logged in:

- Link to `user/menu.php`.

If guest:

- Link to login.
- Link to register.

### PDF Download Card

If PDF exists:

- Show a download button for `uploads/pdfs/menu.pdf`.

Why conditional:

- Avoid showing broken download links before admin uploads a PDF.

## 3.4 Design Decisions for Task 18

### Why public landing page?

Guests should understand the restaurant and see menu preview before registering.

### Why show only available items?

Unavailable items should not be advertised.

### Why limit to 6 items?

The landing page is a preview, not the full menu.

### Why use same menu card style as user menu?

It keeps visual consistency.

### Why dashboard link for logged-in users?

It improves navigation and prevents logged-in users from being treated like guests.

## 3.5 Alternatives for Task 18

### Alternative: Require Login Before Seeing Any Menu

Why not chosen:

- Public preview improves guest experience.
- The spec says landing page should show menu.

### Alternative: Show All Items on Landing Page

Why not chosen:

- The landing page would become too long.
- Full menu belongs on the user menu page.

### Alternative: Static HTML Menu

Why not chosen:

- Admin-created menu items should appear dynamically.
- Database-driven menu proves integration with admin CRUD.

## 3.6 Dependencies for Task 18

Task 18 depends on:

- Task 1 for `menu_items`.
- Task 2 for database connection.
- Task 3 for landing/menu styling.
- Task 4 for header/footer and auth state helpers.
- Task 10 because admins create menu items.
- Task 13 because PDF menu link depends on uploaded file.

Task 18 supports:

- Guest onboarding.
- Registration flow.
- Login flow.
- Public project demo.

## 4. Abd El-Rahman Yasser Defense Questions and Answers

### Q1: Why does the admin menu list show all items?

Admins need to manage both visible and hidden items.

### Q2: Why does delete use a form instead of a link?

Delete changes data, so it must use POST and CSRF protection.

### Q3: Why does landing page not require login?

It is the public entry point and must allow guests to preview the restaurant.

### Q4: Why limit the landing menu preview to 6 items?

To keep the page concise and encourage users to log in for the full menu.

### Q5: Why escape menu item names and descriptions?

Menu data is dynamic and could contain HTML-like text. Escaping prevents XSS.

### Q6: Why show Login to Order for guests?

Orders must belong to authenticated users, so guests are guided to login first.

## 5. Abd El-Rahman Yasser Summary

Abd El-Rahman Yasser handled menu display from both admin and public perspectives.

Task 9 delivered:

- Admin menu item table.
- Image thumbnails.
- Availability badges.
- Edit links.
- CSRF-protected delete forms.

Task 18 delivered:

- Public landing page.
- Menu preview.
- Guest login/register navigation.
- Logged-in dashboard navigation.
- PDF menu download link.

In defense, Abd El-Rahman Yasser should emphasize that his work connects the admin-managed menu to the public user experience.


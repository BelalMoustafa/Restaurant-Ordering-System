# Soud Karim Defense Notes

## 1. Assigned Tasks

Soud Karim was responsible for:

- Task 8: Admin Dashboard.
- Task 17: My Orders, Order History.

These tasks are both dashboard/reporting features:

- Task 8 gives admins a management overview.
- Task 17 gives users a personal order history overview.

Together, they show how the same order data serves different roles with different access rules.

## 2. Task 8: Admin Dashboard

## 2.1 Task Objective

The objective of Task 8 was to create the admin home page.

The admin dashboard had to:

- Be accessible only to admins.
- Show key statistics.
- Provide quick links to admin actions.
- Summarize menu and order state.
- Use MySQLi OO prepared statements.
- Escape dynamic output.

## 2.2 File Responsible

```text
admin/dashboard.php
```

## 2.3 Code Deep-Dive

### Page Setup

```php
define('APP_RUNNING', true);
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- `APP_RUNNING` marks the page as a valid application page.
- `$pageTitle` is used by the shared header.
- `header.php` loads the shared layout and navbar.
- `db.php` provides `$conn` for database queries.

Defense note:

- This dashboard is a read-only page, so it does not process POST requests.

### Admin Protection

```php
requireLogin();
requireAdmin();
```

Explanation:

- `requireLogin()` blocks guests.
- `requireAdmin()` blocks normal users.
- Only authenticated admins can see the dashboard.

Why both are important:

- Login proves there is a session.
- Admin check proves the session has the correct role.

### Total Menu Items Query

```php
$stmtTotalItems = $conn->prepare('SELECT COUNT(*) FROM menu_items');
$stmtTotalItems->execute();
$stmtTotalItems->bind_result($totalMenuItems);
$stmtTotalItems->fetch();
$stmtTotalItems->close();
$totalMenuItems = (int) $totalMenuItems;
```

Explanation:

- Counts every row in `menu_items`.
- Uses a prepared statement for consistency.
- `bind_result()` binds the count result into `$totalMenuItems`.
- `fetch()` retrieves the count.
- The value is cast to integer for safe display and arithmetic.

Business meaning:

- Shows the total number of menu items the restaurant has stored.

### Available Items Query

```php
$stmtAvailableItems = $conn->prepare('SELECT COUNT(*) FROM menu_items WHERE is_available = 1');
$stmtAvailableItems->execute();
$stmtAvailableItems->bind_result($availableItems);
$stmtAvailableItems->fetch();
$stmtAvailableItems->close();
$availableItems = (int) $availableItems;
```

Explanation:

- Counts only menu items visible to users.
- Uses `is_available = 1`.

Business meaning:

- Shows how many items customers can currently order.

### Total Orders Query

```php
$stmtTotalOrders = $conn->prepare('SELECT COUNT(*) FROM orders');
$stmtTotalOrders->execute();
$stmtTotalOrders->bind_result($totalOrders);
$stmtTotalOrders->fetch();
$stmtTotalOrders->close();
$totalOrders = (int) $totalOrders;
```

Explanation:

- Counts all orders in the system.
- Helps admins understand total order activity.

### Pending Orders Query

```php
$stmtPendingOrders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stmtPendingOrders->execute();
$stmtPendingOrders->bind_result($pendingOrders);
$stmtPendingOrders->fetch();
$stmtPendingOrders->close();
$pendingOrders = (int) $pendingOrders;
```

Explanation:

- Counts orders waiting for admin action.
- Pending status is the default when users place orders.

Business meaning:

- This is the most urgent dashboard number because pending orders need review.

### Safe Admin Name

```php
$adminName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
```

Explanation:

- Reads admin name from session.
- Escapes it before output.
- Falls back to `Admin` if missing.

Why escape session data:

- Session data originated from database/user input.
- Escaping prevents XSS.

### Dashboard UI

The dashboard displays:

- Welcome message.
- Current date.
- Total Menu Items.
- Available Items.
- Total Orders.
- Pending Orders.
- Quick Actions.
- System Overview.

Quick actions:

```text
Manage Menu Items
Add New Item
View All Orders
Upload PDF Menu
```

Why quick actions exist:

- The dashboard acts as an admin control center.
- Admins can move quickly to common management tasks.

### System Overview Logic

Menu coverage:

```php
<?= $availableItems ?> of <?= $totalMenuItems ?> items currently on the menu
```

Hidden items:

```php
<?= ($totalMenuItems - $availableItems) ?>
```

Explanation:

- Shows available versus hidden items.
- Helps admin understand menu visibility.

Pending order message:

- If pending orders exist, show review link.
- If not, show that no orders are pending.

## 2.4 Design Decisions for Task 8

### Why show counts instead of raw records?

The dashboard is for quick monitoring, not detailed management. Details are available through Menu Items and Orders pages.

### Why use separate queries?

Each statistic has a clear purpose. Separate queries make the logic easy to read and explain in defense.

### Why count pending orders?

Pending orders require admin action, so this is the most operationally important order count.

### Why escape admin name?

Any displayed dynamic value should be escaped to prevent XSS.

## 2.5 Alternatives for Task 8

### Alternative: One Combined Query

The dashboard could use subqueries in one SQL statement.

Why not chosen:

- Separate statements are easier to understand.
- Each metric can be explained independently.
- The project prioritizes clarity.

### Alternative: Charts

Charts could show order trends.

Why not chosen:

- Not required by the specification.
- Would add JavaScript/chart dependencies.

### Alternative: Auto-refresh Dashboard

Why not chosen:

- Local university demo does not need live polling.
- Manual refresh is enough.

## 2.6 Dependencies for Task 8

Task 8 depends on:

- Task 1 for `menu_items` and `orders` tables.
- Task 2 for `config/db.php`.
- Task 4 for auth helpers and shared layout.
- Task 6 because admin login sets the session role.

Task 8 supports:

- Admin menu management.
- Admin order processing.
- System monitoring during project demo.

---

## 3. Task 17: My Orders, Order History

## 3.1 Task Objective

The objective of Task 17 was to let users view their own order history.

The page had to:

- Be accessible only to authenticated users.
- Fetch only the logged-in user's orders.
- Join order rows with menu item names.
- Display quantity, total price, status, notes, and date.
- Show a total spent summary.
- Escape dynamic output.

## 3.2 File Responsible

```text
user/my_orders.php
```

## 3.3 Code Deep-Dive

### Page Setup

```php
define('APP_RUNNING', true);
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
```

Explanation:

- Defines app context.
- Sets page title.
- Loads shared header.
- Loads database connection.

### Login Protection

```php
requireLogin();
```

Explanation:

- Guests cannot view order history.
- A session is required because the query uses `$_SESSION['user_id']`.

Defense point:

- The order history is tied to authenticated identity.

### User Orders Query

```php
$stmtOrders = $conn->prepare(
    'SELECT
         orders.id, orders.quantity, orders.total_price, orders.status,
         orders.notes, orders.created_at,
         menu_items.name AS item_name
     FROM orders
     JOIN menu_items ON orders.menu_item_id = menu_items.id
     WHERE orders.user_id = ?
     ORDER BY orders.created_at DESC'
);
```

Explanation:

- Selects order fields.
- Joins `menu_items` so the page can show item names.
- Filters by `orders.user_id = ?`.
- Orders newest first.

Most important security rule:

- The user ID comes from session, not from GET or POST.

### Binding User ID

```php
$stmtOrders->bind_param('i', $_SESSION['user_id']);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
$myOrders     = $resultOrders->fetch_all(MYSQLI_ASSOC);
$stmtOrders->close();
```

Explanation:

- Binds logged-in user's ID.
- Executes query.
- Fetches all personal orders.

Why this protects privacy:

- Users cannot change a URL parameter to see another user's orders.
- The filter is based on trusted session data.

### Total Spent Query

```php
$stmtTotal = $conn->prepare('SELECT COALESCE(SUM(total_price), 0.00) FROM orders WHERE user_id = ?');
$stmtTotal->bind_param('i', $_SESSION['user_id']);
$stmtTotal->execute();
$stmtTotal->bind_result($totalSpent);
$stmtTotal->fetch();
$stmtTotal->close();
$totalSpent = (float) $totalSpent;
```

Explanation:

- Sums total price of current user's orders.
- `COALESCE(..., 0.00)` returns zero if no orders exist.
- Result is cast to float for formatting.

Business meaning:

- Gives user a summary of spending across all their orders.

### Status Badge Function

```php
function statusBadgeClass(string $status): string
{
    return match ($status) {
        'confirmed' => 'badge-confirmed',
        'cancelled' => 'badge-cancelled',
        default     => 'badge-pending',
    };
}
```

Explanation:

- Maps order status to CSS badge class.
- Keeps UI status display consistent with admin pages.

### Empty State

If user has no orders:

```text
You haven't placed any orders yet.
Browse Menu
```

Purpose:

- Gives user a clear next step.
- Avoids showing an empty table.

### Order Table

The table displays:

- Order ID.
- Item name.
- Quantity.
- Total.
- Status.
- Notes.
- Date.

### Output Escaping

Examples:

```php
htmlspecialchars($order['item_name'], ENT_QUOTES, 'UTF-8')
htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8')
```

Explanation:

- Item names and notes may contain user-entered/admin-entered data.
- Escaping prevents XSS.

### Notes Truncation

```php
$noteDisplay = mb_strlen($order['notes']) > 40
    ? htmlspecialchars(mb_substr($order['notes'], 0, 40), ENT_QUOTES, 'UTF-8') . '&hellip;'
    : htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8');
```

Explanation:

- Long notes are shortened in the table.
- Full note is available in the title attribute.
- Output is escaped before display.

### Total Spent Card

The page shows:

- Total spent.
- Count of orders.

Purpose:

- Gives the user a summary of activity.

## 3.4 Design Decisions for Task 17

### Why users only see their own orders?

Order history contains personal customer data. Showing another user's orders would be a privacy issue.

### Why join with `menu_items`?

The order table stores `menu_item_id`, but users need readable item names.

### Why total spent summary?

It improves the usefulness of the order history page.

### Why truncate notes?

Long notes can break table layout. Truncation keeps the table readable.

## 3.5 Alternatives for Task 17

### Alternative: Admin-style Detail View for Users

Users could click each order for full details.

Why not chosen:

- Not required by the specification.
- The table already contains the necessary order history.

### Alternative: Fetch User ID From URL

Example:

```text
my_orders.php?user_id=5
```

Why not chosen:

- Users could change the ID and view others' orders.
- Session is the trusted identity source.

### Alternative: Show Cancel Button

Why not chosen:

- The spec does not require user-side cancellation.
- Admin controls order status.

## 3.6 Dependencies for Task 17

Task 17 depends on:

- Task 1 for `orders` and `menu_items`.
- Task 2 for database connection.
- Task 3 for table and badge styling.
- Task 4 for login protection and layout.
- Task 6 for sessions.
- Task 16 because orders must exist before history is meaningful.

Task 17 supports:

- Customer transparency.
- End-to-end proof that order placement works.

## 4. Soud Karim Defense Questions and Answers

### Q1: Why does the admin dashboard use counts?

Counts give admins a fast system overview without reading detailed tables.

### Q2: Why show pending orders separately?

Pending orders require admin attention, so they deserve their own statistic.

### Q3: Why does My Orders use `WHERE orders.user_id = ?`?

To ensure users see only their own orders.

### Q4: Why use session user ID instead of request user ID?

The session is trusted after login. Request parameters can be manipulated.

### Q5: Why join `orders` with `menu_items`?

Orders store item IDs, but users need readable item names.

### Q6: Why escape notes and item names?

Notes and item names can contain user-entered text. Escaping prevents XSS.

## 5. Soud Karim Summary

Soud Karim implemented role-specific reporting.

Task 8 delivered:

- Admin dashboard.
- Total menu item count.
- Available menu item count.
- Total order count.
- Pending order count.
- Quick action navigation.

Task 17 delivered:

- User order history.
- User-only order filtering.
- Order status display.
- Notes handling.
- Total spent summary.

In defense, Soud Karim should emphasize that his pages transform raw database records into useful dashboards while preserving role-based privacy.


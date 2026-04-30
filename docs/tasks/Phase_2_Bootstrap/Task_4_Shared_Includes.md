# Task 4 — Shared Includes (Header, Footer & Auth Helpers)

## Assignment

| Field              | Detail                                        |
|--------------------|-----------------------------------------------|
| **Assigned To**    | Alaa                                          |
| **Reviewed By**    | Belal Moustafa                                |
| **Phase**          | Phase 2 — Project Bootstrap                   |
| **Status**         | Pending                                       |
| **Depends On**     | Task 2 (directories), Task 3 (style.css path) |
| **Blocks**         | Tasks 5–19 (every page uses these includes)   |

---

## Objective
Write the three shared PHP include files that form the backbone of every page in the application:
- `includes/auth.php` — authorization and session helper functions
- `includes/header.php` — the shared HTML `<head>` and navigation bar
- `includes/footer.php` — the shared HTML footer and closing tags

These files will be `require_once`'d at the top and bottom of every page. Getting them right is critical — a bug here breaks the entire application.

---

## Strict Rules Reminder
- **Security:** `auth.php` must use `$_SESSION` — never trust user-supplied data for role checks.
- **Sessions:** Every page that uses `auth.php` must have `session_start()` called before any output. The header include will handle this.
- **No frameworks:** Pure PHP only.
- **Monochrome UI:** The navbar must follow the CSS classes defined in Task 3 (`style.css`).

---

## Deliverables

### 1. `includes/auth.php`

This file contains **only PHP logic — no HTML output**. It must define the following functions:

#### `isLoggedIn() : bool`
- Returns `true` if `$_SESSION['user_id']` is set and not empty.
- Returns `false` otherwise.

#### `isAdmin() : bool`
- Returns `true` if `isLoggedIn()` is true AND `$_SESSION['role'] === 'admin'`.
- Returns `false` otherwise.

#### `requireLogin(string $redirectTo = '../auth/login.php') : void`
- If the user is NOT logged in, redirect to the login page and `exit`.
- Accepts an optional custom redirect path for flexibility (admin pages redirect differently than user pages).

#### `requireAdmin(string $redirectTo = '../user/dashboard.php') : void`
- If the user is NOT an admin, redirect to the user dashboard and `exit`.
- This prevents regular users from accessing admin URLs directly.

#### `requireGuest(string $redirectTo = '../user/dashboard.php') : void`
- If the user IS already logged in, redirect them away from login/register pages.
- Admins redirect to `../admin/dashboard.php`, users redirect to `../user/dashboard.php`.

#### `setFlashMessage(string $type, string $message) : void`
- Stores a one-time message in `$_SESSION['flash']` as an array: `['type' => $type, 'message' => $message]`
- Types: `'success'`, `'danger'`, `'info'`

#### `getFlashMessage() : array|null`
- Retrieves and **immediately unsets** `$_SESSION['flash']`
- Returns the flash array or `null` if none exists

---

### 2. `includes/header.php`

This file outputs the opening HTML structure and the navigation bar. It must:

- Call `session_start()` at the very top (before any HTML)
- Include `auth.php` (so session helpers are available)
- Accept a `$pageTitle` variable (set by the including page before the require) and use it in `<title>`
- Output the full `<!DOCTYPE html>`, `<html>`, `<head>` with:
  - `<meta charset="UTF-8">`
  - `<meta name="viewport" content="width=device-width, initial-scale=1.0">`
  - `<title>` using `$pageTitle ?? 'Restaurant System'`
  - `<link>` to `assets/css/style.css` — **use a root-relative or dynamic path**
- Output the `<body>` opening tag and `.page-wrapper` div
- Output the `.navbar` with:
  - Brand name: **"The Restaurant"** (or similar)
  - Navigation links that change based on login state:
    - **Not logged in:** Show "Menu", "Login", "Register"
    - **Logged in as User:** Show "Menu", "My Orders", "Logout"
    - **Logged in as Admin:** Show "Dashboard", "Menu Items", "Orders", "Upload PDF", "Logout"
  - Highlight the active link using the `.active` CSS class
- Output the `.main-content` div opening tag
- Check for a flash message and render it as an `.alert` div if one exists

**Important — CSS Path Handling:**  
Pages are at different directory depths (`auth/login.php` vs `admin/menu_items/create.php`). Use a dynamic base path approach:

```php
$basePath = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2);
```

Then reference assets as `$basePath . 'assets/css/style.css'`.

---

### 3. `includes/footer.php`

This file closes the HTML structure. It must:

- Close the `.main-content` div
- Output the `.footer` with:
  - Copyright text: `© <?= date('Y') ?> The Restaurant. All rights reserved.`
- Close `.page-wrapper`
- Include `<script src="...assets/js/main.js">` using the same dynamic `$basePath` approach
- Close `</body>` and `</html>`

---

## Step-by-Step Instructions for Alaa

1. Start with `includes/auth.php` — write all 6 functions. This file has no HTML, only PHP.
2. Write `includes/header.php` — build the navbar logic carefully. Test the active link detection.
3. Write `includes/footer.php` — keep it simple and clean.
4. Create a temporary test page (e.g., `test_layout.php` in the root) that:
   - Sets `$pageTitle = 'Test Page'`
   - Requires `includes/header.php`
   - Outputs some dummy content inside a `.container`
   - Requires `includes/footer.php`
5. Open `http://localhost/restaurant_system/test_layout.php` and verify the layout renders correctly with the navbar and footer.
6. Delete `test_layout.php` after verification.
7. Hand off to Belal for review.

---

## Function Reference Summary

```php
isLoggedIn()          → bool
isAdmin()             → bool
requireLogin()        → void (redirects if not logged in)
requireAdmin()        → void (redirects if not admin)
requireGuest()        → void (redirects if already logged in)
setFlashMessage()     → void (stores flash in session)
getFlashMessage()     → array|null (retrieves and clears flash)
```

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `auth.php` contains all 6 functions with correct logic
- [ ] `isAdmin()` checks BOTH `isLoggedIn()` AND the role — not just the role alone
- [ ] `requireAdmin()` redirects users (not admins) away from admin pages
- [ ] `requireGuest()` redirects already-logged-in users away from login/register
- [ ] Flash messages are unset immediately after being read (one-time display only)
- [ ] `header.php` calls `session_start()` before any HTML output
- [ ] Navbar links change correctly based on login state and role
- [ ] CSS and JS paths are dynamic and work from any directory depth
- [ ] `footer.php` correctly closes all open HTML tags
- [ ] No test files left in the repository

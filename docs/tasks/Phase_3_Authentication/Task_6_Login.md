# Task 6 — User Login Page

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Walid                          |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 3 — Authentication            |
| **Status**         | Completed                           |
| **Depends On**     | Task 5 (registered users must exist) |
| **Blocks**         | Task 7, and all of Phases 4 & 5     |

---

## Objective
Build the login page at `auth/login.php`. Users submit their email and password. The system verifies credentials using `password_verify()`, creates a session, and optionally sets a "Remember Me" cookie. Admins and users are redirected to their respective dashboards.

---

## Strict Rules Reminder
- **Security:** Fetch the user by email using a MySQLi prepared statement (`$conn->prepare`, `bind_param`, `get_result`), then verify the password with `password_verify()`. Never compare passwords directly. Only MySQLi OO is permitted.
- **Security:** Do NOT reveal whether the email or the password was wrong — use a generic error: "Invalid email or password."
- **Security:** Regenerate the session ID after login: `session_regenerate_id(true)` — prevents session fixation attacks.
- **Security:** The "Remember Me" cookie must NOT store the password or the user ID in plain form. Generate a cryptographically random token with `bin2hex(random_bytes(32))`, store its `hash('sha256', $token)` in the `remember_token` column of the `users` table, and place only the raw token in the cookie. Rotate the token on every auto-login.
- **Monochrome UI:** Use `.form-card` layout. Zero non-monochrome colors.

---

## Deliverable
`auth/login.php` — handles both GET (show form) and POST (process login).

---

## Page Requirements

### GET Request — Show the Form
- Render the login form inside a `.form-card` container
- Fields:
  - Email Address (`name="email"`, type email, required)
  - Password (`name="password"`, type password, required)
  - Remember Me checkbox (`name="remember_me"`, type checkbox, value `"1"`)
- Submit button: "Login" (`.btn .btn-primary`)
- Link below the form: "Don't have an account? Register" → `register.php`
- If arriving from registration, display the flash success message via `getFlashMessage()`

### POST Request — Process Login

**Step 1 — Basic Validation:**
- Email: not empty, valid format
- Password: not empty
- If either is empty, show inline errors and re-render the form

**Step 2 — Database Lookup:**
- Query `users` table WHERE `email = ?` using a MySQLi prepared statement
- If no user found: show generic error "Invalid email or password." — do NOT specify which field is wrong

**Step 3 — Password Verification:**
- Call `password_verify($inputPassword, $row['password'])`
- If it returns `false`: show the same generic error "Invalid email or password."

**Step 4 — Session Creation (on success):**
- Call `session_regenerate_id(true)` to prevent session fixation
- Set `$_SESSION['user_id'] = $row['id']`
- Set `$_SESSION['role'] = $row['role']`
- Set `$_SESSION['user_name'] = $row['name']`

**Step 5 — Remember Me Cookie:**
- If the `remember_me` checkbox was checked:
  - Generate a token: `$token = bin2hex(random_bytes(32))`
  - Hash it: `$tokenHash = hash('sha256', $token)`
  - Store `$tokenHash` in `users.remember_token` using a MySQLi prepared statement UPDATE
  - Set a cookie named `remember_user` with the value of the **raw** `$token` (not the hash)
  - Expiry: `time() + (30 * 24 * 60 * 60)` — 30 days
  - Path: `/`
- If not checked: clear `remember_token` to NULL in the database and expire any existing cookie

**Step 6 — Redirect Based on Role:**
- If `$_SESSION['role'] === 'admin'`: redirect to `../admin/dashboard.php`
- If `$_SESSION['role'] === 'user'`: redirect to `../user/dashboard.php`

### Remember Me — Auto-Login on Return Visit
At the top of `login.php` (before showing the form on GET), check:
- If `$_COOKIE['remember_user']` exists AND the user is not already logged in:
  - Validate the cookie value: must be exactly 64 hex characters (`strlen === 64`, `ctype_alnum`)
  - Hash it: `$tokenHash = hash('sha256', $cookieToken)`
  - Query `users` WHERE `remember_token = $tokenHash` using a MySQLi prepared statement
  - If found: rotate the token (generate a new one, update the DB, set a new cookie), regenerate the session, set session variables, and redirect
  - If not found: expire the cookie and continue to show the login form

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] Email lookup uses a MySQLi prepared statement — not string interpolation
- [ ] `password_verify()` is used — no direct password comparison
- [ ] Generic error message used — does not reveal which field is wrong
- [ ] `session_regenerate_id(true)` is called on successful login
- [ ] `$_SESSION['user_id']`, `$_SESSION['role']`, `$_SESSION['user_name']` are all set
- [ ] "Remember Me" stores a random token in the cookie — never the user ID or password
- [ ] Token is hashed with SHA-256 before being stored in `users.remember_token`
- [ ] Token is rotated on every successful auto-login
- [ ] Cookie expiry is 30 days
- [ ] Auto-login from cookie works on return visit
- [ ] Admin redirects to `admin/dashboard.php`, user redirects to `user/dashboard.php`
- [ ] `requireGuest()` is called at the top — already logged-in users are redirected away
- [ ] Page uses `includes/header.php` and `includes/footer.php`

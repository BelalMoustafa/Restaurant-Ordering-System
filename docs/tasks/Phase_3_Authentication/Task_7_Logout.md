# Task 7 — Logout

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Marzouk                        |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 3 — Authentication            |
| **Status**         | Pending                             |
| **Depends On**     | Task 6 (session must exist to destroy) |
| **Blocks**         | Nothing — this is a terminal action |

---

## Objective
Build the logout handler at `auth/logout.php`. This file has no visible UI — it is a pure action script. It must completely destroy the user's session, clear the "Remember Me" cookie, and redirect to the login page with a confirmation message.

---

## Strict Rules Reminder
- **Security:** A proper logout must do all three steps: unset session variables, destroy the session, AND expire the cookie. Doing only one or two is a security vulnerability.
- **Security:** Invalidate the session cookie in the browser by overwriting it with an expired timestamp.
- **No GET-based logout:** This file should only be triggered via a link (GET is acceptable for logout in simple apps), but it must not perform any action if the user is not logged in.

---

## Deliverable
`auth/logout.php` — a pure PHP action file with no HTML output of its own.

---

## Logout Sequence (Must follow this exact order)

**Step 1 — Start the session:**
- `session_start()` must be called first

**Step 2 — Unset all session variables:**
- `$_SESSION = []` — clears all session data

**Step 3 — Destroy the session cookie in the browser:**
- If `ini_get("session.use_cookies")` is true:
  - Get the session cookie params: `session_get_cookie_params()`
  - Call `setcookie(session_name(), '', time() - 42000, ...)` with the same params to expire it

**Step 4 — Destroy the session on the server:**
- `session_destroy()`

**Step 5 — Clear the "Remember Me" cookie:**
- `setcookie('remember_user', '', time() - 3600, '/')` — sets expiry in the past to delete it

**Step 6 — Set a flash message and redirect:**
- Since the session is destroyed, flash messages via `$_SESSION` won't work here
- Instead, use a URL query parameter: redirect to `login.php?logged_out=1`
- In `login.php`, check for `$_GET['logged_out']` and display "You have been logged out successfully."

---

## Step-by-Step Instructions for Ziad Marzouk

1. Create `auth/logout.php`
2. Implement all 6 steps in order
3. Update `auth/login.php` (coordinate with Ziad Walid) to display the logout message when `?logged_out=1` is present in the URL
4. Test the full flow: Login → verify session exists → click Logout → verify session is gone → verify cookie is cleared → verify redirect to login with message
5. Hand off to Belal for review

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `$_SESSION = []` is called before `session_destroy()`
- [ ] The session cookie is expired in the browser (not just server-side destroy)
- [ ] `session_destroy()` is called
- [ ] The `remember_user` cookie is cleared with a past expiry timestamp
- [ ] User is redirected to `login.php` after logout
- [ ] A logout confirmation message is displayed on the login page
- [ ] If a non-logged-in user visits `logout.php` directly, they are simply redirected to login (no errors)

# Task 5 — User Registration Page

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Yasser                         |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 3 — Authentication            |
| **Status**         | Completed                           |
| **Depends On**     | Tasks 1, 2, 3, 4                    |
| **Blocks**         | Task 6 (Login needs registered users) |

---

## Objective
Build the user registration page at `auth/register.php`. New visitors can create an account. Passwords must be hashed using `password_hash()`. All input must be validated server-side. Duplicate emails must be rejected.

---

## Strict Rules Reminder
- **Security:** Use MySQLi OO prepared statements (`$conn->prepare`, `bind_param`) for all queries. Never interpolate user input into SQL. Only MySQLi OO is permitted.
- **Security:** Use `password_hash($password, PASSWORD_DEFAULT)` — never store plain text.
- **Security:** Sanitize all input with `htmlspecialchars()` when re-displaying in the form.
- **Monochrome UI:** Use `.form-card` layout from `style.css`. Zero non-monochrome colors.
- **Local Only:** No email verification or SMTP — registration is instant.

---

## Deliverable
`auth/register.php` — handles both GET (show form) and POST (process registration).

---

## Page Requirements

### GET Request — Show the Form
- Render the registration form inside a `.form-card` container
- Fields:
  - Full Name (`name="name"`, type text, required)
  - Email Address (`name="email"`, type email, required)
  - Password (`name="password"`, type password, required, min 8 chars)
  - Confirm Password (`name="confirm_password"`, type password, required)
- Submit button: "Create Account" (`.btn .btn-primary`)
- Link below the form: "Already have an account? Login" → `login.php`
- Re-populate fields (except passwords) on validation failure using `htmlspecialchars()`

### POST Request — Process Registration
Perform the following validations **in order**, collecting all errors before displaying:

1. **Name:** Not empty, max 100 characters
2. **Email:** Not empty, valid email format (`filter_var` with `FILTER_VALIDATE_EMAIL`), max 150 characters
3. **Password:** Not empty, minimum 8 characters
4. **Confirm Password:** Must match the password field exactly
5. **Duplicate Email:** Query the `users` table using a MySQLi prepared statement — if the email already exists, add error "This email is already registered."

If any validation fails:
- Re-render the form with all error messages displayed using `.form-error` spans below each field
- Do NOT insert anything into the database

If all validations pass:
- Hash the password: `$hashed = password_hash($password, PASSWORD_DEFAULT)`
- Insert into `users` table using a MySQLi prepared statement (`$conn->prepare`, `bind_param`): `name`, `email`, `password`, `role` (hardcode `'user'`)
- Set a flash message: `setFlashMessage('success', 'Account created successfully. Please log in.')`
- Redirect to `login.php`

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] Form re-populates name and email on error (not password fields)
- [ ] All 5 validations are implemented
- [ ] Duplicate email check uses a MySQLi prepared statement
- [ ] Password is hashed with `PASSWORD_DEFAULT` before INSERT
- [ ] INSERT uses a MySQLi prepared statement — no string interpolation
- [ ] Successful registration redirects to login with a flash message
- [ ] Page uses `includes/header.php` and `includes/footer.php`
- [ ] `requireGuest()` is called at the top — logged-in users are redirected away

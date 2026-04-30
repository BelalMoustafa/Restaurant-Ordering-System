# Belal Moustafa Defense Notes

## 1. Assigned Responsibilities

Belal Moustafa was responsible for:

- Task 19: Security Hardening.
- Team Leader and Reviewer for all 19 tasks.

This means Belal's role was not only to implement `.htaccess` security files, but also to enforce project rules, review all features, identify security problems, and guide the team toward final sign-off quality.

## 2. Task 19: Security Hardening

## 2.1 Task Objective

The objective of Task 19 was to harden the local Apache environment.

The task required:

- Root `.htaccess` file.
- Uploads `.htaccess` file.
- Block directory listing.
- Block direct access to sensitive folders.
- Block direct access to sensitive file types.
- Prevent PHP execution inside uploads.
- Keep the project local and secure for XAMPP/MAMP demonstration.

## 2.2 Files Responsible

```text
.htaccess
uploads/.htaccess
```

## 2.3 Root `.htaccess` Deep-Dive

### Disable Directory Listing

```apache
Options -Indexes
```

Explanation:

- Prevents Apache from listing folder contents.
- If a folder has no `index.php`, the browser should not show all files.

Why this matters:

- Attackers should not browse project directories.
- Sensitive structure should not be exposed.

### Rewrite Protection

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^config(/.*)?$   - [F,L]
    RewriteRule ^includes(/.*)?$ - [F,L]
    RewriteRule ^database(/.*)?$ - [F,L]
    RewriteRule ^docs(/.*)?$     - [F,L]
</IfModule>
```

Explanation:

- Enables rewrite rules if `mod_rewrite` exists.
- Blocks browser access to sensitive folders.
- `[F,L]` means:
  - `F`: return Forbidden.
  - `L`: stop processing more rules.

Folders blocked:

- `config/`: contains database credentials.
- `includes/`: contains internal helper code.
- `database/`: contains schema SQL.
- `docs/`: contains internal project documentation.

Defense point:

- PHP includes are meant to be used by application pages, not downloaded or browsed directly.

### Sensitive File Type Blocking

```apache
<FilesMatch "\.(sql|log|env|md|json|lock|bak|swp|gitignore|gitkeep)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

Explanation:

- Blocks direct access to sensitive file extensions.

Examples:

- `.sql`: database schema.
- `.env`: environment secrets if ever added.
- `.md`: documentation.
- `.json`: possible config or lock data.
- `.bak`: backups.
- `.gitignore`: internal repository file.

Why this matters:

- Even if a sensitive file is placed outside protected folders, this rule reduces exposure.

### Hidden File Blocking

```apache
<FilesMatch "^\.(?!htaccess)">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

Explanation:

- Blocks hidden files beginning with `.`.
- Allows `.htaccess` itself to remain functional.

Why hidden files matter:

- Hidden files often contain metadata or configuration.

### Charset and Server Signature

```apache
AddDefaultCharset UTF-8
ServerSignature Off
```

Explanation:

- `AddDefaultCharset UTF-8` sets default response charset.
- `ServerSignature Off` hides Apache version/signature from error pages.

Why hide server signature:

- It reduces information disclosure.

## 2.4 `uploads/.htaccess` Deep-Dive

### Disable Upload Directory Listing

```apache
Options -Indexes
```

Explanation:

- Prevents browsing uploaded files as a directory listing.

### Block Script Extensions

```apache
<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|phps|shtml|cgi|pl)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

Explanation:

- Blocks direct access to PHP and script-like file extensions.
- Covers multiple PHP variants and script extensions.

Why this matters:

- Upload folders are common attack targets.
- If an attacker uploads a script, Apache should not execute or serve it.

### Allow Safe Public Upload Types

```apache
<FilesMatch "\.(jpg|jpeg|png|gif|webp|pdf)$">
    Order Deny,Allow
    Allow from all
</FilesMatch>
```

Explanation:

- Allows browser access to images and PDFs.
- These are the intended public upload types.

Note:

- Application validation only permits JPG/PNG images and PDF menu files.
- The server rule is an additional layer.

### Disable PHP Engine

```apache
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
```

Explanation:

- If Apache uses `mod_php`, PHP execution is disabled in uploads.

Why this is important:

- Even if a PHP file reaches uploads, it should not run.

### Disable CGI Execution

```apache
Options -ExecCGI
```

Explanation:

- Prevents CGI execution in uploads.
- Adds another layer against script execution.

## 2.5 Design Decisions for Task 19

### Why use `.htaccess`?

The project runs locally under Apache through XAMPP/MAMP. `.htaccess` is the practical local way to enforce folder-level rules.

### Why protect `config/`?

It contains database credentials.

### Why protect `includes/`?

It contains internal PHP helpers not meant as public pages.

### Why protect `database/`?

It contains SQL schema and seed data.

### Why protect `docs/`?

Documentation can reveal implementation details, test accounts, and project internals.

### Why protect uploads separately?

Uploaded files are user-controlled or admin-controlled input. Upload folders require stricter execution controls.

## 2.6 Alternatives for Task 19

### Alternative: Configure Apache VirtualHost

Why not chosen:

- The project is local and simple.
- `.htaccess` is easier for university demonstration.
- Students can include it directly in the project folder.

### Alternative: Store Uploads Outside Web Root

Why not chosen:

- More secure in production.
- More complex for local XAMPP/MAMP demo.
- The project specification stores files under `uploads/`.

### Alternative: Rely Only on PHP Validation

Why not chosen:

- Defense in depth is better.
- Server configuration should block dangerous files even if application validation has a bug.

## 2.7 Dependencies for Task 19

Task 19 depends on:

- Task 2 for folder structure.
- Task 10 because images are uploaded to `uploads/images/`.
- Task 13 because PDF files are uploaded to `uploads/pdfs/`.

Task 19 supports:

- Secure local demonstration.
- Safe file upload handling.
- Protection of sensitive project files.

---

## 3. Belal's Team Leader and Reviewer Role

## 3.1 Review Scope

Belal reviewed all 19 tasks:

1. SQL Schema.
2. Project Skeleton and Database Connection.
3. Global Assets.
4. Shared Includes.
5. Registration.
6. Login.
7. Logout.
8. Admin Dashboard.
9. Menu Items List.
10. Menu Items Create.
11. Menu Items Edit.
12. Menu Items Delete.
13. PDF Upload.
14. Admin Orders.
15. User Dashboard and Menu.
16. Place Order.
17. My Orders.
18. Landing Page.
19. Security Hardening.

## 3.2 Review Standards Enforced

### Core PHP Only

Rule:

- No frameworks.
- No Laravel.
- No Symfony.
- No CodeIgniter.

Reason:

- The project requirement is Core PHP.
- The team must demonstrate understanding of PHP fundamentals.

### MySQLi OO Only

Rule:

- Use `new mysqli`.
- Use `$conn->prepare()`.
- Use `bind_param()`.
- Use `get_result()` or `bind_result()`.

Reason:

- MySQLi OO is required.
- Prepared statements prevent SQL injection.

### No PDO

Rule:

- PDO is not allowed in code.

Reason:

- The specification requires MySQLi OO.
- Consistency across the team matters.

### Prepared Statements Everywhere

Rule:

- No SQL string interpolation with user input.
- All database access must use prepared statements.

Reason:

- Prevent SQL injection.
- Keep code consistent and reviewable.

### Password Security

Rules:

- Use `password_hash()`.
- Use `password_verify()`.
- Never store plain text passwords.

Reason:

- Protect user credentials.
- Follow PHP security best practice.

### CSRF Protection

Rule:

- All state-changing POST forms need CSRF tokens.
- Validate tokens before database updates or inserts.

Protected actions:

- Registration.
- Login.
- Create menu item.
- Edit menu item.
- Delete menu item.
- Upload PDF.
- Place order.
- Update order status.

Reason:

- Prevent forged requests.

### Role-Based Access Control

Rules:

- Admin pages require admin role.
- User pages require user login.
- Admins are redirected away from user order flow.
- Guests are redirected to login for protected pages.

Reason:

- Prevent unauthorized access.
- Keep business roles separate.

### Output Escaping

Rule:

- Dynamic text must be escaped with `htmlspecialchars()`.

Reason:

- Prevent XSS.

### File Upload Safety

Rules:

- Validate upload errors.
- Validate size.
- Validate MIME type.
- Validate extension.
- Generate safe filenames.
- Block script execution in uploads.

Reason:

- Upload features are high-risk.

### Data Integrity

Rules:

- Keep exactly 3 tables.
- Use foreign keys.
- Do not delete menu items that have orders.
- Calculate order totals server-side.

Reason:

- Preserve order history.
- Prevent price tampering.

### Monochrome UI

Rule:

- Black, white, and gray UI.
- No flashy colors.

Reason:

- Required by specification.
- Keeps the interface consistent.

## 3.3 Critical Issues Found and Fixed During Review

### Headers Already Sent Risk

Problem:

- Some pages included the shared header before performing redirects.

Risk:

- PHP cannot reliably call `header('Location: ...')` after output begins.

Fix:

- Move session/auth/POST processing before `includes/header.php` on pages that redirect.

### Missing CSRF Tokens

Problem:

- Some POST forms did not originally include CSRF tokens.

Risk:

- Forged requests could create accounts, place orders, upload PDFs, or update records.

Fix:

- Added `csrfToken()` and `requireValidCsrf()`.
- Added hidden CSRF inputs to forms.

### CSRF Timing

Problem:

- Some pages validated CSRF after database reads.

Risk:

- State-changing requests should be rejected before meaningful processing.

Fix:

- Validate CSRF at the start of POST handling.

### Cascade Delete Data Loss

Problem:

- Menu item deletion originally risked deleting order history.

Risk:

- Deleting menu items could damage business records.

Fix:

- Changed schema to `ON DELETE RESTRICT` for `orders.menu_item_id`.
- Added app-level check before deleting menu items.

### Admin Access to User Ordering Flow

Problem:

- Admins could access user pages.

Risk:

- Role confusion.

Fix:

- Added `requireUser()` helper.
- Applied it to customer-only pages.

### Documentation PDO References

Problem:

- Some task docs still referenced PDO.

Risk:

- Team confusion during defense.

Fix:

- Updated task docs to refer to MySQLi OO.

## 3.4 Review Process

Belal's review process included:

1. Reading the master project specs.
2. Reading all task documents.
3. Auditing source code against requirements.
4. Checking database schema table count and constraints.
5. Searching for forbidden PDO usage.
6. Searching for raw SQL query risks.
7. Checking role protection on protected pages.
8. Checking CSRF coverage on POST forms.
9. Checking password hashing and verification.
10. Checking upload validation.
11. Checking `.htaccess` protections.
12. Checking UI monochrome compliance.
13. Running PHP syntax checks.
14. Creating QA testing flow documentation.
15. Creating defense documentation for team members.

## 3.5 Defense Talking Points for Belal

### What was your role as team leader?

Belal coordinated review and quality control across all tasks. He made sure the final system matched the specification, used Core PHP, used MySQLi OO, protected against common vulnerabilities, and was ready for university defense.

### What was the most important security issue fixed?

The most critical issues were missing CSRF coverage, redirect/header ordering, and data loss from cascading menu item deletion.

### Why was `ON DELETE RESTRICT` important?

It prevents menu items with historical orders from being deleted. This preserves order history and business integrity.

### Why did you add `requireUser()`?

To separate customer-only pages from admin pages. Admins manage the system but should not place customer orders.

### Why protect uploads with `.htaccess` if PHP already validates files?

Security should be layered. PHP validation prevents bad uploads, and `.htaccess` prevents script execution if a dangerous file ever reaches uploads.

### Why update task docs?

Defense documents and task specs must match the actual project. PDO references could confuse team members because the code and rules require MySQLi OO.

## 4. Belal Summary

Belal's direct implementation task was security hardening through `.htaccess`.

As team leader and reviewer, Belal also ensured:

- Core PHP compliance.
- MySQLi OO compliance.
- Prepared statement usage.
- Password security.
- CSRF protection.
- Role-based access control.
- Upload safety.
- Data integrity.
- Monochrome UI compliance.
- Documentation consistency.
- End-to-end QA readiness.

In defense, Belal should emphasize that a team leader's job is not only to write code, but to make sure all separate tasks connect into one secure, consistent, working system.


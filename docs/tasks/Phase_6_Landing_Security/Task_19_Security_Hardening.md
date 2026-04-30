# Task 19 — Security Hardening

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Belal Moustafa                      |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 6 — Landing Page & Security   |
| **Status**         | Pending                             |
| **Depends On**     | All previous tasks                  |
| **Blocks**         | Nothing — this is the final task    |

---

## Objective
Write the Apache `.htaccess` configuration files that protect sensitive directories and prevent common server-level vulnerabilities. This is a critical security task — without it, attackers could directly access database credentials or execute uploaded files as PHP.

---

## Deliverables
- `restaurant_system/.htaccess` (root-level)
- `restaurant_system/uploads/.htaccess`

---

## `restaurant_system/.htaccess` — Root Level

### Purpose
- Block direct browser access to `config/` and `includes/` directories
- Set a custom 403 Forbidden response for blocked paths
- Optionally enable URL rewriting for cleaner paths (not required for this project)

### Required Directives

```apache
# Disable directory listing for the entire project
Options -Indexes

# Block direct access to the config directory
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Block access to config/ directory
    RewriteRule ^config/ - [F,L]

    # Block access to includes/ directory
    RewriteRule ^includes/ - [F,L]
</IfModule>

# Block access to sensitive file types
<FilesMatch "\.(sql|log|env|md|json|lock)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### What Each Directive Does
- `Options -Indexes` — prevents Apache from showing a directory listing if no `index.php` exists in a folder. Without this, visiting `http://localhost/restaurant_system/config/` would list all files.
- `RewriteRule ^config/ - [F,L]` — returns HTTP 403 Forbidden for any URL starting with `config/`
- `RewriteRule ^includes/ - [F,L]` — same for `includes/`
- `<FilesMatch>` block — blocks direct access to `.sql`, `.log`, `.env`, `.md`, `.json`, `.lock` files (prevents the schema file and docs from being downloaded)

---

## `restaurant_system/uploads/.htaccess` — Uploads Directory

### Purpose
This is the most critical security file. The `uploads/` directory is writable by the web server (files are saved here). If an attacker uploads a file named `shell.php.jpg` and bypasses the MIME check, this `.htaccess` ensures PHP cannot execute inside this directory.

### Required Directives

```apache
# Disable directory listing
Options -Indexes

# CRITICAL: Prevent PHP execution in this directory
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Also block PHP variants
<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Only allow image and PDF files to be served
<FilesMatch "\.(jpg|jpeg|png|gif|pdf)$">
    Order Deny,Allow
    Allow from all
</FilesMatch>
```

### What Each Directive Does
- First `<FilesMatch>` — blocks `.php` files from being served/executed
- Second `<FilesMatch>` — blocks all PHP variants (`.php3`, `.phtml`, `.phar`, etc.) — attackers often try these extensions to bypass simple `.php` checks
- Third `<FilesMatch>` — explicitly allows only image and PDF files to be served

---

## Step-by-Step Instructions for Ziad Walid

1. Create `restaurant_system/.htaccess` with the root-level directives
2. Create `restaurant_system/uploads/.htaccess` with the uploads-level directives
3. Test root `.htaccess`:
   - Visit `http://localhost/restaurant_system/config/db.php` — should get **403 Forbidden**
   - Visit `http://localhost/restaurant_system/includes/auth.php` — should get **403 Forbidden**
4. Test uploads `.htaccess`:
   - Create a test file `uploads/images/test.php` with `<?php echo 'hacked'; ?>`
   - Visit `http://localhost/restaurant_system/uploads/images/test.php` — should get **403 Forbidden**
   - Delete `test.php` after the test
5. Verify that legitimate image files still load correctly (menu item images should still display)
6. Hand off to Belal for final review

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] `Options -Indexes` present in both `.htaccess` files
- [ ] Direct access to `config/db.php` returns 403
- [ ] Direct access to `includes/auth.php` returns 403
- [ ] PHP execution blocked in `uploads/` directory
- [ ] PHP variant extensions (`.phtml`, `.phar`, etc.) also blocked in `uploads/`
- [ ] Legitimate image files in `uploads/images/` still load in the browser
- [ ] PDF files in `uploads/pdfs/` still downloadable
- [ ] No test files left in the repository
- [ ] Belal has personally verified all 403 responses locally

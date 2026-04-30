# Task 2 — Project Skeleton & DB Connection

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Ziad Sameh                          |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 2 — Project Bootstrap         |
| **Status**         | Completed                           |
| **Depends On**     | Task 1 (database must exist)        |
| **Blocks**         | Task 4 (Alaa needs directories in place) |

---

## Objective
Create the complete directory and file skeleton for the entire project on disk, and write the single database connection file (`config/db.php`) that every other PHP file will include to talk to MySQL.

---

## Strict Rules Reminder
- **Local Execution Only:** All paths and configuration must target `localhost` with XAMPP/MAMP defaults.
- **Security:** Use **MySQLi OO** (`new mysqli(...)`) with `$conn->connect_error` checked immediately so database errors are caught. Never expose raw error messages to the browser — log them with `error_log()` and show a generic message to the user.
- **No frameworks:** Pure PHP only. Only MySQLi OO is permitted on this project.

---

## Deliverables

### 1. Full Directory Structure on Disk
Create every folder listed below. Empty directories should contain a `.gitkeep` file so they are tracked:

```
restaurant_system/
├── config/
├── includes/
├── auth/
├── admin/
│   ├── menu_items/
│   └── orders/
├── user/
├── uploads/
│   ├── images/
│   └── pdfs/
├── assets/
│   ├── css/
│   └── js/
└── database/
```

### 2. `config/db.php`
The MySQLi OO connection file. This is the single most critical shared file in the project.

**Requirements:**
- Define DB constants at the top: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Create a `new mysqli(...)` instance
- Check `$conn->connect_error` immediately after instantiation
- On connection failure, log the error with `error_log()` and `die()` with a generic user-safe message
- Call `$conn->set_charset('utf8mb4')` after a successful connection
- Expose the connection as a `$conn` variable

**Default XAMPP credentials to use:**
```
Host:     localhost
Database: restaurant_db
Username: root
Password: (empty string)
```

---

## Step-by-Step Instructions for Ziad Sameh

1. Inside your XAMPP `htdocs/` folder, create the root project folder: `restaurant_system/`.
2. Manually create every sub-directory listed in the structure above.
3. Place a blank `.gitkeep` file inside `uploads/images/` and `uploads/pdfs/` so the folders are not ignored.
4. Create `config/db.php` following the requirements above.
5. Create a quick test file `config/test_connection.php` that includes `db.php` and prints "Connection successful" — run it in the browser at `http://localhost/restaurant_system/config/test_connection.php` to verify.
6. **Delete `test_connection.php`** after confirming the connection works. Do not leave test files in the repo.
7. Hand off to Belal for review.

---

## `config/db.php` — Expected Structure (pseudocode outline)

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant_db');
define('DB_USER', 'root');
define('DB_PASS', '');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log('[DB Connection Error] ' . $conn->connect_error);
    die('<p>Service Unavailable. Please try again later.</p>');
}

$conn->set_charset('utf8mb4');
```

> Note: `utf8mb4` is important — it supports the full Unicode character set including special characters in menu item names.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] All directories exist on disk with correct names
- [ ] `config/db.php` uses MySQLi OO (`new mysqli`) — not string interpolation
- [ ] `$conn->connect_error` is checked immediately after instantiation
- [ ] Connection failure is handled gracefully — no raw errors shown to users
- [ ] `$conn->set_charset('utf8mb4')` is called on success
- [ ] `$conn` variable is available to any file that includes `db.php`
- [ ] No test files left in the repository
- [ ] Ziad Sameh has confirmed the connection works locally in the browser

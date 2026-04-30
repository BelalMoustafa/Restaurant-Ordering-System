# Task 1 — SQL Schema

## Assignment

| Field              | Detail                        |
|--------------------|-------------------------------|
| **Assigned To**    | Hamza                         |
| **Reviewed By**    | Belal Moustafa                |
| **Phase**          | Phase 1 — Database Foundation |
| **Status**         | Pending                       |
| **Depends On**     | Nothing — this is the first task |
| **Blocks**         | All other tasks (Tasks 2–19)  |

---

## Objective
Create the MySQL database `restaurant_db` and define the 3 core tables that power the entire application: `users`, `menu_items`, and `orders`.

---

## Strict Rules Reminder
- **Local Execution Only:** Run this SQL inside phpMyAdmin or the MySQL CLI on your local XAMPP/MAMP installation. No remote servers.
- **Security:** Define proper data types, NOT NULL constraints, and foreign key relationships to enforce data integrity at the database level.
- **No frameworks:** Pure SQL only.

---

## Deliverable
A single SQL file: `database/schema.sql`  
This file must be executable in one run — it should drop and recreate the database cleanly for easy resets during development.

---

## Table Specifications

### Table: `users`
Stores all registered accounts for both admins and regular users.

| Column       | Type                        | Constraints                              |
|--------------|-----------------------------|------------------------------------------|
| `id`         | INT                         | PRIMARY KEY, AUTO_INCREMENT              |
| `name`       | VARCHAR(100)                | NOT NULL                                 |
| `email`      | VARCHAR(150)                | NOT NULL, UNIQUE                         |
| `password`   | VARCHAR(255)                | NOT NULL — stores `password_hash()` output |
| `role`       | ENUM('admin', 'user')       | NOT NULL, DEFAULT 'user'                 |
| `created_at` | TIMESTAMP                   | DEFAULT CURRENT_TIMESTAMP                |

---

### Table: `menu_items`
Stores all dishes on the restaurant's digital menu.

| Column         | Type             | Constraints                              |
|----------------|------------------|------------------------------------------|
| `id`           | INT              | PRIMARY KEY, AUTO_INCREMENT              |
| `name`         | VARCHAR(150)     | NOT NULL                                 |
| `description`  | TEXT             | NULLABLE — optional dish description     |
| `price`        | DECIMAL(10,2)    | NOT NULL                                 |
| `category`     | VARCHAR(100)     | NOT NULL — e.g. Starters, Mains, Desserts |
| `image_path`   | VARCHAR(255)     | NULLABLE — relative path to uploaded image |
| `is_available` | TINYINT(1)       | NOT NULL, DEFAULT 1 (1=available, 0=hidden) |
| `created_at`   | TIMESTAMP        | DEFAULT CURRENT_TIMESTAMP                |

---

### Table: `orders`
Stores every order placed by users. Each row represents one menu item ordered in one transaction.

| Column         | Type                                    | Constraints                                      |
|----------------|-----------------------------------------|--------------------------------------------------|
| `id`           | INT                                     | PRIMARY KEY, AUTO_INCREMENT                      |
| `user_id`      | INT                                     | NOT NULL, FOREIGN KEY → `users(id)` ON DELETE CASCADE |
| `menu_item_id` | INT                                     | NOT NULL, FOREIGN KEY → `menu_items(id)` ON DELETE CASCADE |
| `quantity`     | INT                                     | NOT NULL, DEFAULT 1                              |
| `total_price`  | DECIMAL(10,2)                           | NOT NULL — quantity × item price at time of order |
| `status`       | ENUM('pending','confirmed','cancelled') | NOT NULL, DEFAULT 'pending'                      |
| `notes`        | TEXT                                    | NULLABLE — optional special instructions         |
| `created_at`   | TIMESTAMP                               | DEFAULT CURRENT_TIMESTAMP                        |

---

## Step-by-Step Instructions for Hamza

1. Open **phpMyAdmin** via your XAMPP/MAMP control panel (`http://localhost/phpmyadmin`).
2. Click the **SQL** tab at the top.
3. Create the file `database/schema.sql` in the project root with the following structure:
   - `DROP DATABASE IF EXISTS restaurant_db;`
   - `CREATE DATABASE restaurant_db;`
   - `USE restaurant_db;`
   - `CREATE TABLE users (...)` — as specified above
   - `CREATE TABLE menu_items (...)` — as specified above
   - `CREATE TABLE orders (...)` — as specified above, with FOREIGN KEY constraints
4. Paste the SQL into phpMyAdmin and click **Go** to execute.
5. Verify all 3 tables appear under `restaurant_db` in the left sidebar.
6. Insert **one test admin user** manually using an INSERT statement with a `password_hash()`-compatible hash for testing purposes. Example role: `admin`.
7. Commit the `schema.sql` file and hand it off to Belal for review.

---

## Seed Data (Optional but Recommended)
After the schema is confirmed, insert a default admin account so the team can test login immediately in Phase 3:

```sql
INSERT INTO users (name, email, password, role)
VALUES (
  'Admin User',
  'admin@restaurant.com',
  '$2y$10$exampleHashHere',  -- replace with actual password_hash('admin123', PASSWORD_DEFAULT)
  'admin'
);
```

> **Important:** The actual hash must be generated by running `password_hash('admin123', PASSWORD_DEFAULT)` in a PHP script. Do NOT store plain text.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] Database `restaurant_db` is created
- [ ] All 3 tables exist with correct column names and types
- [ ] `orders.user_id` is a valid foreign key to `users.id`
- [ ] `orders.menu_item_id` is a valid foreign key to `menu_items.id`
- [ ] `password` column is VARCHAR(255) — long enough for bcrypt hashes
- [ ] `role` column uses ENUM with only `admin` and `user` as valid values
- [ ] `schema.sql` file is clean, commented, and re-runnable
- [ ] Belal has executed the schema locally and confirmed it runs without errors

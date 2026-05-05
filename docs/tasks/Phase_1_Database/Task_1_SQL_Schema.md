# Task 1 - SQL Schema

## Assignment

| Field | Detail |
|-------|--------|
| **Assigned To** | Habiba |
| **Reviewed By** | Belal Moustafa |
| **Phase** | Phase 1 - Database Foundation |
| **Status** | Completed |
| **Depends On** | Nothing - this is the first task |
| **Blocks** | All other tasks (Tasks 2-19) |

---

## Objective

Create the MySQL database `restaurant_db` and define the 3 core tables that power the entire application:

- `users`
- `menu_items`
- `orders`

This task establishes the full data layer for authentication, menu management, and order placement.

---

## Strict Rules Reminder

- Local execution only through XAMPP/MAMP.
- Exactly 3 database tables.
- Core SQL only.
- No frameworks.
- Password storage must support `password_hash()`.
- Foreign keys must preserve data integrity.
- The schema must be re-runnable for testing and defense setup.

---

## Deliverable

```text
database/schema.sql
```

The SQL file must be executable in one run. It should drop and recreate the database cleanly so the team can reset the project before testing or defense.

---

## Table Specifications

## Table: `users`

Stores all registered accounts for both admins and regular users.

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT |
| `name` | VARCHAR(100) | NOT NULL |
| `email` | VARCHAR(150) | NOT NULL, UNIQUE |
| `password` | VARCHAR(255) | NOT NULL, stores `password_hash()` output |
| `role` | ENUM('admin','user') | NOT NULL, DEFAULT 'user' |
| `remember_token` | VARCHAR(64) | NULL, UNIQUE |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

Key requirements:

- `email` must be unique to prevent duplicate accounts.
- `password` must be large enough for modern PHP password hashes.
- `role` must only allow `admin` or `user`.
- `remember_token` stores a hashed remember-me token, not the raw cookie value.

---

## Table: `menu_items`

Stores all dishes on the restaurant's digital menu.

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT |
| `name` | VARCHAR(150) | NOT NULL |
| `description` | TEXT | NULL |
| `price` | DECIMAL(10,2) | NOT NULL |
| `category` | VARCHAR(100) | NOT NULL |
| `image_path` | VARCHAR(255) | NULL |
| `is_available` | TINYINT(1) | NOT NULL, DEFAULT 1 |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

Key requirements:

- `price` must use `DECIMAL(10,2)`, not floating point.
- Price `0.00` must be allowed for complimentary items.
- `is_available` controls whether users can see and order the item.
- `image_path` stores a relative path to the uploaded image.

---

## Table: `orders`

Stores every order placed by users.

Each row represents one ordered menu item.

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT |
| `user_id` | INT | NOT NULL, FOREIGN KEY to `users(id)` |
| `menu_item_id` | INT | NOT NULL, FOREIGN KEY to `menu_items(id)` |
| `quantity` | INT | NOT NULL, DEFAULT 1 |
| `total_price` | DECIMAL(10,2) | NOT NULL |
| `status` | ENUM('pending','confirmed','cancelled') | NOT NULL, DEFAULT 'pending' |
| `notes` | TEXT | NULL |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

Foreign key rules:

- `orders.user_id` references `users(id)` with `ON DELETE CASCADE`.
- `orders.menu_item_id` references `menu_items(id)` with `ON DELETE RESTRICT`.

Why `ON DELETE RESTRICT` is required for menu items:

- Menu items with historical orders must not be deleted.
- Deleting them would damage order history.
- Admins should mark ordered items unavailable instead.

---

## Step-by-Step Instructions for Habiba

1. Open phpMyAdmin through the local XAMPP/MAMP environment.
2. Create or update `database/schema.sql`.
3. Add the database reset logic:
   - `DROP DATABASE IF EXISTS restaurant_db;`
   - `CREATE DATABASE restaurant_db;`
   - `USE restaurant_db;`
4. Create the `users` table exactly according to the required columns.
5. Create the `menu_items` table exactly according to the required columns.
6. Create the `orders` table with foreign keys to `users` and `menu_items`.
7. Ensure `orders.menu_item_id` uses `ON DELETE RESTRICT`.
8. Insert default admin and test user records with already-hashed passwords.
9. Insert sample menu items for testing.
10. Run the full schema in phpMyAdmin.
11. Confirm only the 3 required tables exist.
12. Hand the completed schema to Belal Moustafa for final review.

---

## Seed Data Requirement

The schema should include default test accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@restaurant.com` | `admin123` |
| User | `user@restaurant.com` | `admin123` |

Important:

- The password stored in SQL must be a hash.
- Plain-text passwords must never be stored in the database.

---

## Acceptance Criteria

- [x] Database `restaurant_db` is created.
- [x] Exactly 3 tables exist.
- [x] `users` table has correct columns and constraints.
- [x] `menu_items` table has correct columns and constraints.
- [x] `orders` table has correct columns and constraints.
- [x] `orders.user_id` references `users(id)`.
- [x] `orders.menu_item_id` references `menu_items(id)`.
- [x] `orders.menu_item_id` uses `ON DELETE RESTRICT`.
- [x] Password column is `VARCHAR(255)`.
- [x] Role column only allows `admin` and `user`.
- [x] Seed passwords are hashed.
- [x] Schema is clean and re-runnable.
- [x] Belal Moustafa reviewed and approved the schema.

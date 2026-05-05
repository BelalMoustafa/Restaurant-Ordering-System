# Phase 1 - Database Foundation

## Overall Goal

Establish the entire data layer of the Restaurant Ordering System.

This phase produces the SQL schema that every other phase depends on. No application feature can work correctly until the database tables, relationships, and seed data are complete.

---

## Tasks in This Phase

| Task # | Task Name | Assigned Developer | Status |
|--------|-----------|--------------------|--------|
| Task 1 | SQL Schema | Habiba | Completed |

---

## Developer Assignment

| Developer | Role | Responsibility |
|-----------|------|----------------|
| **Habiba** | Developer | Write and execute the full SQL schema for Task 1. |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve the SQL schema before later phases depend on it. |

---

## Assignment Update

The final roster assigns Task 1 to **Habiba**.

This reflects the corrected task swap between Hamza and Habiba:

- Habiba owns Task 1: SQL Schema.
- Hamza owns Task 3: Global CSS/JS Assets.

---

## Reviewer

**Belal Moustafa** is the designated reviewer for all tasks in this phase.

No task output moves to the next phase without Belal's approval.

---

## Phase Completion Criteria

- [x] `restaurant_db` database created in MySQL.
- [x] `users` table created with all required columns and constraints.
- [x] `menu_items` table created with all required columns and constraints.
- [x] `orders` table created with correct foreign keys.
- [x] Only 3 core tables exist.
- [x] Password storage supports `password_hash()`.
- [x] `orders.menu_item_id` uses `ON DELETE RESTRICT`.
- [x] Schema reviewed and approved by Belal Moustafa.

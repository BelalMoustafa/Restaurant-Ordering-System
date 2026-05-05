# Phase 4 - Admin Features

## Overall Goal

Build the complete admin-facing side of the Restaurant Ordering System.

Admins can manage the menu, upload a PDF version of the menu, and view or update orders placed by users. Every page in this phase is protected so only authenticated admins can access it.

---

## Tasks in This Phase

| Task # | Task Name | Assigned Developer | Status |
|--------|-----------|--------------------|--------|
| Task 8 | Admin Dashboard | Soud Karim | Completed |
| Task 9 | Menu Items - List & Read | Abd El-Rahman Yasser | Completed |
| Task 10 | Menu Items - Create | Habiba | Completed |
| Task 11 | Menu Items - Edit & Update | Ziad Sameh | Completed |
| Task 12 | Menu Items - Delete | Hamza | Completed |
| Task 13 | PDF Menu Upload | Alaa | Completed |
| Task 14 | Admin - View All Orders | Ziad Yasser | Completed |

---

## Developer Assignment

| Developer | Role | Responsibility |
|-----------|------|----------------|
| **Soud Karim** | Developer | Task 8 - Admin dashboard with summary statistics. |
| **Abd El-Rahman Yasser** | Developer | Task 9 - Menu items list page. |
| **Habiba** | Developer | Task 10 - Create menu item with image upload. |
| **Ziad Sameh** | Developer | Task 11 - Edit and update menu item. |
| **Hamza** | Developer | Task 12 - Delete menu item safely and remove image file. |
| **Alaa** | Developer | Task 13 - PDF menu upload. |
| **Ziad Yasser** | Developer | Task 14 - Admin orders list and detail view. |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve all tasks before Phase 5. |

---

## Assignment Update

The final roster reflects the task swap between Hamza and Habiba:

- Habiba owns Task 10: Menu Items Create with Image Upload.
- Hamza owns Task 12: Menu Items Delete.

Hassan is not assigned a primary Phase 4 task, but his later security work affects Phase 4 admin pages through:

- CSRF protection.
- XSS escaping.
- Secure handling of state-changing forms.

---

## Task Dependencies Within This Phase

```text
Task 8  (Dashboard)  - can start after authentication exists.
Task 9  (List)       - can start after menu_items table exists.
Task 10 (Create)     - creates menu items for later admin features.
Task 11 (Edit)       - depends on existing menu items.
Task 12 (Delete)     - depends on existing menu items and order integrity checks.
Task 13 (PDF Upload) - independent admin upload feature.
Task 14 (Orders)     - depends on order data from Phase 5 for full testing.
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for all tasks in this phase.

No task output moves to Phase 5 without Belal's approval.

---

## Phase Completion Criteria

- [x] All admin pages redirect non-admins away.
- [x] Admin dashboard shows correct database counts.
- [x] Menu item list displays records correctly.
- [x] Menu item create works with image upload.
- [x] Menu item edit works with image replacement.
- [x] Menu item delete blocks deletion when orders exist.
- [x] PDF upload validates file type and size.
- [x] Admin orders can be viewed and updated.
- [x] CSRF tokens protect state-changing admin forms.
- [x] Dynamic output is escaped with `htmlspecialchars()`.
- [x] All queries use MySQLi OO prepared statements.
- [x] All tasks reviewed and approved by Belal Moustafa.

# Phase 4 — Admin Features

## Overall Goal
Build the complete admin-facing side of the application. Admins can manage the full menu (CRUD on `menu_items`), upload a PDF version of the menu, and view/inspect all orders placed by users. Every page in this phase is protected — only authenticated admins can access it.

---

## Tasks in This Phase

| Task # | Task Name                    | Assigned Developer | Status  |
|--------|------------------------------|--------------------|---------|
| Task 8  | Admin Dashboard             | Soud Karim         | Pending |
| Task 9  | Menu Items — List & Read    | Abd El-Rahman Yasser | Pending |
| Task 10 | Menu Items — Create         | Hamza              | Pending |
| Task 11 | Menu Items — Edit & Update  | Ziad Sameh         | Pending |
| Task 12 | Menu Items — Delete         | Habiba             | Pending |
| Task 13 | PDF Menu Upload             | Alaa               | Pending |
| Task 14 | Admin — View All Orders     | Ziad Yasser        | Pending |

---

## Developer Assignment

| Developer              | Role                        | Responsibility                                              |
|------------------------|-----------------------------|-------------------------------------------------------------|
| **Soud Karim**         | Developer                   | Task 8 — Admin dashboard with summary stats                 |
| **Abd El-Rahman Yasser** | Developer                 | Task 9 — Menu items list page                               |
| **Hamza**              | Developer                   | Task 10 — Create menu item with image upload                |
| **Ziad Sameh**         | Developer                   | Task 11 — Edit and update menu item                         |
| **Habiba**             | Developer                   | Task 12 — Delete menu item and its image file               |
| **Alaa**               | Developer                   | Task 13 — PDF menu upload                                   |
| **Ziad Yasser**        | Developer                   | Task 14 — Admin orders list and detail view                 |
| **Belal Moustafa**     | Team Leader & Code Reviewer | Review and approve all tasks before Phase 5 begins          |

---

## Task Dependencies Within This Phase

```
Task 8  (Dashboard)  — can start immediately after Phase 3
Task 9  (List)       — can start immediately after Phase 3
Task 10 (Create)     — can start immediately after Phase 3
Task 11 (Edit)       — depends on Task 10 (items must exist to edit)
Task 12 (Delete)     — depends on Task 10 (items must exist to delete)
Task 13 (PDF Upload) — independent, can run parallel with Tasks 8–10
Task 14 (Orders)     — depends on Phase 5 Task 16 for real data, but UI can be built now
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for ALL tasks in this phase.  
No task output moves to Phase 5 without Belal's explicit approval.

---

## Phase Completion Criteria
- [ ] All admin pages redirect non-admins away using `requireAdmin()`
- [ ] Admin dashboard shows correct counts from the database
- [ ] Full CRUD cycle on `menu_items` works end-to-end
- [ ] Image upload validates type (PNG/JPG) and size, stores path in DB
- [ ] PDF upload validates type, stores path in DB
- [ ] Old image/PDF files are deleted from disk when replaced or item is deleted
- [ ] All queries use MySQLi OO prepared statements
- [ ] All tasks reviewed and approved by Belal Moustafa

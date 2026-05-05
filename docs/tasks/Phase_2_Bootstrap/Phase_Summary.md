# Phase 2 - Project Bootstrap & Configuration

## Overall Goal

Stand up the project skeleton, database connection layer, global assets, and shared PHP includes.

By the end of this phase, the system has the reusable infrastructure needed by authentication, admin features, user features, and security hardening.

---

## Tasks in This Phase

| Task # | Task Name | Assigned Developer | Status |
|--------|-----------|--------------------|--------|
| Task 2 | Project Skeleton & DB Connection | Ziad Sameh | Completed |
| Task 3 | Global Assets - CSS and JS | Hamza | Completed |
| Task 4 | Shared Includes - Header, Footer and Auth Helpers | Alaa | Completed |

---

## Developer Assignment

| Developer | Role | Responsibility |
|-----------|------|----------------|
| **Ziad Sameh** | Developer | Task 2 - Create directories and write `config/db.php`. |
| **Hamza** | Developer | Task 3 - Write the monochrome CSS design system and global JavaScript helpers. |
| **Alaa** | Developer | Task 4 - Write `includes/header.php`, `includes/footer.php`, and base auth helpers. |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve all three tasks before Phase 3. |

---

## Assignment Update

The final roster assigns Task 3 to **Hamza**.

This reflects the corrected task swap between Hamza and Habiba:

- Hamza owns Task 3: Global Assets.
- Habiba owns Task 1 and Task 10.

---

## Task Dependencies Within This Phase

```text
Task 2 (Skeleton) -> Task 4 (Includes)
Task 3 (CSS/JS)   -> Task 4 (Includes)
Task 2 and Task 3 can be worked on in parallel.
Task 4 depends on the directories and assets being available.
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for all tasks in this phase.

No task output moves to Phase 3 without Belal's approval.

---

## Phase Completion Criteria

- [x] All directories from the project structure exist.
- [x] `config/db.php` connects to `restaurant_db` through MySQLi OO.
- [x] `assets/css/style.css` implements the monochrome design system.
- [x] `assets/js/main.js` provides client-side validation and UI helpers.
- [x] `includes/auth.php` exposes login, role, flash, and security helpers.
- [x] `includes/header.php` renders the navbar and links the stylesheet.
- [x] `includes/footer.php` closes the layout and loads JavaScript.
- [x] All files reviewed and approved by Belal Moustafa.

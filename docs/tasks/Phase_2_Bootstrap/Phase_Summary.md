# Phase 2 — Project Bootstrap & Configuration

## Overall Goal
Stand up the project skeleton on disk, establish the database connection layer, build the complete monochrome CSS design system, and create the shared PHP includes that every page in the application will rely on. By the end of this phase, the project has a working foundation — no feature pages yet, but all shared infrastructure is in place.

---

## Tasks in This Phase

| Task # | Task Name                          | Assigned Developer | Status  |
|--------|------------------------------------|--------------------|---------|
| Task 2 | Project Skeleton & DB Connection   | Ziad Sameh         | Pending |
| Task 3 | Global Assets — CSS & JS           | Habiba             | Pending |
| Task 4 | Shared Includes — Header, Footer & Auth Helpers | Alaa  | Pending |

---

## Developer Assignment

| Developer          | Role                        | Responsibility                                                  |
|--------------------|-----------------------------|-----------------------------------------------------------------|
| **Ziad Sameh**     | Developer                   | Task 2 — Create all directories and write `config/db.php`      |
| **Habiba**         | Developer                   | Task 3 — Write the full monochrome CSS design system and JS helpers |
| **Alaa**           | Developer                   | Task 4 — Write `includes/header.php`, `footer.php`, `auth.php` |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve all three tasks before Phase 3 begins        |

---

## Task Dependencies Within This Phase

```
Task 2 (Skeleton) ──► Task 4 (Includes) — Alaa needs the directory structure in place
Task 3 (CSS/JS)   ──► Task 4 (Includes) — header.php links to the stylesheet
Task 2 & Task 3   can be worked on in PARALLEL
Task 4            must start AFTER Task 2 and Task 3 are complete
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for ALL tasks in this phase.  
No task output moves to Phase 3 without Belal's explicit approval.

---

## Phase Completion Criteria
- [ ] All directories from the project structure exist on disk
- [ ] `config/db.php` connects to `restaurant_db` via MySQLi OO without errors
- [ ] `assets/css/style.css` implements the full monochrome design system
- [ ] `assets/js/main.js` provides client-side form validation helpers
- [ ] `includes/auth.php` exposes `isLoggedIn()`, `isAdmin()`, and redirect helpers
- [ ] `includes/header.php` renders the navbar and links the stylesheet
- [ ] `includes/footer.php` closes the HTML layout cleanly
- [ ] All files reviewed and approved by Belal Moustafa

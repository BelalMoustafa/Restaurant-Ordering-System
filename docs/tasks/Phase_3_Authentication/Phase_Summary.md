# Phase 3 — Authentication System

## Overall Goal
Implement the complete user authentication flow: Registration, Login, and Logout. This phase introduces secure password handling via `password_hash()` and `password_verify()`, session management, and the "Remember Me" cookie feature. No user or admin feature is accessible until this phase is complete.

---

## Tasks in This Phase

| Task # | Task Name              | Assigned Developer | Status  |
|--------|------------------------|--------------------|---------|
| Task 5 | User Registration Page | Ziad Yasser        | Pending |
| Task 6 | User Login Page        | Ziad Walid         | Pending |
| Task 7 | Logout                 | Ziad Marzouk       | Pending |

---

## Developer Assignment

| Developer          | Role                        | Responsibility                                              |
|--------------------|-----------------------------|-------------------------------------------------------------|
| **Ziad Yasser**    | Developer                   | Task 5 — Registration form, validation, password hashing   |
| **Ziad Walid**     | Developer                   | Task 6 — Login form, password verify, session, cookie      |
| **Ziad Marzouk**   | Developer                   | Task 7 — Logout: session destroy, cookie clear, redirect   |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve all three tasks before Phase 4 begins   |

---

## Task Dependencies Within This Phase

```
Task 5 (Register) ──► Task 6 (Login) — Login depends on registered users existing
Task 6 (Login)    ──► Task 7 (Logout) — Logout depends on a session being created by login
Tasks 5, 6, 7 are sequential in this phase
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for ALL tasks in this phase.  
No task output moves to Phase 4 without Belal's explicit approval.

---

## Phase Completion Criteria
- [ ] `auth/register.php` creates new users with hashed passwords
- [ ] `auth/login.php` verifies credentials, sets `$_SESSION['user_id']` and `$_SESSION['role']`
- [ ] "Remember Me" cookie is set on login and read on subsequent visits
- [ ] `auth/logout.php` destroys the session and clears the cookie
- [ ] Admin users are redirected to `admin/dashboard.php` after login
- [ ] Regular users are redirected to `user/dashboard.php` after login
- [ ] All database queries use MySQLi OO prepared statements
- [ ] All tasks reviewed and approved by Belal Moustafa

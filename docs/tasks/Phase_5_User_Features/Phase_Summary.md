# Phase 5 — User Features

## Overall Goal
Build the user-facing side of the application. Regular users can browse the menu, place orders, and view their own order history. All pages are protected — only authenticated users (or admins) can access them.

---

## Tasks in This Phase

| Task # | Task Name                      | Assigned Developer | Status  |
|--------|--------------------------------|--------------------|---------|
| Task 15 | User Dashboard & Menu Page    | Ziad Walid         | Pending |
| Task 16 | Place an Order                | Ziad Marzouk       | Pending |
| Task 17 | My Orders — Order History     | Soud Karim         | Pending |

---

## Developer Assignment

| Developer          | Role                        | Responsibility                                              |
|--------------------|-----------------------------|-------------------------------------------------------------|
| **Ziad Walid**     | Developer                   | Task 15 — User dashboard and menu display page              |
| **Ziad Marzouk**   | Developer                   | Task 16 — Place order form and POST handler                 |
| **Soud Karim**     | Developer                   | Task 17 — User's personal order history page                |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve all tasks before Phase 6 begins          |

---

## Task Dependencies Within This Phase

```
Task 15 (Dashboard/Menu) — can start immediately after Phase 4
Task 16 (Place Order)    — depends on Task 15 (menu must be browsable first)
Task 17 (My Orders)      — depends on Task 16 (orders must exist to display)
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for ALL tasks in this phase.  
No task output moves to Phase 6 without Belal's explicit approval.

---

## Phase Completion Criteria
- [ ] Users can browse the full menu with images and prices
- [ ] Users can place an order for any available menu item
- [ ] Order is correctly inserted into the `orders` table with the correct `user_id`
- [ ] Users can view only their own orders (not other users' orders)
- [ ] All queries use MySQLi OO prepared statements
- [ ] All tasks reviewed and approved by Belal Moustafa

# Phase 5 - User Features

## Overall Goal

Build the user-facing side of the Restaurant Ordering System.

Regular users can browse the menu, place orders, and view their own order history. User pages are protected so guests cannot access them, and admin accounts are kept out of the regular user ordering flow where appropriate.

---

## Tasks in This Phase

| Task # | Task Name | Assigned Developer | Status |
|--------|-----------|--------------------|--------|
| Task 15 | User Dashboard & Menu Page | Ziad Walid | Completed |
| Task 16 | Place an Order | Ziad Marzouk & Hassan | Completed |
| Task 17 | My Orders - Order History | Soud Karim | Completed |

---

## Developer Assignment

| Developer | Role | Responsibility |
|-----------|------|----------------|
| **Ziad Walid** | Developer | Task 15 - User dashboard and menu display page. |
| **Ziad Marzouk** | Developer | Task 16 - Order page UI, session flow, item selection, and user-facing order form. |
| **Hassan** | Security & Backend Developer | Task 16 - Backend price validation, quantity validation, database insertion, CSRF protection, and security fixes. |
| **Soud Karim** | Developer | Task 17 - User's personal order history page. |
| **Belal Moustafa** | Team Leader & Code Reviewer | Review and approve all tasks before Phase 6. |

---

## Hassan's Phase 5 Role

Hassan co-developed Task 16 because order placement is the most security-sensitive user feature.

His responsibilities included:

- Validating CSRF before order processing.
- Fetching the true menu item price directly from the database.
- Preventing DOM manipulation and Inspect Element price tampering.
- Validating quantity boundaries.
- Calculating `total_price` securely on the server.
- Inserting orders with MySQLi OO prepared statements.
- Ensuring dynamic values are escaped to reduce XSS risk.

Ziad Marzouk handled:

- UI layout.
- Session-based user flow.
- Item selection behavior.
- Pre-selected item behavior from the menu page.
- Form display and redirect flow.

---

## Task Dependencies Within This Phase

```text
Task 15 (Dashboard/Menu) -> Task 16 (Place Order)
Task 16 (Place Order)    -> Task 17 (My Orders)
Task 16 also feeds data into Phase 4 Task 14 (Admin Orders).
```

---

## Reviewer

**Belal Moustafa** is the designated reviewer for all tasks in this phase.

No task output moves to Phase 6 without Belal's approval.

---

## Phase Completion Criteria

- [x] Users can browse the menu with images and prices.
- [x] Users can place an order for available menu items.
- [x] Order creation uses the logged-in user's session ID.
- [x] Item price is fetched from the database during order submission.
- [x] Browser-submitted prices are never trusted.
- [x] Quantity is validated between 1 and 20.
- [x] Total price is calculated server-side.
- [x] Order insert uses MySQLi OO prepared statements.
- [x] Users can view only their own orders.
- [x] CSRF protection is applied to order placement.
- [x] XSS-safe escaping is applied to dynamic output.
- [x] All tasks reviewed and approved by Belal Moustafa.

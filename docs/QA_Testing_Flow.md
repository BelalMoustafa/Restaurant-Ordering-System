# QA Testing Flow & User Stories

## 1. Pre-requisites

### Local Environment

- XAMPP or MAMP installed.
- Apache server running.
- MySQL server running.
- Project folder placed under the local web root:
  - XAMPP example: `C:\xampp\htdocs\Restaurant Ordering System`
- Browser available for manual testing.

### Database Setup

1. Open phpMyAdmin or MySQL CLI.
2. Import the SQL file:

```text
database/schema.sql
```

3. Confirm the database name is:

```text
restaurant_db
```

4. Confirm exactly these 3 tables exist:

```text
users
menu_items
orders
```

### Test URL

Use:

```text
http://localhost/Restaurant%20Ordering%20System/index.php
```

### Default Seeded Accounts

Admin account:

```text
Email: admin@restaurant.com
Password: admin123
Role: admin
```

Default user account:

```text
Email: user@restaurant.com
Password: admin123
Role: user
```

### Required Test Files

Prepare these local files before testing:

```text
valid-image.jpg
valid-image.png
invalid-image.txt
valid-menu.pdf
invalid-menu.txt
```

Recommended image size:

```text
Less than 2MB
```

Recommended PDF size:

```text
Less than 5MB
```

### Browser Setup

- Use an incognito/private browser window for clean session testing.
- Keep browser developer tools available for CSRF and cookie verification.
- Clear cookies between remember-me tests when required.

---

## 2. Core User Stories

### Guest User Stories

- As a Guest, I want to view the public landing page so that I can see the restaurant menu preview.
- As a Guest, I want to register a new account so that I can place orders.
- As a Guest, I want to log in so that I can access customer features.
- As a Guest, I want to use Remember Me so that I can stay logged in across browser restarts.
- As a Guest, I should not access protected user or admin pages without authentication.

### Customer User Stories

- As a User, I want to view all available menu items so that I can decide what to order.
- As a User, I want to place an order for an available menu item so that the restaurant receives my request.
- As a User, I want order totals calculated by the system so that I cannot manipulate prices.
- As a User, I want to view my order history so that I can track my previous orders.
- As a User, I want to see only my own orders so that other customers' information remains private.
- As a User, I want to log out so that my session is securely ended.
- As a User, I must not access admin-only pages.

### Admin User Stories

- As an Admin, I want to view dashboard statistics so that I can monitor menu and order activity.
- As an Admin, I want to create menu items so that customers can order them.
- As an Admin, I want to upload JPG or PNG item images so that the menu is visually complete.
- As an Admin, I want to reject invalid image files so that unsafe uploads are blocked.
- As an Admin, I want to edit menu items so that I can update names, descriptions, prices, categories, availability, and images.
- As an Admin, I want to create a menu item with price `0.00` so that complimentary items are supported.
- As an Admin, I want to delete menu items with no orders so that unused data can be removed.
- As an Admin, I want deletion blocked for menu items that have orders so that order history is preserved.
- As an Admin, I want to upload a PDF menu so that customers can download the full menu.
- As an Admin, I want to view all orders so that I can manage customer requests.
- As an Admin, I want to update order statuses so that customers' order states are tracked.
- As an Admin, I want all state-changing actions protected by CSRF tokens so that forged requests are rejected.

---

## 3. Step-by-Step Manual Test Execution: Golden Flow

## Scenario 1: Guest Browsing, Registration, Login, and Remember Me

### 1.1 Open Public Landing Page

- Action: Open `http://localhost/Restaurant%20Ordering%20System/index.php`.
- Expected Result: The landing page loads successfully with a monochrome layout, restaurant branding, menu preview, and Login/Register navigation links.

### 1.2 Verify Guest Menu Preview

- Action: Scroll to the menu section.
- Expected Result: Available menu items appear with name, category, price, description, and either an image or `No Image` placeholder.

### 1.3 Attempt to Access User Menu While Logged Out

- Action: Navigate directly to `http://localhost/Restaurant%20Ordering%20System/user/menu.php`.
- Expected Result: The system redirects to the login page.

### 1.4 Open Registration Page

- Action: Click `Register`.
- Expected Result: Registration form appears with Full Name, Email Address, Password, and Confirm Password fields.

### 1.5 Submit Empty Registration Form

- Action: Click `Create Account` without entering data.
- Expected Result: Required field validation messages appear and no account is created.

### 1.6 Submit Invalid Email

- Action: Enter `QA Tester`, email `invalid-email`, password `Password123`, confirm password `Password123`, then submit.
- Expected Result: The system shows an invalid email message and does not create the account.

### 1.7 Submit Password Mismatch

- Action: Enter email `qa.user@example.com`, password `Password123`, confirm password `Different123`, then submit.
- Expected Result: The system shows a password mismatch message and does not create the account.

### 1.8 Register Valid User

- Action: Enter name `QA User`, email `qa.user@example.com`, password `Password123`, confirm password `Password123`, then click `Create Account`.
- Expected Result: The system redirects to login with a success message confirming account creation.

### 1.9 Login With Wrong Password

- Action: Enter `qa.user@example.com` with password `WrongPassword`.
- Expected Result: The system shows a generic invalid email or password message.

### 1.10 Login With Remember Me

- Action: Enter `qa.user@example.com`, password `Password123`, check `Remember me for 30 days`, then click `Login`.
- Expected Result: The system logs in and redirects to the user dashboard.

### 1.11 Verify Remember Me Cookie

- Action: Open browser developer tools and inspect cookies for the application.
- Expected Result: A `remember_user` cookie exists, is HttpOnly, and contains a random token rather than a user ID or password.

### 1.12 Test Remember Me Auto Login

- Action: Close the browser tab, reopen the site in the same browser session, and navigate to `auth/login.php`.
- Expected Result: The system automatically authenticates the remembered user and redirects to the user dashboard.

### 1.13 Logout

- Action: Click `Logout`.
- Expected Result: The session ends, the remember-me cookie is cleared, and the user is redirected to login with a logout confirmation message.

---

## Scenario 2: Admin Menu Management

### 2.1 Login as Admin

- Action: Go to `auth/login.php`, enter `admin@restaurant.com`, password `admin123`, and click `Login`.
- Expected Result: The system redirects to the admin dashboard.

### 2.2 Verify Admin Dashboard

- Action: Review the dashboard statistics.
- Expected Result: Total Menu Items, Available Items, Total Orders, and Pending Orders are displayed.

### 2.3 Open Menu Items Page

- Action: Click `Menu Items`.
- Expected Result: A table of all menu items appears with image, name, category, price, availability, and actions.

### 2.4 Open Create Menu Item Page

- Action: Click `Add New Item`.
- Expected Result: The Add Menu Item form appears.

### 2.5 Test Required Validation

- Action: Submit the create form empty.
- Expected Result: Required field validation messages appear and no item is created.

### 2.6 Test Negative Price Rejection

- Action: Enter name `QA Negative Price Item`, category `QA`, price `-1`, then submit.
- Expected Result: The system rejects the price and shows that price cannot be negative.

### 2.7 Test Price Zero Acceptance

- Action: Enter name `QA Complimentary Item`, description `Free QA item`, category `QA`, price `0`, leave image empty, and submit.
- Expected Result: The system creates the item successfully and redirects to the menu items list with a success message.

### 2.8 Verify Zero Price Display

- Action: Find `QA Complimentary Item` in the menu items table.
- Expected Result: The item appears with price `$0.00`.

### 2.9 Create Item With Valid JPG Image

- Action: Click `Add New Item`, enter name `QA Image Item`, description `QA image upload test`, category `QA`, price `12.50`, upload `valid-image.jpg`, and submit.
- Expected Result: The item is created successfully and appears in the menu items list with a thumbnail image.

### 2.10 Test Invalid Image Upload

- Action: Click `Add New Item`, enter valid name/category/price, upload `invalid-image.txt`, and submit.
- Expected Result: The system rejects the upload and shows an invalid file type or extension message.

### 2.11 Edit Menu Item

- Action: Click `Edit` for `QA Image Item`.
- Expected Result: The edit form opens with the current item data pre-filled.

### 2.12 Update Item Details

- Action: Change name to `QA Image Item Updated`, change price to `15.75`, change description to `Updated QA description`, and click `Save Changes`.
- Expected Result: The system redirects to the menu items list with a success message and updated values.

### 2.13 Replace Item Image

- Action: Edit `QA Image Item Updated`, upload `valid-image.png`, and save.
- Expected Result: The system updates the item and displays the new image.

### 2.14 Delete Item Without Orders

- Action: Delete `QA Complimentary Item`.
- Expected Result: The item is deleted successfully because it has no related orders.

### 2.15 Upload Valid PDF Menu

- Action: Click `Upload PDF`, choose `valid-menu.pdf`, and submit.
- Expected Result: The PDF uploads successfully and the page shows a link to view the current PDF.

### 2.16 Replace PDF Menu

- Action: Upload `valid-menu.pdf` again.
- Expected Result: The previous PDF is replaced and a success message appears.

### 2.17 Reject Invalid PDF Upload

- Action: Upload `invalid-menu.txt` on the PDF upload page.
- Expected Result: The system rejects the file and shows an invalid file type or extension message.

---

## Scenario 3: User Browsing and Order Placement

### 3.1 Logout Admin

- Action: Click `Logout`.
- Expected Result: Admin session ends and the login page appears.

### 3.2 Login as User

- Action: Log in with `qa.user@example.com` and password `Password123`.
- Expected Result: The system redirects to the user dashboard.

### 3.3 Open User Menu

- Action: Click `Menu`.
- Expected Result: The full menu appears, grouped by category, showing only available items.

### 3.4 Verify Admin-only Links Are Hidden

- Action: Review the navigation bar.
- Expected Result: Admin links such as Menu Items, Orders, and Upload PDF are not visible.

### 3.5 Start Order From Menu

- Action: Click `Order This` on `QA Image Item Updated`.
- Expected Result: The system opens the Place Order page with the selected item pre-selected.

### 3.6 Verify Price Preview

- Action: Set quantity to `2`.
- Expected Result: The price preview updates client-side and shows the correct calculated total based on item price.

### 3.7 Place Valid Order

- Action: Add notes `QA order note`, then click `Place Order`.
- Expected Result: The system creates the order, redirects to My Orders, and shows a success message.

### 3.8 Verify Order History

- Action: Review the My Orders table.
- Expected Result: The new order appears with correct item name, quantity `2`, total price `$31.50`, pending status, notes, and date.

### 3.9 Verify Server-side Price Integrity

- Action: Use browser developer tools to alter any client-side price display, then place another order.
- Expected Result: The stored order total still uses the database price and is not affected by client-side changes.

### 3.10 Test Quantity Minimum

- Action: Go to Place Order, select an item, enter quantity `0`, and submit.
- Expected Result: The system rejects the order and shows that quantity must be between 1 and 20.

### 3.11 Test Quantity Maximum

- Action: Enter quantity `21` and submit.
- Expected Result: The system rejects the order and shows that quantity must be between 1 and 20.

---

## Scenario 4: Admin Order Processing and CSRF Protection

### 4.1 Login as Admin

- Action: Logout as user, then login as `admin@restaurant.com` with password `admin123`.
- Expected Result: The system redirects to the admin dashboard.

### 4.2 Open All Orders

- Action: Click `Orders`.
- Expected Result: The All Orders table appears and includes the order created by the QA user.

### 4.3 View Order Details

- Action: Click `View Details` for the QA user's order.
- Expected Result: The order detail page shows order ID, customer name, email, item, unit price, quantity, total price, notes, date, and current status.

### 4.4 Update Order Status to Confirmed

- Action: Select status `Confirmed` and click `Update Status`.
- Expected Result: The system updates the order status, redirects back to the detail page, and shows a success message.

### 4.5 Verify Status Badge

- Action: Review the order detail page and All Orders table.
- Expected Result: The order status displays as Confirmed.

### 4.6 Update Order Status to Cancelled

- Action: Select status `Cancelled` and click `Update Status`.
- Expected Result: The system updates the order status and shows a success message.

### 4.7 Test Invalid Status Rejection

- Action: Use browser developer tools to change the status form value to `hacked`, then submit.
- Expected Result: The system rejects the value, redirects back to the order detail page, and shows an invalid status message.

### 4.8 Test CSRF Protection on Status Update

- Action: Use browser developer tools to remove the hidden `csrf_token` input from the status form, then submit.
- Expected Result: The system rejects the request, shows an invalid request token message, and does not update the order status.

### 4.9 Test CSRF Protection With Modified Token

- Action: Change the hidden `csrf_token` value to `invalid-token`, then submit.
- Expected Result: The system rejects the request, shows an invalid request token message, and does not update the order status.

### 4.10 Verify Order Still Exists After Menu Item Delete Attempt

- Action: Go to Menu Items and attempt to delete `QA Image Item Updated`, which has orders.
- Expected Result: The system blocks deletion and shows a message instructing the admin to mark it unavailable instead.

---

## Scenario 5: Security and Edge Case Testing

### 5.1 User Cannot Access Admin Dashboard

- Action: Login as `qa.user@example.com`, then navigate directly to `admin/dashboard.php`.
- Expected Result: The system redirects away from the admin page and does not show admin content.

### 5.2 User Cannot Access Admin Menu Management

- Action: Navigate directly to `admin/menu_items/index.php` as a normal user.
- Expected Result: The system blocks access and redirects away from the admin page.

### 5.3 User Cannot Access Admin Orders

- Action: Navigate directly to `admin/orders/index.php` as a normal user.
- Expected Result: The system blocks access and redirects away from the admin page.

### 5.4 Admin Cannot Use User Ordering Flow

- Action: Login as admin and navigate directly to `user/place_order.php`.
- Expected Result: The system redirects the admin to the admin dashboard.

### 5.5 Guest Cannot Access Protected User Pages

- Action: Logout, then navigate directly to `user/my_orders.php`.
- Expected Result: The system redirects to login.

### 5.6 Test XSS in Menu Item Description

- Action: Login as admin, create or edit a menu item with description `<script>alert('xss')</script>`.
- Expected Result: The item saves, but when displayed, the script is escaped as text and no JavaScript alert runs.

### 5.7 Test XSS in Order Notes

- Action: Login as user, place an order with notes `<script>alert('order-xss')</script>`.
- Expected Result: The order saves, but notes are escaped as text in My Orders and Admin Order Details; no JavaScript alert runs.

### 5.8 Test SQL Injection in Login

- Action: On login page, enter email `' OR '1'='1` and any password.
- Expected Result: Login fails with a generic invalid email or password message.

### 5.9 Test SQL Injection in Order Item ID

- Action: Use browser developer tools to alter `menu_item_id` to `1 OR 1=1` before submitting an order.
- Expected Result: The system rejects the item selection or treats it as invalid; no SQL error appears.

### 5.10 Test Direct GET Delete Attempt

- Action: Navigate directly to `admin/menu_items/delete.php?id=1` as admin.
- Expected Result: The system rejects GET access and redirects to the menu items list.

### 5.11 Test Upload PHP File as Image

- Action: Attempt to upload a `.php` file through the menu item image upload field.
- Expected Result: The system rejects the file due to invalid MIME type or extension.

### 5.12 Test Upload PHP File as PDF

- Action: Attempt to upload a `.php` file through the PDF upload field.
- Expected Result: The system rejects the file due to invalid MIME type or extension.

### 5.13 Verify Uploads Do Not Execute PHP

- Action: If a PHP file is manually placed in `uploads/`, try to open it through the browser.
- Expected Result: Apache denies access or does not execute the PHP file.

### 5.14 Verify Config Directory Protection

- Action: Navigate directly to `config/db.php`.
- Expected Result: Access is forbidden or no sensitive database details are exposed.

### 5.15 Verify Includes Directory Protection

- Action: Navigate directly to `includes/auth.php`.
- Expected Result: Access is forbidden or direct access is rejected.

### 5.16 Verify Logout Clears Session

- Action: Login as user, click `Logout`, then press browser back to return to the dashboard.
- Expected Result: Protected dashboard content is not accessible and the user is redirected to login if the page is refreshed.

### 5.17 Verify Remember Me Cleared on Logout

- Action: Login with Remember Me checked, logout, then inspect browser cookies.
- Expected Result: The `remember_user` cookie is cleared or expired.

### 5.18 Verify Only Own Orders Are Visible

- Action: Login as `qa.user@example.com` and open My Orders.
- Expected Result: Only orders belonging to `qa.user@example.com` appear.

### 5.19 Verify Admin Sees All Orders

- Action: Login as admin and open All Orders.
- Expected Result: Orders from all users appear.

### 5.20 Final End-to-End Acceptance

- Action: Complete the full path: Guest landing page -> Register -> Login -> Browse Menu -> Place Order -> View My Orders -> Admin Login -> View Order -> Update Status -> Logout.
- Expected Result: Every step completes successfully without PHP warnings, SQL errors, broken redirects, unauthorized access, or security bypasses.

# Project Business Logic

## 1. Project Purpose

The Restaurant Ordering System is a local web application built for a university project. Its purpose is to simulate the core workflow of a small restaurant that wants to publish a digital menu, let customers register and place orders, and let administrators manage menu items and order statuses.

The project has two main roles:

- Admin: Manages restaurant content and order processing.
- User: Browses the menu and places orders.

The system is intentionally simple and focused. It does not try to implement online payment, delivery tracking, inventory management, or third-party integrations. That is a deliberate scope decision because the project requirement is to demonstrate Core PHP, MySQL, authentication, authorization, CRUD operations, secure database access, file upload validation, and local execution.

## 2. Main Business Actors

### Guest

A Guest is anyone who visits the public landing page without logging in.

Guest capabilities:

- View the public landing page.
- Preview available menu items.
- Register a new user account.
- Log in to an existing account.

Guest restrictions:

- Cannot place orders.
- Cannot view order history.
- Cannot access admin pages.
- Cannot perform any state-changing operation.

### User

A User is a registered customer with the role `user`.

User capabilities:

- Log in and log out.
- Browse available menu items.
- Download the PDF menu if uploaded.
- Place an order for an available menu item.
- Add optional special notes to an order.
- View only their own order history.

User restrictions:

- Cannot access admin dashboard.
- Cannot create, edit, or delete menu items.
- Cannot upload menu PDFs.
- Cannot view other users' orders.
- Cannot change order status.
- Cannot place orders as another user.

### Admin

An Admin is a privileged account with the role `admin`.

Admin capabilities:

- Log in and log out.
- View dashboard statistics.
- Create menu items.
- Edit menu items.
- Delete menu items only when they have no related orders.
- Upload JPG or PNG menu item images.
- Upload and replace the restaurant PDF menu.
- View all orders from all users.
- View full detail for a single order.
- Update order status to `pending`, `confirmed`, or `cancelled`.

Admin restrictions:

- Cannot use the customer ordering flow.
- Cannot delete menu items that already have order history.
- Cannot upload invalid file types.
- Cannot bypass CSRF protection on state-changing forms.

## 3. End-to-End Business Flow

## 3.1 Public Entry Flow

The user journey begins at `index.php`.

Business purpose:

- Introduce the restaurant.
- Show a public preview of available menu items.
- Encourage guests to register or log in.

Flow:

1. Guest opens the landing page.
2. System queries available menu items from the database.
3. System displays a maximum public preview of menu items.
4. Guest can choose to register, log in, or view more menu information after authentication.

Important business rule:

- Only menu items with `is_available = 1` are shown publicly.

Why this matters:

- Admins can hide unavailable items without deleting them.
- Users cannot order items that are intentionally hidden.

## 3.2 Registration Flow

File responsible:

- `auth/register.php`

Business purpose:

- Allow a new customer to create a user account.

Flow:

1. Guest opens the registration page.
2. Guest enters full name, email, password, and password confirmation.
3. System validates required fields.
4. System validates email format.
5. System validates password length.
6. System checks whether the email already exists.
7. System hashes the password using `password_hash()`.
8. System inserts the new user with role `user`.
9. System redirects to login with a success message.

Business rules:

- Email must be unique.
- Passwords must never be stored as plain text.
- New registrations always receive the `user` role.
- Users cannot assign themselves admin privileges.
- Registration POST requests must pass CSRF validation.

Why this matters:

- Unique emails make login identity clear.
- Password hashing protects users if database data is exposed.
- Fixed user role prevents privilege escalation.
- CSRF protection prevents forced account creation from another website.

## 3.3 Login Flow

File responsible:

- `auth/login.php`

Business purpose:

- Authenticate users and admins.
- Start a secure session.
- Optionally remember the user through a cookie.

Flow:

1. Visitor enters email and password.
2. System validates that fields are not empty.
3. System fetches the user by email using a prepared statement.
4. System validates the password using `password_verify()`.
5. System regenerates the session ID.
6. System stores `user_id`, `role`, and `user_name` in `$_SESSION`.
7. If Remember Me is checked:
   - System generates a random token.
   - System hashes it with SHA-256.
   - System stores only the hash in the database.
   - System stores the raw token in an HttpOnly cookie.
8. System redirects based on role:
   - Admin goes to `admin/dashboard.php`.
   - User goes to `user/dashboard.php`.

Business rules:

- Login errors must be generic.
- Session ID must be regenerated after successful login.
- Remember-me database value stores only the hashed token.
- Raw remember-me token exists only in the browser cookie.
- Login POST requests must pass CSRF validation.

Why this matters:

- Generic errors prevent attackers from discovering registered emails.
- Session regeneration prevents session fixation attacks.
- Hashed remember-me tokens reduce damage if the database is exposed.
- CSRF protection prevents malicious login form submissions.

## 3.4 User Menu Browsing Flow

Files responsible:

- `user/dashboard.php`
- `user/menu.php`

Business purpose:

- Let authenticated customers browse the full available menu.

Flow:

1. User logs in.
2. User opens dashboard.
3. User clicks Menu.
4. System fetches available menu items from the database.
5. System groups menu items by category.
6. System displays item name, description, category, price, and image.
7. User can click `Order This` to start an order for a selected item.

Business rules:

- Only authenticated users can access user menu pages.
- Admin accounts are redirected away from user ordering pages.
- Only available items are shown.
- Item data is escaped before output.

Why this matters:

- Role separation keeps admin and customer flows clean.
- Hidden items cannot be ordered accidentally.
- Output escaping prevents stored XSS through item names or descriptions.

## 3.5 Order Placement Flow

File responsible:

- `user/place_order.php`

Business purpose:

- Let a user place a single-line order for one menu item.

Flow:

1. User opens the order form.
2. System fetches all available menu items.
3. User selects an item.
4. User enters quantity.
5. User optionally enters notes.
6. System validates CSRF token before processing the POST request.
7. System validates that selected item exists and is available.
8. System validates quantity is between 1 and 20.
9. System fetches the item price from the database.
10. System calculates total price server-side.
11. System inserts order with:
    - `user_id` from session.
    - `menu_item_id` from validated item.
    - `quantity`.
    - `total_price`.
    - default status `pending`.
    - optional notes.
12. System redirects user to My Orders with success message.

Business rules:

- User ID must come from session, never from POST.
- Price must come from database, never from client-side fields.
- Quantity must be limited.
- Item must be available at the time of order.
- Order creation must pass CSRF validation.

Why this matters:

- Prevents users from placing orders as another user.
- Prevents users from manipulating price in browser developer tools.
- Prevents invalid or excessive quantities.
- Prevents ordering hidden or unavailable items.

## 3.6 User Order History Flow

File responsible:

- `user/my_orders.php`

Business purpose:

- Let users review their own previous orders.

Flow:

1. User opens My Orders.
2. System fetches orders where `orders.user_id = $_SESSION['user_id']`.
3. System joins order data with menu item names.
4. System displays item, quantity, total, status, notes, and date.

Business rules:

- Users only see their own orders.
- Notes must be escaped before output.
- Order status is read-only for users.

Why this matters:

- Protects customer privacy.
- Prevents cross-account data exposure.
- Keeps status control in the admin role only.

## 3.7 Admin Dashboard Flow

File responsible:

- `admin/dashboard.php`

Business purpose:

- Give admins a quick overview of restaurant system activity.

Flow:

1. Admin logs in.
2. Admin lands on dashboard.
3. System calculates:
   - Total menu items.
   - Available menu items.
   - Total orders.
   - Pending orders.
4. Admin sees quick action links.

Business rules:

- Only admins can access dashboard.
- Counts are read from the database.
- Dashboard is read-only.

Why this matters:

- Helps admins understand the current system state quickly.
- Keeps management tools grouped in one place.

## 3.8 Admin Menu Management Flow

Files responsible:

- `admin/menu_items/index.php`
- `admin/menu_items/create.php`
- `admin/menu_items/edit.php`
- `admin/menu_items/delete.php`

Business purpose:

- Allow admins to manage restaurant menu items.

### Create Menu Item

Flow:

1. Admin opens Add New Item.
2. Admin enters name, description, price, category, and availability.
3. Admin optionally uploads an image.
4. System validates CSRF token.
5. System validates fields.
6. System validates image extension and MIME type.
7. System saves the uploaded image if valid.
8. System inserts the item into `menu_items`.

Business rules:

- Name is required.
- Category is required.
- Price is required and cannot be negative.
- Price `0.00` is valid.
- Images must be JPG or PNG.
- Image size must be limited.
- Create action must pass CSRF validation.

### Edit Menu Item

Flow:

1. Admin opens Edit for a menu item.
2. System fetches the current item by ID.
3. Admin updates fields.
4. Admin optionally uploads a replacement image.
5. System validates CSRF token before state-changing work.
6. System validates inputs and file.
7. System replaces old image if a new image is uploaded.
8. System updates the row.

Business rules:

- Existing item must exist.
- CSRF token must be valid before update processing.
- Replacement images follow the same rules as create.
- Old image should be removed from disk when replaced.

### Delete Menu Item

Flow:

1. Admin submits delete form.
2. System validates POST method.
3. System validates CSRF token.
4. System fetches item by ID.
5. System checks if the item has existing orders.
6. If orders exist, deletion is blocked.
7. If no orders exist, row is deleted.
8. If item image exists, image file is deleted.

Business rules:

- Delete must be POST only.
- Delete must pass CSRF validation.
- Menu items with order history cannot be deleted.

Why deletion is blocked when orders exist:

- Orders are business records.
- If an item is deleted, old orders would lose historical meaning.
- The project schema now uses `ON DELETE RESTRICT` for menu items referenced by orders.
- Admins should mark ordered items unavailable instead of deleting them.

## 3.9 Admin PDF Menu Upload Flow

File responsible:

- `admin/upload_menu_pdf.php`

Business purpose:

- Allow admins to upload a downloadable PDF version of the full menu.

Flow:

1. Admin opens Upload PDF.
2. Admin selects a PDF.
3. System validates CSRF token.
4. System validates upload status.
5. System validates file size.
6. System validates MIME type is PDF.
7. System validates extension is `.pdf`.
8. System replaces existing `uploads/pdfs/menu.pdf`.
9. System shows success message and link to current PDF.

Business rules:

- PDF upload is admin-only.
- PDF upload must pass CSRF validation.
- Only PDF files are accepted.
- Only one active PDF exists at a time.

Why fixed filename is acceptable:

- The database is required to have only 3 core tables.
- A separate settings table would violate the strict 3-table requirement.
- Fixed `uploads/pdfs/menu.pdf` keeps implementation simple and demo-friendly.

## 3.10 Admin Order Processing Flow

Files responsible:

- `admin/orders/index.php`
- `admin/orders/view.php`

Business purpose:

- Allow admins to inspect all orders and update their status.

Flow:

1. Admin opens All Orders.
2. System lists orders from all users.
3. Admin opens one order detail page.
4. System shows customer, item, quantity, total price, status, notes, and date.
5. Admin selects a new status.
6. System validates CSRF token before update.
7. System validates status is one of:
   - `pending`
   - `confirmed`
   - `cancelled`
8. System updates order status.

Business rules:

- Only admins can view all orders.
- Status value must be whitelisted.
- Status update must pass CSRF validation.
- Users cannot update order status.

Why status whitelist matters:

- The database column is an ENUM.
- Application validation prevents invalid values before database update.
- It avoids confusing or unsafe states such as `done`, `paid`, or injected values.

## 4. Key Business Rules Summary

### Authentication Rules

- Registered users log in by email and password.
- Passwords are hashed using `password_hash()`.
- Login checks use `password_verify()`.
- Sessions store `user_id`, `role`, and `user_name`.
- Remember-me tokens are random and hashed in the database.

### Authorization Rules

- Guest users can only access public pages and auth pages.
- Users can access user pages only.
- Admins can access admin pages only.
- Admins are redirected away from user order placement.
- Users are redirected away from admin pages.

### Menu Rules

- Admins manage menu items.
- Users see only available items.
- Price can be `0.00`.
- Price cannot be negative.
- Deleting items with orders is blocked.

### Order Rules

- Users create orders.
- Admins process orders.
- Order total is calculated server-side.
- User cannot control `user_id`, `total_price`, or `status`.
- Users see only their own orders.
- Admins see all orders.

### File Upload Rules

- Menu item images must be JPG or PNG.
- PDF menu must be PDF only.
- File sizes are limited.
- Uploaded PHP files are blocked by validation and `.htaccess`.

### Security Rules

- All SQL uses MySQLi OO prepared statements.
- All state-changing POST forms use CSRF tokens.
- Output is escaped with `htmlspecialchars()`.
- Sensitive folders are protected by `.htaccess`.
- Local execution only; no deployment configuration is included.

## 5. Defense Talking Points

### What is the main goal of the system?

The main goal is to demonstrate a complete restaurant ordering workflow using Core PHP and MySQL. It includes authentication, authorization, CRUD, file upload validation, order placement, order history, admin order management, and security protections.

### Why are there only 3 tables?

The project specification requires exactly 3 core tables:

- `users`
- `menu_items`
- `orders`

The design keeps all required features inside these tables. For example, the PDF menu is stored as a fixed file path instead of using a fourth settings table.

### Why can menu items be hidden instead of deleted?

Deleting a menu item that has orders would damage historical order records. The safer business rule is:

- If an item has no orders, it can be deleted.
- If an item has orders, deletion is blocked and the admin should mark it unavailable.

### Why does the user not send the price?

Prices from the browser cannot be trusted. A user could edit HTML or JavaScript and submit a fake price. The system always fetches the current price from the database and calculates the total on the server.

### Why is CSRF important here?

CSRF prevents another website from forcing a logged-in user or admin to submit unwanted actions, such as creating items, placing orders, uploading PDFs, deleting items, or changing order status.

### Why is output escaping important?

Users and admins can enter text such as descriptions or notes. If the system prints that text directly, malicious JavaScript could run in the browser. `htmlspecialchars()` converts dangerous characters into safe text.


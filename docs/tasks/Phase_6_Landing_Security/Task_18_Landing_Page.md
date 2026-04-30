# Task 18 — Public Landing Page

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Abd El-Rahman Yasser                |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 6 — Landing Page & Security   |
| **Status**         | Pending                             |
| **Depends On**     | All previous tasks                  |
| **Blocks**         | Nothing — this is near the end      |

---

## Objective
Build the public root landing page at `index.php`. This is the first page any visitor sees. It requires no login. It showcases the restaurant, displays a preview of available menu items, and directs visitors to login or register.

---

## Deliverable
`index.php` (in the project root)

---

## Page Requirements

### No Login Required
- Do NOT call `requireLogin()` — this page is fully public.
- If the user IS logged in, show a personalized greeting and a link to their dashboard instead of the login/register buttons.

### Hero Section
- Full-width `.hero` section with:
  - Restaurant name as `<h1>`: **"The Restaurant"**
  - Tagline: *"A curated dining experience."*
  - Two CTA buttons side by side:
    - "View Full Menu" → scrolls to the menu section (anchor `#menu`)
    - "Login / Register" → `auth/login.php` (hidden if already logged in, replaced with "Go to Dashboard")

### Menu Preview Section (`id="menu"`)
- Section heading: "Our Menu"
- Fetch up to **6 available items** from `menu_items` using:
  ```sql
  SELECT * FROM menu_items WHERE is_available = 1 ORDER BY created_at DESC LIMIT 6
  ```
  Use a MySQLi OO prepared statement.
- Display in `.menu-grid` with `.menu-card` components (same as `user/menu.php`)
- Below the grid: "See Full Menu" button → `auth/login.php` (or `user/menu.php` if logged in)

### PDF Download Section
- If `uploads/pdfs/menu.pdf` exists, show a section: "Download our full menu as a PDF" with a download link.

### Footer
- Standard footer via `includes/footer.php`

### CSS Additions for Hero (add to `style.css` if not already present)
- `.hero`: padding `80px 0`, text-align center, border-bottom `1px solid #000`
- `.hero h1`: font-size `3rem`, margin-bottom `16px`
- `.hero p`: font-size `1.2rem`, color `#333333`, margin-bottom `32px`
- `.hero .btn`: margin `0 8px`

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] Page is publicly accessible without login
- [ ] Logged-in users see dashboard link instead of login/register buttons
- [ ] Menu preview shows max 6 items using MySQLi OO prepared statement
- [ ] Hero section renders cleanly with monochrome styling
- [ ] PDF download link shown conditionally
- [ ] Page uses `includes/header.php` and `includes/footer.php`
- [ ] Anchor scroll to `#menu` works

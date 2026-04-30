# Task 3 — Global Assets (CSS & JS)

## Assignment

| Field              | Detail                              |
|--------------------|-------------------------------------|
| **Assigned To**    | Habiba                              |
| **Reviewed By**    | Belal Moustafa                      |
| **Phase**          | Phase 2 — Project Bootstrap         |
| **Status**         | Pending                             |
| **Depends On**     | Task 2 (directories must exist)     |
| **Blocks**         | Task 4 (header.php links to style.css) |

---

## Objective
Build the complete visual design system for the entire application in a single CSS file, and write the JavaScript utility file for client-side form validation and UI interactions. Every page in the project will inherit from these two files — they must be thorough and production-quality.

---

## Strict Rules Reminder
- **Monochrome UI:** The design is strictly **black and white**. The only permitted colors are:
  - `#000000` — Black (primary text, borders, buttons)
  - `#ffffff` — White (backgrounds, button text)
  - `#f5f5f5` — Off-white / light grey (subtle backgrounds, alternating table rows)
  - `#333333` — Dark grey (secondary text, muted labels)
  - `#cccccc` — Light grey (borders, dividers, disabled states)
  - `#ff4444` — **Exception only:** error/danger states (delete buttons, error messages)
  - `#28a745` — **Exception only:** success states (success messages only)
- **No frameworks:** No Bootstrap, Tailwind, or any external CSS library. Pure CSS only.
- **No external fonts from CDN** unless it is a system-safe font stack.
- **Subtle animations only:** Transitions on hover/focus. No bouncing, flashing, or distracting effects.

---

## Deliverables

### 1. `assets/css/style.css`

Must cover the following sections (use CSS comments to separate them):

#### Section 1 — CSS Reset & Base
- Box-sizing reset (`*, *::before, *::after`)
- Remove default margin/padding
- Set base font: `font-family: 'Georgia', serif` for headings, `'Helvetica Neue', Arial, sans-serif` for body
- Base font size: `16px`, line-height: `1.6`
- Background: `#ffffff`, Color: `#000000`

#### Section 2 — Typography
- `h1` through `h4` styles with appropriate sizes and weights
- Paragraph spacing
- `.text-muted` utility class (color: `#333333`)
- `.text-danger` utility class (color: `#ff4444`)
- `.text-success` utility class (color: `#28a745`)

#### Section 3 — Layout & Container
- `.container` class: max-width `960px`, centered with `margin: 0 auto`, padding `0 20px`
- `.page-wrapper`: min-height `100vh`, display flex, flex-direction column
- `.main-content`: flex `1` (pushes footer to bottom)

#### Section 4 — Navigation / Header
- `.navbar`: full-width, black background, white text, padding `16px 0`
- `.navbar .container`: flex layout, space-between alignment
- `.navbar-brand`: white text, font-size `1.4rem`, font-weight bold, no underline
- `.navbar-nav`: flex list, no bullets, gap `24px`
- `.navbar-nav a`: white text, no underline, subtle underline on hover with `transition: 0.2s`
- `.navbar-nav a.active`: underline always visible

#### Section 5 — Buttons
- `.btn`: base button — display inline-block, padding `10px 24px`, border `2px solid #000`, cursor pointer, font-size `0.95rem`, transition `0.2s`, no border-radius (sharp corners for minimalist look)
- `.btn-primary`: black background, white text — inverts on hover (white bg, black text)
- `.btn-secondary`: white background, black border, black text — inverts on hover
- `.btn-danger`: `#ff4444` border and text, white background — inverts on hover
- `.btn-sm`: smaller padding variant `6px 14px`, font-size `0.85rem`

#### Section 6 — Forms
- `.form-group`: margin-bottom `20px`
- `label`: display block, font-weight bold, margin-bottom `6px`, font-size `0.9rem`
- `input[type=text]`, `input[type=email]`, `input[type=password]`, `input[type=number]`, `select`, `textarea`: full width, border `1px solid #000`, padding `10px 12px`, font-size `1rem`, outline none, transition border-color `0.2s`
- Focus state: border `2px solid #000`, subtle box-shadow `0 0 0 3px rgba(0,0,0,0.08)`
- `.form-error`: small red error text below a field, font-size `0.82rem`, color `#ff4444`
- `.form-card`: centered form container, max-width `480px`, margin `60px auto`, padding `40px`, border `1px solid #000`

#### Section 7 — Tables
- `.table`: full width, border-collapse collapse
- `th`: black background, white text, padding `12px 16px`, text-align left, font-size `0.9rem`, text-transform uppercase, letter-spacing `0.05em`
- `td`: padding `12px 16px`, border-bottom `1px solid #cccccc`
- `tr:nth-child(even)`: background `#f5f5f5`
- `tr:hover`: background `#eeeeee`, transition `0.15s`

#### Section 8 — Cards / Panels
- `.card`: border `1px solid #000`, padding `24px`, margin-bottom `24px`
- `.card-title`: font-size `1.1rem`, font-weight bold, margin-bottom `12px`, border-bottom `1px solid #000`, padding-bottom `8px`
- `.stats-grid`: CSS grid, `repeat(auto-fit, minmax(200px, 1fr))`, gap `20px`
- `.stat-card`: border `1px solid #000`, padding `24px`, text-align center
- `.stat-number`: font-size `2.5rem`, font-weight bold, display block
- `.stat-label`: font-size `0.85rem`, color `#333333`, text-transform uppercase, letter-spacing `0.08em`

#### Section 9 — Menu Item Cards (for user-facing menu)
- `.menu-grid`: CSS grid, `repeat(auto-fill, minmax(260px, 1fr))`, gap `24px`
- `.menu-card`: border `1px solid #000`, overflow hidden
- `.menu-card img`: width `100%`, height `200px`, object-fit cover, display block, filter `grayscale(100%)` — keeps monochrome theme
- `.menu-card-body`: padding `16px`
- `.menu-card-title`: font-size `1.05rem`, font-weight bold, margin-bottom `6px`
- `.menu-card-price`: font-size `1.1rem`, font-weight bold
- `.menu-card-category`: font-size `0.8rem`, color `#333333`, text-transform uppercase, letter-spacing `0.06em`, margin-bottom `8px`

#### Section 10 — Alerts / Flash Messages
- `.alert`: padding `14px 20px`, border `1px solid`, margin-bottom `20px`, font-size `0.95rem`
- `.alert-success`: border-color `#28a745`, background `#f0fff4`, color `#28a745`
- `.alert-danger`: border-color `#ff4444`, background `#fff5f5`, color `#ff4444`
- `.alert-info`: border-color `#000`, background `#f5f5f5`, color `#000`

#### Section 11 — Footer
- `.footer`: border-top `1px solid #000`, padding `24px 0`, text-align center, font-size `0.85rem`, color `#333333`, margin-top auto

#### Section 12 — Animations
- `@keyframes fadeIn`: opacity 0 → 1, translateY 10px → 0, duration `0.3s ease`
- Apply `.fade-in` class to `.form-card`, `.card`, `.alert` on page load
- `@keyframes slideDown`: for navbar on mobile (optional)
- Hover transitions on all interactive elements must use `transition: all 0.2s ease`

#### Section 13 — Utility Classes
- `.mt-1` through `.mt-4` (margin-top: 8px, 16px, 24px, 32px)
- `.mb-1` through `.mb-4` (margin-bottom: 8px, 16px, 24px, 32px)
- `.text-center`, `.text-right`, `.text-left`
- `.d-flex`, `.justify-between`, `.align-center`
- `.w-100` (width: 100%)
- `.img-thumbnail`: max-width `80px`, height `60px`, object-fit cover, border `1px solid #000`

---

### 2. `assets/js/main.js`

Must cover the following:

#### Form Validation
- Function `validateRequired(fieldId, errorId)` — checks if a field is empty, shows inline error
- Function `validateEmail(fieldId, errorId)` — validates email format with regex
- Function `validateMinLength(fieldId, errorId, min)` — checks minimum character length
- Function `validateFileType(fieldId, errorId, allowedTypes)` — checks file extension before upload
- Function `validateFileSize(fieldId, errorId, maxSizeMB)` — checks file size before upload
- Attach validation to the registration form and login form on `DOMContentLoaded`

#### UI Helpers
- Auto-dismiss `.alert` elements after 4 seconds with a fade-out effect
- Confirm dialog on all `.btn-danger` delete buttons: `"Are you sure you want to delete this item? This action cannot be undone."`
- Add `.fade-in` class to `.card` and `.form-card` elements on page load

---

## Step-by-Step Instructions for Habiba

1. Navigate to `assets/css/` and create `style.css`.
2. Build each section in order, using `/* === SECTION NAME === */` comment headers.
3. Navigate to `assets/js/` and create `main.js`.
4. Test the CSS by opening a plain HTML file that uses the classes and verify the monochrome look.
5. Test the JS validation functions in the browser console.
6. Hand off to Belal for review.

---

## Acceptance Criteria (Reviewed by Belal Moustafa)
- [ ] All 13 CSS sections are present and correctly implemented
- [ ] Zero use of color outside the approved monochrome palette (except the two exceptions)
- [ ] All images rendered through `.menu-card img` are grayscale via CSS filter
- [ ] All buttons have hover state transitions
- [ ] Form inputs have visible focus states
- [ ] JS validation functions are all present and named correctly
- [ ] Delete confirmation dialog is wired to `.btn-danger` buttons
- [ ] Alert auto-dismiss works after 4 seconds
- [ ] No external CSS or JS libraries imported

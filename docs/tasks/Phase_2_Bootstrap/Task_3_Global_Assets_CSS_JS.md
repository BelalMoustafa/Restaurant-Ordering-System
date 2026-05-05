# Task 3 - Global Assets (CSS and JS)

## Assignment

| Field | Detail |
|-------|--------|
| **Assigned To** | Hamza |
| **Reviewed By** | Belal Moustafa |
| **Phase** | Phase 2 - Project Bootstrap |
| **Status** | Completed |
| **Depends On** | Task 2 (directories must exist) |
| **Blocks** | Task 4 (header.php links to style.css and footer.php loads main.js) |

---

## Objective

Build the global visual design system and JavaScript utility layer for the full Restaurant Ordering System.

Every page inherits from these assets, so the files must be consistent, reusable, and aligned with the strict monochrome design requirement.

---

## Deliverables

```text
assets/css/style.css
assets/js/main.js
```

---

## Strict Rules Reminder

- No Bootstrap.
- No Tailwind.
- No external frontend framework.
- No CDN dependencies.
- UI must remain monochrome.
- Use clean whitespace, borders, contrast, and typography instead of flashy colors.
- JavaScript may improve user experience, but PHP remains responsible for security validation.

---

## CSS Requirements

`assets/css/style.css` must cover:

- Reset and base styles.
- Typography.
- Layout containers.
- Navigation/header.
- Buttons.
- Forms.
- Tables.
- Cards and panels.
- Menu item cards.
- Alerts and flash messages.
- Footer.
- Utility classes.
- Responsive behavior.

Key design requirements:

- Body background must be white.
- Main text must be black.
- Buttons must use black, white, and gray tones.
- Forms must have visible borders and focus states.
- Tables must be readable on admin pages.
- Uploaded menu images should be displayed in grayscale.
- Mobile layouts must stack cleanly.

---

## JavaScript Requirements

`assets/js/main.js` must provide:

- Required field validation.
- Email validation.
- Minimum length validation.
- File type validation.
- File size validation.
- Registration form helper validation.
- Login form helper validation.
- Menu item form helper validation.
- PDF upload helper validation.
- Order form helper validation.
- Flash message auto-dismiss.
- Delete confirmation for danger actions.
- Image preview for uploads.
- Live order price preview.

Important:

- JavaScript validation is only for user experience.
- All security-critical validation must still happen in PHP.

---

## Step-by-Step Instructions for Hamza

1. Confirm that Task 2 created the `assets/css/` and `assets/js/` directories.
2. Create `assets/css/style.css`.
3. Build the CSS from global styles to component styles.
4. Ensure the design remains monochrome across all reusable classes.
5. Create `assets/js/main.js`.
6. Add validation helpers that safely check whether DOM elements exist before using them.
7. Add form-specific validation for auth, menu, upload, and order pages.
8. Add UI helpers such as alert auto-dismiss and delete confirmation.
9. Test the assets from pages that include `includes/header.php` and `includes/footer.php`.
10. Hand the completed assets to Belal Moustafa for review.

---

## Acceptance Criteria

- [x] `assets/css/style.css` exists.
- [x] `assets/js/main.js` exists.
- [x] CSS follows the monochrome design requirement.
- [x] Buttons, forms, tables, cards, and alerts are styled consistently.
- [x] Menu images are displayed in grayscale.
- [x] Responsive behavior works on smaller screens.
- [x] JavaScript validation helpers are present.
- [x] Delete confirmation is wired to danger actions.
- [x] Flash messages auto-dismiss.
- [x] Order price preview is available on the order form.
- [x] No external CSS or JS libraries are imported.
- [x] Belal Moustafa reviewed and approved the assets.

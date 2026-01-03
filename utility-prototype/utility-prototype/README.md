# Utility Prototype Theme (v0.5)

## Install
- Upload the zip as a WordPress theme and activate it.
- Create a **Product** (Products → Add New) and paste config JSON into **Product Config (JSON)**.
- Create a Page and choose **Template → Product Prototype**.
- In the Page sidebar, set **Prototype Product** to the Product you created.

## What changed
- Locked configuration to an array-based schema (`options[].id`) to keep ordering explicit and make future CMS/API mapping straightforward.
- Added minimal server-side validation on save (invalid configs are rejected with a clear admin notice) and a lightweight live “valid/invalid JSON” hint.
- Moved the supplied prototype assets into the theme (`/assets/img`) so the submission works immediately on any install with no Media Library import step.
- Updated the prototype UI to match the reference layout: select-based option groups with “Select your …” placeholders, unit price + total calculation, selection summary, share actions, and a wishlist toggle.
- Added Proxima Nova (local webfont files) and updated the accent token to `#250858`.

## Notes
- No plugins required.
- Vanilla JS, no external libraries.
- Keyboard-friendly controls and sensible fallbacks (native share where available, otherwise copy link).

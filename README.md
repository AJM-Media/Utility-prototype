# **Utility Design \- Front-end Prototype**

**Real-World Front-end Task \- Configurable Product Page**

**Author:** Andrew Murray  
**Client:** Utility  
**Date:** January 2026

---

## **Overview**

This prototype was produced in response to Utility Design’s request for a real-world front-end task, focused on improving the usability and overall experience of purchasing a configurable product with multiple options.

The brief and supplied Figma artwork were used as the primary reference, with the goal of translating static design concepts into a fully functional, interactive prototype suitable for early-stage user testing.

The result is a lightweight WordPress-based prototype that closely follows the provided designs while introducing a small number of considered UX and accessibility improvements, which are outlined below.

---

## **Approach**

* Built as a **custom lightweight WordPress theme**, avoiding large frameworks or third-party libraries (avoided plugins as much as possible,no woocommerce etc which I would typically use when building a full-function, product to checkout build).

* Core technologies used: **HTML, CSS, vanilla JavaScript, and PHP** (WordPress templating).

* The product configuration logic is **data-driven**, allowing options, prices, images and swatches to be defined via JSON rather than hardcoded markup.

* The prototype was developed locally using **Local (by Flywheel)** and migrated to a temporary domain for review.

The aim throughout was to balance realism (how this would scale in a real project) with simplicity, keeping the codebase readable and maintainable but prepared in a way that would support further development.

---

## **Key Features Implemented**

### **Configurable Product Logic**

* Product options (fabric, colour, leg finish, optional seat cushion) are defined via a structured JSON schema.

* Options can apply price modifiers, swap gallery images, and display visual swatches.

* Total cost updates dynamically based on selected options and quantity.

* “Add to Basket” is disabled until all required selections are made.

### **UI & UX**

* Layout and visual styling closely match the supplied Figma designs.

* Custom dropdown components were implemented to support swatches inside options while preserving keyboard accessibility.

* Visual affordances (hover states, disabled states, subtle transitions) were added to improve clarity and feedback.

* Minor layout adjustments were made to improve scanability and flow (e.g. placement of unit price before & total cost after quantity selection).

### **Accessibility**

* Semantic HTML structure throughout (e.g. `<nav>`, `<main>`, `<form>`, `<fieldset>`).

* Keyboard navigation supported across all interactive elements.

* ARIA attributes applied where appropriate (custom dropdowns, toggle buttons, dialogs).

* Accessible focus states and reduced-motion considerations included.

* A non-intrusive accessibility tip is presented via a dismissible toast modal.

### **Responsiveness**

* Fully responsive across common breakpoints.

* Layout adapts cleanly from desktop to mobile without losing functionality.

* Decorative/extra elements are suppressed on smaller screens to avoid clutter (e.g breadcrumbs, which were introduced to the desktop version to improve UX and SEO.

### **Cross-Browser Behaviour**

* Built and tested in modern evergreen browsers.

* Graceful fallback behaviour for older browsers (native selects still function if custom UI is unsupported).

---

## **Design Deviations (Intentional)**

While the Figma designs were followed closely, a small number of changes were made deliberately:

* **Disabled Add to Basket state**  
   Not explicitly shown in the designs, but added to prevent invalid submissions and guide users through required selections.

* **Total cost positioning**  
   The total cost is shown after quantity selection to better reflect the user’s mental model: choose options → choose quantity → see final total.

* **Custom dropdown implementation**  
   Native selects were visually replaced to support swatches inside options, while retaining the underlying native `<select>` for accessibility and reliability.

* **Non-slider product gallery**  
  I did notice pagination for the product images in the figma design but as there weren’t many to work with and I’d put a focus on accessibility via keyboard/tabbing etc. it seemed counter-productive to implement. If I were to implement a slider, I would likely use [swiper.js](http://swiper.js) as it’s so effective and lightweight.

Each of these changes was made to improve usability or clarity without altering the overall visual intent of the design.

---

## **File / Structure Overview**

* **style.css**  
   Core theme styles, CSS variables, typography, layout, component styling.

* **functions.php**  
   Theme setup, asset loading, custom post type registration, JSON validation, admin tooling.

* **page-product-prototype.php**  
   Main product page template, responsible for rendering the configurable product UI.

* **assets/js/main.js**  
   All front-end behaviour: pricing logic, option handling, custom dropdowns, gallery interactions, accessibility toast, sharing, and UI state.

* **assets/img/**  
   Product images, swatches, SVG icons, and decorative assets.

---

## **Challenges & Considerations**

* Balancing custom UI (swatch dropdowns) with accessibility and keyboard support.

* Ensuring pricing logic remained robust and easy to reason about as options and quantities changed.

* Keeping the codebase intentionally small and readable while still demonstrating real-world complexity.

* Migrating a locally built prototype to a temporary domain while preserving data-driven behaviour.

---

## **What I Would Do Next (With More Time)**

* Extract product configuration into reusable blocks/components.

* Add inline validation messaging for incomplete selections.

* Introduce basic analytics hooks for user testing (option changes, abandonment points).

* Expand the schema to support grouped fabrics, availability rules, and stock logic.

* Improve admin tooling for non-technical product setup (Woocom/ACF etc).

* Further explore branding and polish (animations, libraries etc).

---

## **Summary**

This prototype is intentionally not a full e-commerce implementation. It is designed to demonstrate front-end architecture, UX thinking, accessibility awareness, and code quality within the scope of early-stage prototyping and user testing.

I’ve been extremely busy lately and really enjoyed having time to work on some Development again. This was completed in half a day and if you inspect it you’ll see that a lot of extra time went into good practices that would make this site/theme much easier to expand.

I hope you appreciate the effort and would love to discuss the task/role in more detail.

Thanks,  
Andrew

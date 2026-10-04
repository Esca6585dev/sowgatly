# Admin panel redesign

**Chosen: concept A**, with a collapsible sidebar (full ↔ icon rail with tooltips, state
remembered), light/dark theme (follows the OS, switch remembered), a mobile drawer and the
gift-box logo. Screenshots: `a-light.png`, `a-light-collapsed.png`, `a-dark.png`,
`a-dark-collapsed.png`, `a-mobile-light.png`, `a-mobile-dark-menu.png`.

## Concepts

Static mockups for replacing the Metronic admin theme. Colours come from the logo
gradient (`#FF6A00 → #FF2A00`); both concepts have light and dark themes driven by CSS
variables (`data-theme` on `<html>`). Open the HTML files in a browser; the moon/sun
button switches the theme, or add `?theme=dark` to the URL.

| Concept | Idea |
|---|---|
| **A — Ýumşak** (`variant-a.html`) | Labelled light sidebar grouped by section, soft rounded cards, gradient hero KPI, orders table with status pills, sales bars and chat preview. Closest to a classic admin. |
| **B — Kontrast** (`variant-b.html`) | Dark icon rail, full-width gradient KPI banner, live order board by status (kanban), compact side panels for shop applications and chats. Denser, more "operations desk". |

Once a concept is chosen it will be rebuilt in Blade with Tailwind (Vite build already in
the project), Metronic removed from the admin views, and every admin page moved onto the
new layout.

# PROMPT-CLIENT — Sowgatly customer website and account panel

> **Ulanyş (TM):** täze Claude Code sessiýasyny `Esca6585dev/sowgatly` reposynda açyň we
> şeýle ýazyň: «`PROMPT-CLIENT.md`-ni oka we Phase 0-dan başlap ýerine ýetir, her
> fazadan soň commit we push et». Dizaýn nusgasy:
> `docs/client-design/storefront.html` (brauzerde açyň) we şol bukjadaky suratlar.
>
> **Status:** not started. This file supersedes the "Phase 1 — Customer website" section
> of `PROMPT.md`; everything else there still applies.

## Goal

Build the customer-facing web shop for Sowgatly, a Flowwow-style marketplace for
flowers and gifts in Turkmenistan: many independent shops, one storefront, catalog
filtered by city, delivery in about an hour. Customers browse, search, buy, track
orders and manage their account in **four languages: Turkmen (default), Russian,
English, Turkish**.

The clickable prototype `docs/client-design/storefront.html` is the reference for
layout, components, copy and behaviour. Match it closely; replace its sample data with
real data from the database.

## Ground rules

- Read `README.md` and `CLAUDE.md` first and follow their project rules (never change
  the JSON shape of existing `/api/*` endpoints, additive migrations only, a feature
  test for every new page or endpoint, `php artisan test` must stay green).
- Server-rendered **Blade** pages, **Tailwind CSS** through the existing Vite setup
  (`resources/css/app.css`, `resources/js/app.js`) and **Alpine.js** for small
  interactions (steppers, dropdowns, drawer, tabs). No SPA framework.
- Reuse the design tokens of the admin panel (`public/css/admin-design.css`, `:root`
  and `[data-theme=dark]` blocks) so both sides share one palette: brand gradient
  `#FF6A00 → #FF2A00`, warm neutrals, light and dark theme. Fonts: Unbounded for
  headings and prices, Manrope for body text (Google Fonts with system fallbacks).
- Logo: `public/img/logo/logo-rounded.svg` (gift box). Favicons already exist under
  `public/img/logo/`.
- Mobile first. At ≤ 640 px the header collapses to logo + cart + full-width search,
  and a fixed bottom tab bar appears (Home, Catalog, Cart, Favorites, Account), as in
  the prototype. No horizontal page scroll at any width.
- Business logic lives in the existing models and in service classes shared with the
  API controllers. Do not duplicate order, cart, OTP or review logic in web controllers;
  extract it into `app/Services/*` and call it from both.
- Web controllers in `App\Http\Controllers\Store\*`, routes in a new `routes/store.php`
  included from `routes/web.php`, route names prefixed `store.`. All pages live under
  `/{locale}`.
- Eager-load every list (`with()`), paginate catalogs, cache the home feed per city the
  same way `GET /api/home` does.

## Phase 0 — Turkish as the fourth language

1. Add `tr` to `config/languages.php` (name `Türkçe`, flag icon from the Metronic flag
   set or an inline SVG) and to the `{locale}` route constraint and `SetLocale` /
   `SetApiLocale` middleware so `/tr/...` and `Accept-Language: tr` work.
2. Create `resources/lang/tr.json` with every key that exists in `tm.json`, plus the
   Laravel validation/auth/pagination files under `resources/lang/tr/`.
3. Content columns: add nullable `name_tr` and `description_tr` to `products`, `name_tr`
   to `categories` and `regions` (additive migration). Everywhere a localized value is
   read (resources, Blade helpers, API resources), fall back `tr → en → tm` when the
   Turkish value is empty. Extend the API resources by **adding** a `tr` key to the
   existing `name`/`description` objects; do not remove or rename keys.
4. Admin product, category and region forms get the Turkish fields.
5. A `@t($model, 'name')` Blade helper (or a `localized()` model method) returns the
   value for the current locale with the fallback chain.

Acceptance: `/tr/admin/dashboard` renders in Turkish where translations exist;
`GET /api/products` with `Accept-Language: tr` still returns the old keys plus `tr`;
tests for the fallback chain.

## Phase 1 — Layout and shared components

- `resources/views/store/layouts/app.blade.php` with the header from the prototype:
  logo, **city picker** (regions of type `city`, remembered in session and a cookie,
  default Aşgabat), search box with live suggestions (debounced `GET /{locale}/search/suggest`
  returning products, categories and shops), language switcher **TM · RU · EN · TR**
  that keeps the current page, theme toggle (remembered in `localStorage`, follows the
  OS by default), favorites and cart buttons with counters, account button (avatar
  initials when signed in, "Sign in" otherwise). Footer with shop-owner link
  (`/for-shops`), contacts, legal pages.
- Blade components: `product-card` (first image, discount badge, heart, name, shop,
  rating and review count, "delivery in N min" pill from `production_time`, price with
  old price struck through, add button that turns into a quantity stepper),
  `category-tile`, `banner`, `price`, `rating`, `status-pill`, `order-tracker`,
  `empty-state`, `toast`, `bottom-tab-bar`, `pagination`.
- Toasts for every action ("Added to cart", "Saved", "Order placed № …").

## Phase 2 — Storefront pages (routes relative to `/{locale}`)

| Route | Page |
|---|---|
| `/` | Home: banner rail (big hero + two side banners from the **Banners** admin section, filtered by city), category tiles, "Delivered today" and "Popular" shelves, featured shops. Data from the same queries as `GET /api/home`. |
| `/catalog`, `/catalog/{category}` | Grid with filters: subcategory, price range, discount only, delivery today, min rating, sort (popular, price ↑↓, newest). Filters live in the query string. |
| `/search?q=` | Same grid; matches localized product names, category names and shop names. Empty state with suggestions. |
| `/product/{id}-{slug}` | Gallery with thumbnails, name, rating, delivery time, price, quantity stepper, **Add to cart** and **Buy now** (adds then opens checkout), composition, size, delivery estimate for the selected city, shop card with **Message the shop** (opens a chat thread through the existing chat service), perks row (free greeting card, photo before delivery, freshness guarantee), reviews with the three criteria, similar products. |
| `/shop/{id}-{slug}` | Shop header (logo, rating, hours, city, delivery fee, pickup), its products with the catalog filters, **Message the shop**. |
| `/favorites` | Grid of favorites. Guests keep favorites in a cookie that is merged on sign-in. |
| `/cart` | Lines grouped by shop with steppers and remove, promo code field, summary (items, discount, delivery fee per shop, total). Guest cart in session, merged into the DB cart on sign-in. |
| `/checkout` | Sign-in required. Recipient name and phone (prefilled from the profile), address picker from `user_addresses` with "add new" inline, delivery time (**as soon as possible** or a date and time slot from the shop's hours), fulfilment (delivery or pickup when the shop allows it), payment method (cash or online bank from `GET /payment-methods`), greeting-card text. Creates one order per shop through the shared order service, then redirects to the order page with a toast. |

## Phase 3 — Account panel (`/account/...`)

Side tabs on desktop, a horizontal tab strip on mobile, exactly as in the prototype:

| Tab | Content |
|---|---|
| **My orders** | Active orders (`pending`, `processing`, `delivering`) as cards with a **status tracker** (Received → Being prepared → On the way → Delivered, with the time of each step from the order's status history), courier name and ETA while delivering, items with thumbnails, total, **Cancel** while the order rules allow it, **Message the shop**. Auto-refresh every 30 s. |
| **Order history** | Completed and cancelled orders with totals, search by number or product (`?q=`), **Order again** (adds the items to the cart), **Write a review** for delivered items not yet reviewed. |
| **Order detail** `/account/orders/{number}` | Full tracker, items, recipient, address, payment, fees, chat link, review buttons. |
| **Addresses** | List with default badge, add, edit, delete, make default (`user_addresses`). |
| **Profile** | Avatar upload, name, email, phone, birthday. Changing the phone sends an OTP to the **new** number and only saves after the code is confirmed. Changing the email marks it unverified. |
| **Settings** | Language (4 buttons), theme (light/dark/system), notification switches (order status, discounts, important dates) stored on the user, delete account with an in-page confirmation step. |
| **Chats** | List of the customer's threads with unread badges and a thread view (same polling as the mobile app). |
| **Log out** | Ends the web session and returns to the home page. |

Sign-in `/login`: phone → 4-digit OTP, rate-limited like the API, `Auth::login()` on the
`web` guard (no Sanctum token). New phone numbers continue to a short name form.
Extract the OTP logic from `Api\AuthOtpController` into a service used by both.

## Phase 4 — Quality

- SEO: `<title>`, meta description, Open Graph per page, `sitemap.xml`, canonical URLs
  with `hreflang` for the four locales.
- Accessibility: every control keyboard-reachable with a visible focus ring, `aria-label`
  on icon buttons, `prefers-reduced-motion` respected.
- Performance: images lazy-loaded with width/height, responsive `srcset` when sizes
  exist, home feed cached per city, no N+1 (assert query counts in tests for home,
  catalog and orders).
- Feature tests under `tests/Feature/Store/`: home per city, catalog filters and sort,
  search in all four languages, product page, guest cart add/update/remove and merge on
  sign-in, favorites merge, OTP sign-in creates a web session, checkout creates one
  order per shop, cancel rules, reorder, address CRUD, profile update with phone OTP,
  settings, language switch keeps the page, Turkish fallback chain.
- Browser smoke test with Playwright (Chromium is preinstalled): sign in with
  `OTP_DEBUG_CODE=0000`, add to cart, check out, see the order in **My orders**,
  switch language and theme. Save screenshots to `docs/client-design/live/`.

## Done when

- All pages from the prototype exist with real data, in tm/ru/en/tr, light and dark,
  desktop and phone.
- `php artisan test` passes, the Playwright smoke test passes, Swagger regenerated for
  any new API fields.
- `README.md` documents the website routes and how to add a fifth language.
- Each phase is its own commit (English message), pushed to the working branch.

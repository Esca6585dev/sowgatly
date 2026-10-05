# Sowgatly — iş tabşyrygy (prompt)

> **Nähili ulanmaly:** Täze Claude Code sessiýasyny açyp, iki reponyň-da (`sowgatly`,
> `sowgatly-app-react-native`) `main` branchyny saýlaň we şeýle ýazyň:
>
> `PROMPT.md faýlyny oka we ondaky işleri Phase 0-dan başlap tertip bilen ýerine ýetir.
> Her Phase-den soň commit et we main-a push et.`
>
> Prompt iňlisçe ýazylan, sebäbi model tehniki tabşyrygy iňlisçe has takyk ýerine ýetirýär.
> Islän ýeriňizi üýtgedip bilersiňiz. Bir sessiýada hemmesi gutarmasa, indiki sessiýada
> `PROMPT.md-de galan işleri dowam et` diýseňiz ýeterlik.

---

## Context

Two repositories make up Sowgatly, a flower and gift marketplace for Turkmenistan
(think **flowwow.com**: many independent shops, one storefront, city-based catalog,
fast delivery, reviews):

- `Esca6585dev/sowgatly` — Laravel 10 backend. Already contains a finished REST API
  (`routes/api.php`, Sanctum, OTP login by phone), an admin panel
  (`/{locale}/admin`, Blade, Spatie permissions) and all data: regions (cities),
  shops, categories (`name_tm/name_ru/name_en`), products with images, attributes,
  compositions, brands, carts, orders with delivery fields and status transitions,
  favorites, product reviews, user addresses, in-app notifications.
  Read `README.md` first.
- `Esca6585dev/sowgatly-app-react-native` — Expo 49 mobile client that uses the same
  API. Read its `README.md` first.

The public website today is a placeholder (`resources/views/user-panel/main-page.blade.php`
shows three logos). The job is to turn it into a real customer-facing store built
with Blade on top of the existing models, remove leftovers from unrelated projects,
refresh Swagger, and produce an Android APK.

**Hard rules**

- Do not change the JSON shape of any existing `/api/*` endpoint; the mobile app depends
  on it. Adding new endpoints is fine.
- Reuse the existing Eloquent models, resources and business rules (ownership checks,
  `Order::canTransitionTo`, `TurkmenistanPhoneNumber` rule, OTP services). Do not fork
  the logic into a second copy for the web.
- Keep the admin panel working.
- Every phase ends with: `php artisan test` green (MySQL database `sowgatly`),
  `php artisan route:list` free of errors, a commit, and a push to `main`.
- Write code comments and commit messages in English. User-visible text goes through
  Laravel localization (`lang/tm`, `lang/ru`, `lang/en`), Turkmen first.
- Never commit `.env`, `vendor/`, `node_modules/`, keystores or API keys.

---

## Phase 0 — Remove leftovers from other projects

Delete, do not comment out. Make sure nothing else references what you remove
(`grep` before deleting, run `composer dump-autoload` and `php artisan route:list` after).

Backend (`sowgatly`):

1. ~~**Chess API**~~ — done, the block is gone from `routes/api.php`.
2. ~~**Resume / letterhead generator**~~ — done: routes, `HomeController`, the resume page and the `Application/Letterhead/Section/Standart` models are gone.
3. **Web password auth for customers** — partly done: `Auth::routes()`, the e-mail verification routes, the laravel/ui controllers and their views are removed; `/login` now redirects to the admin login. Still to remove: `routes/auth.php`, the Breeze/Fortify controllers under `App\Http\Controllers\Auth` (keep `AdminLoginController`/`AdminLogoutController`), the remaining `resources/views/auth/*` except `admin-login`, and `FortifyServiceProvider` if nothing uses it.
4. Remove the `laravel/telescope` dependency and its migration unless `.env.example`
   documents it; it is not used.
5. `DatabaseSeeder` lists `ProductSeeder` twice; keep one.
6. Remove `public/base64.txt` and `public/docs/api-docs.json` (a stale copy of the
   Swagger spec; the live one is served from `storage/api-docs`).
7. ~~**Missing `messages` table**~~ — done (`2026_10_04_000007_create_messages_table`).
8. ~~**Orphan `Text` model**~~ — done.
9. ~~Metronic~~ — done: the admin panel was rebuilt on its own design system (`public/admin/`, `docs/admin-design/`) and `public/metronic-template/` was deleted.
10. ~~Auth exception handling~~ — done: API requests get a JSON 401, browser requests to the admin panel are redirected to the admin login.
11. Update `README.md` so it no longer mentions anything you removed.

Mobile (`sowgatly-app-react-native`):

11. Delete unused components: `Details`, `IntroSliderScreen`, `ModalComponent`, `Piece`,
   `PizzaTranslator`, `Reorder`, `Square` (verify with grep that nothing imports them).
12. `ForgotPasswordScreen`, `NewPasswordScreen` and `ConfirmEmailScreen` are UI mock-ups
   with no API behind them. Remove them and their navigator entries; the OTP flow replaces
   them.
13. The `Chat` tab is static UI with no backend. Remove it from `AppNavigator` and delete
    the component.

Commit: `Remove chess, resume/letterhead and password-auth leftovers`.

---

## Phase 1 — Customer website in Blade (Flowwow-style)

Build the storefront as server-rendered Blade pages under the existing
`/{locale}` prefix (`tm`, `ru`, `en`; default `tm`, chosen by IP on `/` as today).
Mobile-first, responsive, fast. Use **Tailwind CSS** via the existing Vite setup
(`resources/css/app.css`, `resources/js/app.js`); add Alpine.js for small interactions
(dropdowns, quantity steppers, city modal). No SPA framework.

### 1.1 Layout and shared pieces

- `resources/views/store/layouts/app.blade.php`: header with logo, **city selector**
  (regions of `type` city, remembered in session and cookie), search box, category
  menu, favorites, cart badge with item count, profile/login button, language switcher.
  Footer with shop-owner link, contacts, legal pages.
- Components: `product-card` (first image, name, price with discount strike-through,
  shop name, rating stars from reviews, "delivery today" badge when `production_time`
  allows), `shop-card`, `rating-stars`, `price`, `empty-state`, `flash` alerts,
  `pagination`.
- Store controllers live in `App\Http\Controllers\Store\*`; routes in a new
  `routes/store.php` included from `routes/web.php`. Names prefixed `store.`.

### 1.2 Pages (routes relative to `/{locale}`)

| Route | Page |
|---|---|
| `/` | Home: hero banner, category chips, "Popular in {city}", "Delivery today", featured shops, how-it-works strip. All product queries filtered by the selected city (`shop.region_id`). |
| `/catalog`, `/catalog/{category}` | Product grid with filters: subcategory, price range, discount only, delivery today, sort (popular, price, newest). Paginated. Query string drives the filters so URLs are shareable. |
| `/search?q=` | Same grid, searching `products.name` and shop name. |
| `/product/{id}-{slug}` | Gallery, price, discount, composition and attributes, sizes, production time, add-to-cart with quantity, shop block (name, rating, working hours from `mon_fri_open` etc., link), reviews list and review form (signed-in buyers only, one per order), similar products. |
| `/shop/{id}-{slug}` | Shop header (image, rating, hours, city), its products with the same filters. |
| `/favorites` | Guest favorites in a cookie, merged into the account on login. |
| `/cart` | Items grouped by shop, quantity steppers, remove, totals per shop and overall. Guests keep a session cart that is merged into the DB cart on login. |
| `/checkout` | Requires login. Per shop: recipient name and phone, delivery address (choose from `user_addresses` or add new), delivery type `asap` or `scheduled` with date and time slot, note/card text. Creates one `Order` per shop with the existing order logic, decrements nothing (stock is not tracked), then redirects to `/orders/{id}?placed=1`. |
| `/orders`, `/orders/{id}` | Order history and detail with status timeline (`pending → processing → completed`, `cancelled`), items with images, cancel button while `pending`. |
| `/account` | Profile (name, email), addresses CRUD, notifications list with mark-all-read, logout. |
| `/login`, `/register` | OTP flow: phone → code. Two-step forms, rate limited like the API. On success create the **web session** (`Auth::login`) for the `web` guard; do not issue Sanctum tokens here. Reuse `AuthOtpController` logic by extracting it into a service class both controllers call. |
| `/for-shops` | Static page explaining how a shop joins, with a contact form that stores into `messages` (admin already lists them). |
| `/about`, `/delivery`, `/privacy` | Static localized pages. |

### 1.3 Behaviour details

- City is required for prices and availability; when no city is chosen show the
  city modal on first visit (default Ashgabat).
- Product URLs include a slug built from the localized name but resolve by id.
- SEO: `<title>`, meta description, Open Graph tags per page, `sitemap.xml` route,
  `robots.txt` already exists.
- Images come from the `images` table (`url`); fall back to a placeholder.
- All lists are eager-loaded (`with()`) to avoid N+1; add indexes if a query needs one.
- Flash messages after every POST. Validation errors inline.
- Add feature tests under `tests/Feature/Store/` for: home renders, catalog filters,
  product page, guest cart add/update/remove, OTP login creates a session, checkout
  creates orders per shop, cancel only while pending.

Commit per page group; push after the phase.

---

## Phase 2 — Refresh Swagger

`storage/api-docs/api-docs.json` was generated in February 2025 and is stale.

1. Add `@OA` annotations to every API controller method that lacks them:
   `ProductReviewController`, `FavoriteController`, `UserAddressController`,
   `OrderController::cancel`, `ShopOrderController`, `UserNotificationController`,
   `UserController::updateMe`. Remove annotations for endpoints that no longer exist
   (`/api/users`, `/api/users/{id}`).
2. Run `php artisan l5-swagger:generate` and commit the regenerated JSON.
3. Verify every route in `php artisan route:list --path=api` appears in the spec.

Commit: `Bring Swagger in line with the current API`.

---

## Phase 3 — Android APK

In `sowgatly-app-react-native`:

1. Set `EXPO_PUBLIC_API_URL=https://sowgatly.app` for production via `eas.json`
   build profiles (`development`, `preview`, `production`); keep local defaults for dev.
2. Add `eas.json` with a `preview` profile that outputs an **APK**
   (`"android": {"buildType": "apk"}`) and a `production` profile for AAB.
3. Make sure `app.json` has the right `android.package` (`com.sowgatly.app`), icon,
   splash and bump `versionCode`.
4. Document in the README how to build: `npx eas build -p android --profile preview`
   (cloud) and the local alternative `npx expo prebuild && cd android && ./gradlew assembleRelease`.
5. If a cloud build is not possible from the session, run the local Gradle build, fix
   anything that fails, and report the exact command and output. Never commit a release
   keystore; document where it must be stored.

Commit: `Add EAS build profiles and APK instructions`.

---

## Phase 4 — Close the gaps between the Figma design and the backend

> **Status: backend and admin parts are DONE** (see `README.md` → "API overview" and
> "Order lifecycle"). The column and endpoint names follow `PROMPT-API.md`
> (`fulfillment`, `payment_bank`, `waitlist_items`, `chat_threads`, `shop_applications`),
> which is the detailed spec for the Flutter client. What remains here is the
> **mobile wiring** (4.5) and the website pages that use the same data (Phase 1).

Done in the backend:

- Checkout: `fulfillment` delivery/pickup, `shops.delivery_fee` (default 20 TMT,
  editable per shop in the admin form), `shops.pickup_available`, `items_total`,
  `delivery_fee`, `payment_method` cash/online, `payment_bank` from
  `GET /api/payment-methods` (`config/payments.php`), `payment_status`, `paid_at`,
  `recipient_name`. Online payment is a stub (order stays `unpaid`) until a bank gateway exists.
- Orders: `delivering` status, `number` accessor (`0000001`), `GET /api/orders?q=`,
  admin **Orders** section with filters, detail page, status and payment changes.
- Profile: `birth_date`, avatar upload (multipart or base64) via `PUT`/`POST /api/users/me`,
  `DELETE /api/users/me/image`.
- Waiting list ("Лист ожидания"): `/api/me/waitlist`, `product_available` notification
  when stock or status comes back.
- Chats: customer ↔ shop threads (`/api/me/chats*`, `/api/shop/chats*`), unread counters,
  `chat_message` notifications, read-only admin **Chats** section. Polling for now.
- "Разместить свой магазин": `POST /api/shop-applications` (guests too), admin
  **Shop applications** section with status + note, `shop_application` notification.
- `shops.status` pending/approved/rejected with `Shop::approved()` scope (admin form).
- `messages` table created so the admin "Messages" page works; chess routes removed.

### 4.5 Mobile app (still to do)

Wire the new API into the Expo app: pickup/delivery toggle, delivery fee and total,
payment method picker, `delivering` status label, order search, avatar upload, shop
application form, Chats tab backed by `/api/me/chats`, waiting list screen.

---

## Phase 5 — Final pass

- Run both READMEs against reality and fix anything that drifted.
- `php artisan test`, `npm run build` (backend assets) and `npx expo export --platform web`
  (mobile) must succeed.
- Summarize in the final message: what was built, what was removed, how to run the
  website, where the APK/build instructions are, and anything left undone with the reason.

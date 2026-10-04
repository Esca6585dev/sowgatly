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

1. **Chess API**: the `Route::prefix('chess')` block in `routes/api.php` points at
   `App\Http\Controllers\GameController`, which does not exist. Remove the block.
2. **Resume / letterhead generator**: remove the `HomeController` routes
   (`/{locale}/home`, `/{locale}/email`, `/{locale}/profile/*`), `HomeController.php`,
   the models `Application`, `Letterhead`, `Section`, `Standart` (no migrations exist
   for them), the `/Esca6585` resume route and `resume()` action, `resources/views/resume.blade.php`,
   `resources/views/excel.blade.php`, `resources/views/home.blade.php`,
   `resources/views/welcome.blade.php`, `public/resume/` and `public/docs/` if they
   belong to the resume feature, and the `/otp` test route plus `send()` in
   `UserControllers/UserController.php`.
3. **Web password auth for customers**: customers sign in with OTP, so remove both
   `Auth::routes(...)` calls in `routes/web.php` and the unused `routes/auth.php`
   scaffolding (Fortify/Breeze controllers under `App\Http\Controllers\Auth` that are
   not used by the admin login). Keep `AdminLoginController` and `AdminLogoutController`.
   Keep the `admins` guard and everything under `routes/admin-routes/`.
4. Remove the `laravel/telescope` dependency and its migration unless `.env.example`
   documents it; it is not used.
5. `DatabaseSeeder` lists `ProductSeeder` twice; keep one.
6. Remove `public/base64.txt` and `public/docs/api-docs.json` (a stale copy of the
   Swagger spec; the live one is served from `storage/api-docs`).
7. **Missing `messages` table**: `App\Models\Message` and the admin `MessageController`
   exist but no migration creates the table, so the admin "Messages" page crashes.
   Add a migration (`id, username, email, messages, user_id nullable, timestamps`) and
   confirm the admin CRUD works. The website contact form in Phase 1 writes here.
8. **Orphan `Text` model**: `App\Models\Text` and `resources/views/admin-panel/text/`
   have no route or controller. Remove them.
9. `public/metronic-template/` (206 MB, 5 841 files) is the admin theme. Keep only the
   CSS/JS/font/image files the admin Blade views actually reference (grep the 44 views
   that mention it) and delete the rest of the template (demo pages, docs, unused
   plugins).
10. Update `README.md` so it no longer mentions anything you removed.

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

The Figma file (`figma.com/design/amTVuTZbUOhu2avAmuXa8I`) shows features the API,
database and admin panel do not have yet. Everything below is **additive**: new
columns get defaults, new fields in JSON responses are added next to the existing ones,
existing endpoints keep working for the current mobile app.

### 4.1 Checkout: pickup, delivery fee, payment

- `orders.fulfillment_type` enum `delivery|pickup` (default `delivery`). For `pickup`
  no address is required and the delivery fee is 0.
- `shops.delivery_fee` decimal, default 20 TMT, editable in the admin shop form.
  `orders.subtotal`, `orders.delivery_fee`, `orders.total_amount = subtotal + delivery_fee`.
- Payment: `orders.payment_method` enum `cash|online`, `orders.payment_provider`
  string nullable (bank code, e.g. `rysgal`), `orders.payment_status` enum
  `unpaid|paid|failed|refunded` (default `unpaid`), `orders.paid_at`.
  `GET /api/payment-methods` returns the configured providers from `config/payments.php`
  (Rysgal and the other banks shown in the design). The real bank gateway is not
  available yet: implement a `PaymentGateway` interface with a `ManualGateway` that
  leaves the order `unpaid`, so the bank driver can be dropped in later without touching
  the controllers.
- `POST /api/orders` accepts the new fields; the response includes them. Validation:
  `fulfillment_type`, `payment_method`, `payment_provider` required when `online`.

### 4.2 Orders

- Add the `delivering` status: `pending → processing → delivering → completed`,
  `cancelled` from pending/processing. Update `Order::TRANSITIONS`, the shop-side
  status endpoint, notifications and the admin panel.
- `GET /api/orders?q=` searches by order id and product name.
- Admin panel: an **Orders** section (list with status/payment filters, detail page,
  status change). It does not exist today.

### 4.3 Profile

- Avatar upload: `POST /api/users/me/avatar` (multipart `image`, reuse the
  `ImageOrBase64` rule and the storage used for product images); `UserResource`
  returns `image_url`. `DELETE /api/users/me/avatar` removes it.
- "Разместить свой магазин": shop moderation. `shops.status` enum
  `pending|approved|rejected` (existing rows `approved`, shops created through
  `POST /api/shops` start as `pending`). Only approved shops and their products are
  listed publicly; the owner always sees their own. Admin shop form gets the status
  field and approve/reject actions; the owner gets an in-app notification on decision.
- "Лист ожидания" (waiting list) is not defined. **Ask the product owner** what it
  means before building it (wishlist? notify-when-available? pending orders?).

### 4.4 Chat with a manager

- Tables `conversations` (`user_id`, `admin_id` nullable, `last_message_at`,
  `user_unread`, `admin_unread`) and `chat_messages` (`conversation_id`,
  `sender_type` `user|admin`, `sender_id`, `body`, `read_at`). One conversation per
  customer.
- API: `GET /api/me/chat` (messages, newest last, paginated, marks admin messages
  read), `POST /api/me/chat` (`body`), `GET /api/me/chat/unread-count`.
- Admin panel: **Chats** section, list ordered by `last_message_at` with unread badge,
  conversation page with reply form. Admin replies create an in-app notification for
  the customer.
- Delivery is polling for now (the app refreshes every few seconds while the chat
  screen is open); keep the code ready for Pusher/WebSockets later.

### 4.5 Mobile app

Wire the new API into the Expo app: pickup/delivery toggle, delivery fee and total,
payment method picker, `delivering` status label, order search, avatar upload, shop
request flow, Chats tab backed by the real API.

Commit per sub-section; push after the phase.

---

## Phase 5 — Final pass

- Run both READMEs against reality and fix anything that drifted.
- `php artisan test`, `npm run build` (backend assets) and `npx expo export --platform web`
  (mobile) must succeed.
- Summarize in the final message: what was built, what was removed, how to run the
  website, where the APK/build instructions are, and anything left undone with the reason.

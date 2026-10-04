# Sowgatly — Flutter programma üçin API işleri (ädimme-ädim prompt)

> **Nähili ulanmaly:** Täze Claude Code sessiýasyny açyp, `sowgatly` reposynyň `main`
> branchyny saýlaň we şeýle ýazyň:
>
> `PROMPT-API.md faýlyny oka we ondaky işleri Phase 0-dan başlap tertip bilen ýerine ýetir.
> Her Phase-den soň commit et we main-a push et.`
>
> Bu faýl `PROMPT.md`-den (web storefront) aýratyn: ol ýerdäki Phase 0 (leftover-leri
> aýyrmak) bu işden **öň** edilen bolmaly. Flutter programma `Esca6585dev/sowgatly-app`
> reposynda, onuň `PROMPT.md`-si her Phase-de haýsy API Phase gerekdigini görkezýär.
> Bir sessiýada hemmesi gutarmasa, indiki sessiýada `PROMPT-API.md-de galan işleri dowam et`
> diýseňiz ýeterlik.
>
> Prompt iňlisçe ýazylan, sebäbi model tehniki tabşyrygy iňlisçe has takyk ýerine ýetirýär.


> **Progress (2026-10-04): all phases are implemented** and covered by tests under
> `tests/Feature/Api`, `tests/Feature/Admin` and `tests/Feature/SwaggerCoversRoutesTest.php`
> (`php artisan test` is green, Swagger regenerated). Differences from the text below:
> `shops.delivery_fee` defaults to **20** (the fee in the design); `shops.status`
> (pending/approved/rejected) exists; `ShopApplication` has an `admin_note`; checkout is
> still **single-shop** (all cart items go into one order under the first item's shop —
> per-shop splitting would change the `order` response and is left for a later decision);
> a review's `order_id` must belong to the caller and not be cancelled (not necessarily
> `completed`); `product_reviews` pagination only switches on when `page` is sent; the
> API locale comes from `Accept-Language` (`SetApiLocale` middleware); push uses FCM
> HTTP v1 with a service-account file (`FCM_SERVICE_ACCOUNT_FILE`), and the old controller
> with a hard-coded legacy FCM server key was removed (rotate that key in Firebase).
> See `README.md` → "API for the Flutter app" and `docs/notifications.md`.

---

## Context

`Esca6585dev/sowgatly` is the Laravel 10 backend of Sowgatly (flower and gift marketplace
for Turkmenistan). It already has a REST API under `/api` (Sanctum bearer tokens, OTP login
by phone, `routes/api.php`), an admin panel (`/{locale}/admin`, Blade, Spatie permissions)
and the data model: regions, shops, categories (`name_tm/ru/en`), products with images,
attributes, compositions, brands, carts, orders (status machine in `Order::TRANSITIONS`,
`delivery_type asap|scheduled`, `scheduled_at`, `recipient_phone`, `delivery_address`,
`note`), favorites, product reviews (one per user+product, `rating 1..5`, `comment`),
user addresses, in-app notifications (`type`, `data` json, `read_at`), devices (FCM tokens).
Read `README.md` and `routes/api.php` first.

A new Flutter client is being built from the Figma design
`https://www.figma.com/design/amTVuTZbUOhu2avAmuXa8I`. The design needs data the API does
not have yet. This prompt adds it, **additively**.

**Hard rules**

- **Never change the JSON shape or the semantics of an existing `/api/*` endpoint.**
  The React Native app and the Flutter app both consume them. Adding fields to a response,
  adding optional request fields with safe defaults, and adding new routes is fine.
  Removing or renaming anything is not.
- Reuse the existing models, resources, policies and business rules
  (`Order::canTransitionTo`, `TurkmenistanPhoneNumber`, OTP services, ownership checks in
  controllers). No second copy of logic.
- Every new write endpoint checks ownership (the user can only touch their own rows) and
  is covered by a feature test. Every new endpoint gets `@OA` annotations.
- Keep the admin panel working; where a new table needs management (banners, shop
  applications, chats moderation) add a minimal admin CRUD in the existing admin style
  (`routes/admin-routes/panel`, Blade + Metronic, Spatie permission per resource).
- Migrations are additive (`nullable` or with defaults) so existing rows stay valid.
- Every phase ends with: `php artisan test` green (MySQL database `sowgatly`),
  `php artisan route:list --path=api` free of errors, `php artisan l5-swagger:generate`
  run, a commit with an English message, and a push to `main`.
- Code comments, commit messages and Swagger descriptions in English. User-visible strings
  (notification texts) go through `lang/tm`, `lang/ru`, `lang/en`, Turkmen first.
- Never commit `.env`, `vendor/`, `node_modules/`, keystores or API keys.

---

## Phase 0 — Baseline and conventions

1. Make sure `PROMPT.md` Phase 0 was done (no `chess` routes, no `GameController`
   reference). If the `Route::prefix('chess')` block is still in `routes/api.php`, remove
   it now: it points at a class that does not exist.
2. Add `app/Http/Controllers/Api/Concerns/RespondsWithJson.php` trait with
   `ok($data = [], $message = null, $status = 200)` →
   `{ "success": true, "message": ..., ...data }` and `fail($message, $status = 422, $errors = null)`
   → `{ "success": false, "message": ... }`. Use it in **new** controllers only; do not
   refactor the existing ones (shape risk).
3. Add `tests/Feature/Api/ApiTestCase.php` base class: creates a user via factory, acts
   with Sanctum (`Sanctum::actingAs`), helpers `shopWithProducts()`, `cityRegion()`.
   Check that factories exist for `User`, `Region`, `Shop`, `Category`, `Product`,
   `Image`; add the missing ones.
4. Verify `.env.example` documents `OTP_DEBUG_CODE` and add `APP_API_GUEST_BROWSING=true`
   (used in Phase 1).

Commit: `Add JSON response trait and API test base`.

---

## Phase 1 — Public read-only catalog (guest browsing)

The app lets guests browse before logging in (Flowwow-style). Today every catalog route sits
inside the `auth:sanctum` group.

1. Move these **GET** routes out of the authenticated group into a new group with
   middleware `['throttle:120,1']` only, keeping the same URIs, controllers and responses:
   `product/search`, `product/category/{category_id}`, `products/{id}` (show only),
   `products/{id}/reviews` (index only), `categories`, `categories/{id}`,
   `categories/{id}/subcategories`, `compositions`, `brands`, `shops` (index, show),
   `regions`, `regions/{id}`, `regions/parent/{parent_id}`.
   Leave `GET products` (index) authenticated: it lists the caller's own shop products.
   Leave every write route authenticated.
2. Guard the move behind `config('app.api_guest_browsing')` (`APP_API_GUEST_BROWSING`,
   default `true`) so it can be switched off without a deploy of code.
3. Controllers that call `Auth::user()` in those read methods must tolerate `null`
   (e.g. `ProductController::show` must not fail for a guest; a favorite flag, if any,
   is `false` for guests). Authenticated calls must behave exactly as before — add
   `Route::middleware('auth:sanctum')` optional-auth behaviour by registering the group
   with `->middleware(['throttle:120,1'])` and letting Sanctum's `EnsureFrontendRequestsAreStateful`
   / bearer parsing set the user when a token is present (use the `auth.optional`
   middleware pattern: try `auth('sanctum')->user()` without aborting).
4. Tests: guest can read search/show/categories/shops/regions; guest gets 401 on
   cart/favorites/orders; authenticated responses unchanged (snapshot one product JSON
   before and after).

Commit: `Make the read-only catalog available to guests`.

---

## Phase 2 — Home feed, banners, product rating summary

Design: home `174:329` has category tiles, a promo banner row ("С НОВЫМ ГОДОМ / Дарите
моменты счастья"), product sections per category, and product cards with a rating.

1. **Rating summary on products.** Add `reviews_avg` (float, 1 decimal, null when no
   reviews) and `reviews_count` (int) to `ProductResource` using `withAvg`/`withCount`
   where the controller loads products (search, category, show, favorites). Additive
   fields only. Add a composite index on `product_reviews (product_id, rating)`.
   Same for shops: `rating_avg`, `reviews_count` on `ShopResource` computed over the shop's
   products' reviews (eager, not N+1: a subquery select).
2. **Banners.** Migration `banners`: `id, title_tm, title_ru, title_en, subtitle_tm/ru/en
   nullable, image (path), link_type enum(none,category,product,shop,url), link_value
   nullable, region_id nullable FK, position int default 0, is_active bool default true,
   starts_at/ends_at nullable datetimes, timestamps`. Model, factory, `BannerResource`
   (`id, title{tm,ru,en}, subtitle{}, image (absolute URL), link_type, link_value, position`).
   `GET /api/banners?region_id=` → active banners for that region or global, ordered by
   `position`. Admin CRUD under `/{locale}/admin/banners` with image upload (reuse the
   product image upload helper), permission `banner-list/create/edit/delete` seeded.
3. **Home endpoint.** `GET /api/home?region_id=&per_section=6`:

   ```json
   {
     "success": true,
     "region": { "id": 1, "name": "Aşgabat" },
     "categories": [ CategoryResource... ],          // root categories, ordered
     "banners": [ BannerResource... ],
     "sections": [
       { "key": "delivery_today", "title": {"tm":"...","ru":"...","en":"..."}, "products": [ProductResource...] },
       { "key": "popular",        "title": {...}, "products": [...] },
       { "key": "category:3",     "title": {...name of category...}, "category_id": 3, "products": [...] }
     ]
   }
   ```

   Rules: products must be `status = 1` and `seller_status = 1`, shop in `region_id`;
   `delivery_today` = `production_time` ≤ 180 minutes; `popular` = most ordered in the last
   30 days (count of `order_items`), fallback newest; one `category:*` section per root
   category that has ≥ 2 products in the region, newest first. Cache the whole payload
   per region for 5 minutes (`Cache::remember`), bust it in Banner/Product observers.
4. Add `min_rating` (float) and `sort=popular` to `GET /api/product/search`
   (extends the existing `switch`; default stays `latest()`). Add `delivery_today=1` filter.
5. Swagger for all of it. Tests: home sections shape, region filtering, banners active
   window, `min_rating` filter, `sort=popular`.

Commit: `Add home feed, banners and rating summaries`.

---

## Phase 3 — Reviews with three criteria

Design (`39:114`, `46:2`): a review has three ratings — Соответствие (match),
Цена / качество (value), Сервис магазина (service) — plus a comment, and is written from the
completed order.

1. Migration: add to `product_reviews` nullable `rating_match`, `rating_value`,
   `rating_service` (tinyint 1..5) and nullable `order_id` FK (`orders`, nullOnDelete).
2. `POST /api/products/{id}/reviews`: accept the three optional criteria and `order_id`.
   When the three are present and `rating` is absent, compute `rating = round(avg)`.
   Keep `rating` required-or-derived so old clients still work. When `order_id` is given,
   verify the order belongs to the user, is `completed`, and contains the product.
   Keep the existing rule "only buyers can review" and the unique `(user_id, product_id)`.
3. `GET /api/products/{id}/reviews`: add `rating_match/value/service`, `order_id`,
   `user: {id, name, image}` to each item (additive), paginate with `?page=` **only when
   `page` is sent**; without it return the plain list as today.
4. `GET /api/orders/{id}`: add `items[].reviewed` (bool) so the app knows which products
   still need a review. Additive.
5. Tests: criteria saved, derived rating, order ownership, `reviewed` flag.

Commit: `Add three-criteria product reviews linked to orders`.

---

## Phase 4 — Checkout options: pickup, delivery fee, payment, waitlist

Design (`41:31`): "Доставка | Самовывоз", "Доставка 20 ТМТ", "Способ оплаты: Онлайн оплата"
with a bank list (Рысгал, Сенагат, Внешэкономбанк, Халкбанк), "Итого". Orders screens have a
"Лист ожидания" tab and the profile has a "Лист ожидания" row.

1. **Shops:** migration adds `delivery_fee decimal(10,2) default 20`, `pickup_available
   bool default false`, `min_order_amount decimal(10,2) nullable`, `phone string nullable`,
   `description_tm/ru/en text nullable`, `rating`-related nothing (computed in Phase 2).
   Expose them in `ShopResource` (additive) and in the admin shop form.
2. **Orders:** migration adds `fulfillment enum(delivery,pickup) default delivery`,
   `delivery_fee decimal(10,2) default 0`, `items_total decimal(10,2) nullable`,
   `payment_method enum(cash,online) default cash`, `payment_bank string nullable`,
   `payment_status enum(unpaid,paid,refunded) default unpaid`, `recipient_name string nullable`.
   `POST /api/orders` accepts optional `fulfillment` (default `delivery`),
   `payment_method` (default `cash`), `payment_bank` (required when `online`, one of
   `rysgal, senagat, vneshekonombank, halkbank`), `recipient_name`.
   `delivery_address` becomes required only when `fulfillment = delivery`
   (use `required_if`; the old required rule must still hold for old clients that never
   send `fulfillment`). `delivery_fee` = shop's `delivery_fee` for delivery, 0 for pickup;
   `items_total` = sum of items; `total_amount` = items_total + delivery_fee
   (**this changes a number, not a shape**; document it in the commit and README).
   Return the new columns in the order JSON (additive). Online payment is a stub: store
   the bank and keep `payment_status = unpaid`; add `config/payments.php` with the bank
   list so the app can fetch it from `GET /api/payment-methods`
   (`[{code, name{tm,ru,en}, type: cash|online}]`).
3. **Order number:** add accessor `number` = `str_pad(id, 7, '0', STR_PAD_LEFT)` and
   append it to order JSON (`"number": "0000001"`).
4. **Waitlist** (products the user wants to be told about when back in stock or
   available in their city): migration `waitlist_items` (`user_id, product_id, region_id
   nullable, notified_at nullable, timestamps`, unique user+product).
   `GET /api/me/waitlist` (products with `ProductResource`), `POST /api/me/waitlist
   { product_id }`, `DELETE /api/me/waitlist/{product_id}`. When a product's `stock`
   goes from 0/null to > 0 or `status` becomes active (Product observer), create a
   `UserNotification` type `product_available` for each waiting user and set `notified_at`.
5. Tests: pickup order has no address and zero fee; delivery adds the shop fee; online
   needs a bank; totals; old payload (no new fields) still creates a valid order;
   waitlist CRUD and notification on restock.

Commit: `Add pickup, delivery fee, payment method and waitlist to orders`.

---

## Phase 5 — Profile: birth date and avatar upload

Design (`61:104`, `64:44`): "Настройка профиля" with photo, phone, name, e-mail, date of
birth.

1. Migration: `users.birth_date date nullable`. Add to `$fillable` and `UserResource`.
2. `PUT /api/users/me`: accept `birth_date` (`nullable|date|before:today`) and `image`
   either as multipart file (`image|max:4096`) or base64 string (reuse the product
   `uploadBase64Image` helper by extracting it into `app/Support/ImageUploader.php` and
   using it from both places). Store under `storage/app/public/users/{id}/avatar.jpg`,
   delete the previous file. `UserResource.image` already returns an absolute URL — keep it.
   Also accept `POST /api/users/me` with `_method=PUT` for clients that cannot send
   multipart `PUT`.
3. `DELETE /api/users/me/image` removes the avatar.
4. Tests: birth_date validation, multipart upload, base64 upload, old file removed.

Commit: `Add birth date and avatar upload to the profile endpoint`.

---

## Phase 6 — Favorite collections ("Подборки")

Design (`37:14`, `28:2`): favorites are organised into named collections with a cover
mosaic and item count; "+" creates one.

1. Migrations: `favorite_collections` (`id, user_id, name (100), position int default 0,
   timestamps`, unique user+name) and `favorite_collection_items` (`collection_id,
   product_id, timestamps`, unique pair). Keep the existing `favorites` table as the
   implicit default list; do not migrate rows.
2. Endpoints under `auth:sanctum`:
   - `GET /api/me/collections` → `[{id, name, items_count, covers: [url up to 4], created_at}]`
   - `POST /api/me/collections { name }` → the collection
   - `PUT /api/me/collections/{id} { name?, position? }`
   - `DELETE /api/me/collections/{id}`
   - `GET /api/me/collections/{id}` → collection + `products: ProductResource[]` (paginated
     with `?page=`, same `meta` shape as product search)
   - `POST /api/me/collections/{id}/products { product_id }` and
     `DELETE /api/me/collections/{id}/products/{product_id}`
   - Add `collection_ids: [..]` to `ProductResource` **only when loaded** via
     `whenLoaded` so existing payloads are untouched.
3. Ownership checks on every route (404 for another user's collection, never 403 that
   leaks existence). Max 50 collections per user, 500 items per collection.
4. Tests: CRUD, ownership, unique name, covers, pagination.

Commit: `Add favorite collections`.

---

## Phase 7 — Chats between customers and shops

Design (`37:13`, `64:99`): chat list with "Менеджер", last message, time, unread badge;
thread with bubbles and an input. Note: `App\Models\Message` already exists for the
website contact form, so use different names.

1. Migrations: `chat_threads` (`id, user_id, shop_id, order_id nullable, last_message_at
   nullable, user_unread int default 0, shop_unread int default 0, timestamps`, unique
   user+shop+order) and `chat_messages` (`id, thread_id, sender_type enum(user,shop),
   sender_id, body text (max 2000), read_at nullable, timestamps`, index thread+id).
2. Customer endpoints (`auth:sanctum`):
   - `GET /api/me/chats` → threads with `shop {id,name,image}`, `order_id`,
     `last_message {body, sender_type, created_at}`, `unread` (user_unread), ordered by
     `last_message_at desc`.
   - `POST /api/me/chats { shop_id, order_id? }` → existing or new thread (idempotent).
   - `GET /api/me/chats/{id}/messages?after=<id>&limit=50` → messages ascending; without
     `after` the last 50.
   - `POST /api/me/chats/{id}/messages { body }` → the message; increments `shop_unread`,
     updates `last_message_at`, creates a `UserNotification`-like record for the shop owner
     (type `chat_message`, for the shop owner's user id).
   - `POST /api/me/chats/{id}/read` → zero `user_unread`, set `read_at` on shop messages.
   - `GET /api/me/chats/unread-count` → `{ unread: n }`.
3. Shop-side endpoints for the shop owner (`$request->user()->shop`):
   `GET /api/shop/chats`, `GET /api/shop/chats/{id}/messages`,
   `POST /api/shop/chats/{id}/messages`, `POST /api/shop/chats/{id}/read`. Same shapes,
   `sender_type = shop`.
4. Rate limit message sending `throttle:30,1`. Strip HTML from `body`.
5. Admin: read-only list of threads and messages under `/{locale}/admin/chats` for
   moderation (permission `chat-list`).
6. Optional, behind `BROADCAST_DRIVER`: broadcast `ChatMessageSent` on a private channel
   `chat.{thread_id}` so the app can switch from polling to websockets later. Do not make
   Pusher a hard dependency; polling must work.
7. Tests: thread creation idempotent, ownership, unread counters both sides, `after`
   pagination, throttle.

Commit: `Add customer-shop chats`.

---

## Phase 8 — Shop applications, notification types, Swagger

1. **Shop applications** ("Разместить свой магазин"): migration `shop_applications`
   (`id, user_id nullable, name, phone, region_id nullable, description text nullable,
   status enum(new,contacted,approved,rejected) default new, timestamps`).
   `POST /api/shop-applications { name, phone (TurkmenistanPhoneNumber), region_id?,
   description? }` — allowed for guests and users, `throttle:5,60`. Admin list with
   status change under `/{locale}/admin/shop-applications` (permission
   `shop-application-list/edit`). If `PROMPT.md` Phase 1 already built the website
   "for-shops" form writing into `messages`, make that form call the same service so both
   land in `shop_applications`.
2. **Notification catalogue.** Document every `UserNotification.type` the API emits and
   the `data` keys in `docs/notifications.md`: `order_status` (`order_id, status`),
   `product_available` (`product_id`), `chat_message` (`thread_id, shop_id`), plus any
   found in the code. Add `GET /api/me/notifications/unread-count` → `{ unread: n }`.
   Add `title` and `body` (localized by `Accept-Language`, Turkmen default) to each item in
   `GET /api/me/notifications` as additive fields, generated server-side from `lang/*`.
3. **Push:** when a `UserNotification` is created, send an FCM message to the user's
   `devices` if `FCM_SERVER_KEY` (or the v1 service-account file path) is configured;
   otherwise skip silently. Queueable job, `QUEUE_CONNECTION` default `sync`. Document
   the env vars in `.env.example` and README.
4. **Swagger:** every route in `php artisan route:list --path=api` must appear in
   `storage/api-docs/api-docs.json`. Regenerate and commit. Add a `tests/Feature/SwaggerCoversRoutesTest.php`
   that fails when an API route is missing from the spec.
5. **README:** new section "API for the Flutter app" linking to Swagger, listing the
   endpoints added in Phases 1–8 and the `APP_API_GUEST_BROWSING`, payment and FCM env vars.

Commit: `Add shop applications, notification catalogue, push and Swagger coverage`.

---

## Phase 9 — Final pass

- `php artisan test` green, `php artisan route:list` clean, `composer dump-autoload`,
  `php artisan l5-swagger:generate`, `npm run build` (admin/web assets).
- Diff `storage/api-docs/api-docs.json` against the version before Phase 1 and confirm
  every pre-existing path and schema is still present and unchanged except for additive
  properties.
- Summarize in the final message: endpoints added per phase, migrations added, admin
  pages added, env vars added, anything left undone and why.

---

## Appendix — Endpoint summary added by this prompt

| Phase | Method | Path | Auth |
|---|---|---|---|
| 1 | GET | catalog read routes (search, products/{id}, categories, shops, regions, brands, compositions, reviews index) | guest |
| 2 | GET | `/api/home`, `/api/banners` | guest |
| 2 | GET | `/api/product/search` + `min_rating`, `delivery_today`, `sort=popular` | guest |
| 3 | POST | `/api/products/{id}/reviews` + `rating_match/value/service`, `order_id` | user |
| 4 | POST | `/api/orders` + `fulfillment`, `payment_method`, `payment_bank`, `recipient_name` | user |
| 4 | GET | `/api/payment-methods` | guest |
| 4 | GET/POST/DELETE | `/api/me/waitlist`, `/api/me/waitlist/{product_id}` | user |
| 5 | PUT | `/api/users/me` + `birth_date`, `image`; DELETE `/api/users/me/image` | user |
| 6 | CRUD | `/api/me/collections`, `/api/me/collections/{id}/products` | user |
| 7 | CRUD | `/api/me/chats`, `/api/me/chats/{id}/messages`, `/read`, `/unread-count`; `/api/shop/chats*` | user / shop owner |
| 8 | POST | `/api/shop-applications` | guest |
| 8 | GET | `/api/me/notifications/unread-count` | user |

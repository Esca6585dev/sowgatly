# sowgatly.app — backend

Laravel 10 backend for **Sowgatly**, a gift and flower marketplace for Turkmenistan.
It serves three things from one codebase:

- **REST API** under `/api` used by the mobile app
  ([sowgatly-app-react-native](https://github.com/Esca6585dev/sowgatly-app-react-native)).
- **Admin panel** under `/{locale}/admin` (categories, brands, regions, shops,
  products, users, roles and permissions, plus orders, chats and shop applications).
- **Public website** under `/{locale}/sowgatly` (Blade views).

## Requirements

| Tool     | Version                              |
|----------|--------------------------------------|
| PHP      | 8.1 or newer (8.3 tested)            |
| Composer | 2.x                                  |
| MySQL    | 8.x (or MariaDB)                     |
| Node.js  | 18+ (only for building Blade assets) |

PHP extensions: `pdo_mysql`, `mbstring`, `gd` (QR codes), `zip` (Excel export),
`sockets` (SMPP SMS gateway).

## Quick start

```bash
git clone https://github.com/Esca6585dev/sowgatly.git
cd sowgatly

composer install
cp .env.example .env
php artisan key:generate

# create an empty database named "sowgatly", then:
php artisan migrate --seed
php artisan storage:link

php artisan serve          # http://localhost:8000
```

Blade assets (admin panel and website) are built with Vite:

```bash
npm install
npm run dev     # or: npm run build
```

## Environment variables

Copy `.env.example` and fill in what you need. Everything else has a working default.

| Variable | Purpose |
|----------|---------|
| `DB_*` | MySQL connection. Database name defaults to `sowgatly`. |
| `APP_URL` | Public URL, used in links and Swagger. |
| `L5_SWAGGER_CONST_LOCAL_HOST` / `L5_SWAGGER_CONST_REMOTE_HOST` | Servers listed in the Swagger UI. |
| `SMPP_HOST`, `SMPP_PORT`, `SMPP_SYSTEM_ID`, `SMPP_PASSWORD` | SMS gateway that delivers login codes. |
| `TWILIO_SID`, `TWILIO_TOKEN`, `TWILIO_FROM` | Alternative SMS provider. |
| `OTP_DEBUG_CODE` | **Development only.** When set (for example `0000`) every login code equals this value and no SMS is sent. Leave empty in production. |
| `SANCTUM_STATEFUL_DOMAINS` | Origins allowed to use cookie auth (the Expo web build runs on `localhost:19006`). |
| `SWAGGER_TOKEN` | Pre-filled bearer token in the Swagger UI. |

## Seeded accounts

`php artisan migrate --seed` creates:

- **Admin panel:** username `admin-sowgatly`, password `password-sowgatly`
  (see `database/seeders/AdminSeeder.php`). Change this before deploying.
- Sample users, regions, shops, categories, brands and products for local testing.

## Authentication (API)

Customers sign in with their phone number and a 4-digit one-time code.

1. `POST /api/otp/generate` with `phone_number` sends a code by SMS.
2. `POST /api/login` with `phone_number` and `otp` returns a Sanctum bearer token.
3. New users call `POST /api/register` with `phone_number` and `name` instead; the
   response includes the code when `OTP_DEBUG_CODE` is set.
4. Send `Authorization: Bearer <token>` on every other request.
   `POST /api/logout` revokes the token.

Login endpoints are rate limited to 10 requests per minute per IP.

## API overview

All routes below require a bearer token. Full request and response schemas are in
Swagger at **`/api/documentation`** (JSON at `/api/json`).

| Area | Endpoints |
|------|-----------|
| Catalog | `GET /products`, `GET /product/search?name=`, `GET /product/category/{id}`, `GET /categories`, `GET /categories/{id}/subcategories`, `GET /brands`, `GET /compositions`, `GET /regions`, `GET /regions/parent/{id}` |
| Reviews | `GET/POST /products/{id}/reviews` |
| Profile | `GET/PUT /users/me`, `GET/POST/PUT/DELETE /me/addresses` |
| Cart | `GET /cart`, `POST /cart/add`, `PUT/DELETE /cart/items/{id}` |
| Favorites | `GET /favorites`, `POST /favorites/toggle` |
| Orders | `POST /orders`, `GET /orders`, `GET /orders/{id}`, `POST /orders/{id}/cancel` |
| Shop owners | `GET /shops`, `POST/PUT/DELETE /shops/{id}` (own shop only), `POST/PUT/DELETE /products` (own shop only), `GET /shop/orders`, `PUT /shop/orders/{id}/status` |
| Notifications | `GET /me/notifications`, `POST /me/notifications/read` |

| Checkout extras | `GET /payment-methods` (cash + online banks), `POST /orders` accepts `fulfillment` (`delivery`/`pickup`), `payment_method` (`cash`/`online`), `payment_bank`, `recipient_name`; `GET /orders?q=` searches by number or product |
| Waiting list | `GET/POST /me/waitlist`, `DELETE /me/waitlist/{product_id}`; a `product_available` notification is created when the product is back |
| Chats | Customer: `GET/POST /me/chats`, `GET/POST /me/chats/{id}/messages`, `POST /me/chats/{id}/read`, `GET /me/chats/unread-count`. Shop owner: the same under `/shop/chats` |
| Shop applications | `POST /shop-applications` (guests too), `GET /me/shop-applications` |
| Avatar | `PUT`/`POST /users/me` with `image` (file or base64) and `birth_date`; `DELETE /users/me/image` |

Categories, brands, compositions, regions and shop addresses are read-only in the
API and managed from the admin panel.

### Order lifecycle

Statuses: `pending → processing → delivering → completed`, `cancelled` from pending
or processing. Customers may cancel while `pending`; the shop moves the order forward
(`PUT /shop/orders/{id}/status`) and so can an admin. Every order carries
`items_total`, `delivery_fee` (the shop's fee, 0 for pickup), `total_amount`,
`payment_method`, `payment_bank`, `payment_status` (`unpaid`/`paid`/`refunded`) and a
zero-padded `number`. Online payment is not connected to a bank gateway yet: the
chosen bank is stored and the order stays `unpaid` until an admin marks it paid.

## Project layout

```
app/Http/Controllers/Api/         REST API controllers
app/Http/Controllers/AdminControllers/  Admin panel
app/Http/Controllers/UserControllers/   Public website
app/Http/Resources/               API JSON transformers
app/Models/                       Eloquent models
app/Rules/                        e.g. TurkmenistanPhoneNumber
database/migrations/              Schema
database/seeders/                 Demo data and admin account
routes/api.php                    API routes
routes/web.php, routes/admin-routes/   Web and admin routes
resources/views/                  Blade templates
storage/api-docs/api-docs.json    Generated Swagger spec
tests/Feature/                    API feature tests
```

## Tests

Tests run against a MySQL database named `sowgatly` (see `phpunit.xml`), so make
sure the database from the quick start exists, then:

```bash
php artisan test
```

## Notes for contributors

- `vendor/` is **not** committed. Run `composer install` after cloning or pulling.
- Never commit `.env`. Add new variables to `.env.example` with an empty or safe value.
- Regenerate Swagger after changing API annotations: `php artisan l5-swagger:generate`.
- Feature branches are merged into `main`; `main` is the only long-lived branch.

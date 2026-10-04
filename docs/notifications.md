# In-app notifications (`user_notifications`)

Every row has `type`, a JSON `data` object and `read_at`. `GET /api/me/notifications`
also returns a localized `title` and `body` (by `Accept-Language`: `tm` default, `ru`,
`en`; texts live in `resources/lang/*/api.php` under `notifications`). When Firebase
Cloud Messaging is configured (`FCM_SERVICE_ACCOUNT_FILE`), the same title/body is
pushed to every device token of the user (`devices` table) by the queued
`SendPushNotification` job; the push `data` payload carries `notification_id`, `type`
and the keys below as strings.

| `type` | Emitted when | `data` keys | Opens |
|---|---|---|---|
| `order_created` | an order is placed (`POST /api/orders`) | `order_id`, `status` (`pending`) | order detail |
| `order_status` | the shop or an admin changes the order status | `order_id`, `status` (`processing`, `delivering`, `completed`, `cancelled`) | order detail |
| `product_available` | a waiting-list product gets stock or is re-activated | `product_id` | product |
| `chat_message` | the other side posts in a chat thread | `thread_id`, `shop_id`, `message_id` | chat thread |
| `shop_application` | an admin approves or rejects a shop application | `application_id`, `status` (`approved`, `rejected`) | profile → "Open your shop" |

Counts: `GET /api/me/notifications/unread-count` → `{ "unread": n }`;
`POST /api/me/notifications/read` marks everything read.

Adding a type: create the row with `UserNotification::create([...])`, add the keys to
`UserNotification::TYPES`, add `notifications.<type>.title/body` to the three `api.php`
lang files and extend this table.

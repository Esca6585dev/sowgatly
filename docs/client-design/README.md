# Client website — design prototype

`storefront.html` is a self-contained clickable prototype of the customer website and
account panel (open it in a browser; no server needed). It uses the same tokens as the
admin design (`public/css/admin-design.css`) and works in Turkmen, Russian, English and
Turkish, light and dark, desktop and phone. Products, orders and the profile are sample
data kept in memory.

What it covers: header with logo, city, search, language switch, theme, favorites, cart
and account; banner rail; categories; product shelves and cards with add-to-cart
steppers; product page; cart with promo code (`SOWGAT15`); checkout; account panel with
active orders and a status tracker, order history with reorder, addresses, profile
(name, email, phone, birthday), settings (language, theme, notifications) and log out;
mobile bottom tab bar.

The build plan for the real Blade implementation is `PROMPT-CLIENT.md` in the repo root.

Screenshots: `d-*.png` desktop, `m-*.png` phone.

# Customer flow & business logic (notes for the report / test cases)

Owner: Huynh Thanh Tu. Covers: registration, login, profile, order history, cart, guest/member checkout,
guest/member pricing, coupon validation, delivery fee, simulated payment, order confirmation, customer-side order status.

## Business rules
| Rule | Implementation |
|---|---|
| Guest vs member price | `Product::priceFor($user)` – `member_price` if logged in, else `guest_price`. Prices are never stored in the cart session (only `product_id => qty`), so cart, checkout and order always agree. The unit price is snapshotted into `order_items.price`. |
| Coupon | `CouponService`: code (case-insensitive) must exist, `is_active = 1`, subtotal ≥ `min_order_value`. Discount = fixed `discount_value` (VND), capped at the subtotal. One coupon per order. Re-validated when the cart changes and again at checkout. |
| Delivery fee | Fixed `config('booknest.delivery_fee')` (30.000 ₫, env `BOOKNEST_DELIVERY_FEE`); 0 for an empty cart. |
| Total | `subtotal − discount + delivery_fee` (derived, not stored – `Order::total_price`, as in the ERD). |
| Stock | Checked on add, on quantity update, on opening checkout and again inside the order transaction (row lock); stock is decremented when the order is placed. |
| Payment (simulated) | `cod` → `Unpaid`; `card` / `ewallet` → `Paid` immediately. No gateway is called. |
| Order status | `Pending → Confirmed → Shipping → Completed` or `Cancelled`. Created as `Pending`; the admin area updates it, customers see it on the order page. |
| Guest access to an order | Guests can open the confirmation/status of orders placed in the current session, or look an order up with reference (`BN-000012`) + email on `/orders/track`. Members can only open their own orders (others return 404). |

## Routes
| Route | Who | Purpose |
|---|---|---|
| `GET/POST /register`, `GET/POST /login`, `POST /logout` | guest / member | account |
| `GET/PUT /profile` | member | name, email, phone, delivery address |
| `GET /orders`, `GET /orders/{id}` | member | order history, order details + status |
| `GET /cart`, `POST /cart/add/{product}`, `PATCH/DELETE /cart/items/{product}` | all | cart (AJAX + non-JS fallback) |
| `POST/DELETE /cart/coupon` | all | apply / remove coupon (AJAX + fallback) |
| `GET/POST /checkout` | all | delivery info, payment method, place order |
| `GET /orders/{id}/confirmation` | owner / session | order confirmation |
| `GET/POST /orders/track` | all | guest order status lookup |

## Database
PostgreSQL (team decision). The migrations use only Laravel's Schema Builder, and the full test suite plus `migrate:fresh --seed` were run against PostgreSQL 16.
Create the database first (`CREATE DATABASE booknest;`) and set `DB_CONNECTION=pgsql` and the `DB_*` values in `.env` (see `.env.example`).

## Demo data (`php artisan migrate:fresh --seed`)
- Member: `lan@example.com` / `Password123`
- Admin (for the admin area): `admin@booknest.test` / `Password123`
- Coupons: `WELCOME25` (−25.000 ₫, min 200.000 ₫), `BOOK50` (−50.000 ₫, min 500.000 ₫), `EXPIRED10` (inactive)
- One book is seeded with stock 0 to demo the out-of-stock message.

## Automated tests
`php artisan test` – `AccountTest`, `CartAndPricingTest`, `CheckoutAndOrdersTest` (34 tests with the default example test).

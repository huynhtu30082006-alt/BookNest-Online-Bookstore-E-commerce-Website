# BookNest – Online Bookstore E-commerce Website

BookNest is a web-based online bookstore for students and young professionals. Customers can browse printed books and
**buy without registering (guest)** or **register to get member prices**, use discount codes and follow their orders.
The cart and checkout always show the guest/member price, discount, delivery fee and final total.

**Course:** Web Programming – Web E-commerce Project · **Team:** WEB_2627302_01 · University of Transport Ho Chi Minh City (UTH)

> More documentation:
> [`DOCUMENTATION/SETUP.md`](DOCUMENTATION/SETUP.md) (installation) ·
> [`DOCUMENTATION/CUSTOMER_FLOW.md`](DOCUMENTATION/CUSTOMER_FLOW.md) (customer flow notes) ·
> [`DOCUMENTATION/STUDENT_INFO.md`](DOCUMENTATION/STUDENT_INFO.md) (team and student information)

---

## Table of contents
1. [Team and repository](#1-team-and-repository)
2. [Technology stack](#2-technology-stack)
3. [Run the project](#3-run-the-project)
4. [Automated tests](#4-automated-tests)
5. [Project structure](#5-project-structure)
6. [Customer flow](#6-customer-flow)
7. [Git workflow and project coordination](#7-git-workflow-and-project-coordination)

---

## 1. Team and repository

Repository: <https://github.com/huynhtu30082006-alt/BookNest-Online-Bookstore-E-commerce-Website>

| Member | Student ID | Responsibility |
|---|---|---|
| Huynh Thanh Tu | 054206000112 | Customer flow and business logic, Git coordination |
| Truong Ho Anh Thu | 056306012648 | Database and administrator area, report writing |
| Tran Van Linh | 068206000050 | UI, product browsing, responsive layout, AJAX |

---

## 2. Technology stack

| Component | Technology | Purpose |
|---|---|---|
| Frontend | HTML5, CSS3, JavaScript | Build website structure, styling and interactive features |
| UI Framework | Bootstrap 5 (CDN) | Create responsive layouts and reusable UI components |
| Template Engine | Laravel Blade | Render dynamic pages using backend data |
| Backend | PHP, Laravel 13 | Business logic, authentication, application workflows and server-side processing |
| Database | **PostgreSQL** | Store and manage application data |
| Automated tests | PHPUnit 12 (via `php artisan test`) | Feature and unit tests on an in-memory SQLite database |
| Development Environment | Laravel Herd | Set up and run the local PHP/Laravel environment |
| Version Control | Git and GitHub | Manage source code and support team collaboration |
| Code Editor | Visual Studio Code | Write and manage application code |

> The Checkpoint 01 report lists MySQL; the team later decided to use **PostgreSQL**, which is what this project is set up for.

---

## 3. Run the project

### Requirements
- PHP **8.4+** (the locked Laravel/Symfony dependencies require it) and Composer – Laravel Herd provides both
- PostgreSQL (tested on 16) with an empty database named `booknest`
- Git
- Internet access in the browser (Bootstrap is loaded from a CDN; no `npm install` / `npm run build` is needed)

### Steps
```bash
# 0. Get the code
git clone https://github.com/huynhtu30082006-alt/BookNest-Online-Bookstore-E-commerce-Website.git
cd BookNest-Online-Bookstore-E-commerce-Website

# 1. Create the database once (psql or pgAdmin)
#    CREATE DATABASE booknest;

# 2. Install and configure
cd CODE
composer install
cp .env.example .env            # Windows CMD: copy .env.example .env
php artisan key:generate
#    then open .env and set DB_PASSWORD (and DB_USERNAME if it is not "postgres")

# 3. Create tables and demo data  (WARNING: migrate:fresh drops every table in the database)
php artisan migrate:fresh --seed

# 4. Start the site
php artisan serve             # http://127.0.0.1:8000   (with Herd: http://booknest.test)
```

The database settings in `.env` are:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=booknest
DB_USERNAME=postgres
DB_PASSWORD=your_password
```
`.env` holds your personal passwords and is **never committed** (see [section 7](#7-git-workflow-and-project-coordination)).
Sessions are stored in the database (`SESSION_DRIVER=database`), so run the migrations before opening the site.

Optional setting: `BOOKNEST_DELIVERY_FEE` (VND, default `30000`) changes the delivery fee.

### Demo accounts and data (created by the seeders)
| Item | Value |
|---|---|
| Member | `lan@example.com` / `Password123` |
| Admin | `admin@booknest.test` / `Password123` |
| Coupon `WELCOME25` | −25.000 ₫, minimum order 200.000 ₫ (members only, one use per customer) |
| Coupon `BOOK50` | −50.000 ₫, minimum order 500.000 ₫ (members only, one use per customer) |
| Coupon `EXPIRED10` | inactive (used to demo the "no longer active" message) |
| Books | 12 books in 4 categories (Literature, Self-development, Textbooks & IT, Foreign languages) with a guest price and a lower member price |
| Out-of-stock book | *Minna no Nihongo – Sơ cấp 1* (stock 0) |

Tip: to try a coupon you must be logged in and the cart subtotal (at member prices) must reach the coupon's minimum,
e.g. 3 × *Atomic Habits* for `WELCOME25`.

---

## 4. Automated tests

```bash
cd CODE
php artisan test                                  # all tests
php artisan test --filter=CustomerFlowTest        # the customer-flow suite only
php artisan test --filter=test_member_can_cancel  # a single test (name or part of it)
```

The tests use an in-memory SQLite database (see `phpunit.xml`), so they never touch your PostgreSQL data.

| File | Content |
|---|---|
| `tests/Feature/CustomerFlowTest.php` | The complete suite for the customer flow (table below) |
| `tests/Feature/AccountTest.php` | Earlier tests for registration, login and profile |
| `tests/Feature/CartAndPricingTest.php` | Earlier tests for the cart, pricing and coupons |
| `tests/Feature/CheckoutAndOrdersTest.php` | Earlier tests for checkout and orders |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | Laravel default smoke tests |

What `CustomerFlowTest` covers:

| Area | What is verified |
|---|---|
| Git repository and project coordination | `.gitignore` protects `.env`, `vendor`, `node_modules`; `.env.example` has no secrets and targets PostgreSQL; tests use an isolated in-memory DB; required folders and documentation files exist; student info lists the repository and all members; the README explains how to run and test; Git does not track `.env`, `vendor` or `.history` |
| Registration | Valid registration, always the `customer` role, hashed password, field validation, password rules, Vietnamese phone formats and normalisation to `+84…`, optional address, shared phone numbers, guest cart kept |
| Login / logout | Success, wrong credentials, validation, redirect back to the wanted page, rate limiting, cart kept on login, cart and coupon cleared on logout |
| Profile | View, update, phone normalisation, unique email, validation, role cannot be changed |
| Order history | Only own orders, empty state, newest first, totals and status, pagination |
| Cart | Add / update / remove, accumulation, stock and per-item limits, out-of-stock and inactive books, validation, deactivated books dropped, AJAX JSON responses |
| Pricing and delivery fee | Guest vs member price, member savings hint, fixed delivery fee (0 for an empty cart), configurable fee, VND formatting, same price in cart / checkout / order, price snapshot on the order |
| Coupon validation | Members only, case-insensitive, minimum order (including the exact boundary), inactive / unknown / empty codes, discount capped at the subtotal, one coupon per order, removal, automatic drop when invalid, one use per member (account or email), cancelled orders free the coupon, re-check at checkout |
| Checkout | Guest and member orders, stored prices, stock decrement, cart and coupon cleared, pre-filled member form, validation, empty cart, stock changes, last copy, prices from the browser ignored |
| Simulated payment | COD → `Unpaid`; card / e-wallet → `Paid`; messages on the confirmation page |
| Confirmation and status | Confirmation page, order details, every status, admin status changes visible, privacy (404 for other people's orders), guest tracking (flexible reference, case-insensitive email, rate limiting) |
| Cancelling | Pending orders only, stock restored, `Paid` → `Refunded`, double cancel safe, nobody else can cancel |
| End-to-end journeys | Guest buys and tracks the order later; new member registers, buys with a coupon and cancels |

---

## 5. Project structure

```
BookNest/
├── CODE/                       Laravel application
│   ├── app/
│   │   ├── Http/Controllers/   Auth/RegisterController, Auth/LoginController, ProfileController, CartController,
│   │   │                       CouponController, CheckoutController, OrderController, HomeController (temporary)
│   │   ├── Http/Requests/      CheckoutRequest (checkout form validation)
│   │   ├── Services/           CartService, PricingService, CouponService, OrderService, PaymentService
│   │   ├── Models/             User, Category, Product, Coupon, Order, OrderItem
│   │   ├── Exceptions/         CartException, CouponException, OrderException (customer-safe error messages)
│   │   └── Support/            Money (VND formatting), Phone (+84 normalisation), CartSummary (price breakdown object)
│   ├── config/booknest.php     Delivery fee, max quantity per item, payment methods
│   ├── database/
│   │   ├── migrations/         users profile fields, categories, products, coupons, orders, order_items
│   │   ├── seeders/            User, Category, Product, Coupon seeders (+ DatabaseSeeder)
│   │   └── factories/          User, Category, Product, Coupon, Order (used by the tests)
│   ├── resources/views/        layouts/, partials/, auth/, profile/, cart/, checkout/, orders/, shop/
│   ├── public/css, public/js   booknest.css, booknest.js (AJAX for cart and coupon)
│   ├── routes/web.php          All customer routes
│   ├── tests/                  Feature/ (CustomerFlowTest and earlier tests), Unit/
│   ├── phpunit.xml             Test configuration (in-memory SQLite)
│   └── .env.example            Environment template (copy to .env)
├── DATABASE/                   Database design files / SQL scripts
├── DATA/                       Sample data
├── DOCUMENTATION/              SETUP.md, STUDENT_INFO.md, CUSTOMER_FLOW.md (TEST_CASES.xlsx and FINAL_REPORT.pdf are added at submission)
├── VIDEO/DEMO_LINK.txt         YouTube demo link
└── README.md
```

`CODE/README.md` is the standard Laravel readme and `CODE/AGENTS.md` / `CODE/CLAUDE.md` are Laravel's AI-assistant guideline files;
this root `README.md` is the project documentation.

---

## 6. Customer flow

**Responsible member:** Huynh Thanh Tu

**Scope:** registration, login, profile, order history · cart · guest/member checkout · guest/member pricing ·
coupon validation · delivery fee · simulated payment · order confirmation · customer-side order status.

Also included: cancelling a Pending order (members and guests) and members-only coupons.

Not part of this scope (owned by teammates): product browsing pages (home, categories, search, product detail),
responsive UI design, and the whole administrator area. `HomeController` + `resources/views/shop/home.blade.php`
are only a **temporary** product list so "Add to cart" can be tried; the real product pages replace them.

### 6.1 How it works

```
Product page ──Add to cart──▶ CartController ──▶ CartService (session: product_id => quantity)
                                                      │
Cart page ◀── PricingService.summary() ◀──────────────┘   guest_price or member_price, coupon, delivery fee
   │  apply coupon (members) ──▶ CouponController ──▶ CouponService.validate()
   ▼
Checkout ──▶ CheckoutRequest (validation) ──▶ OrderService.place()  [one DB transaction]
                                                │  lock products · re-check stock · re-price · re-check coupon
                                                │  create order + items · decrement stock
                                                │  PaymentService.charge() · clear cart & coupon
                                                ▼
                               Order confirmation  ──▶  Order status page / My orders
                                                              │  (status Pending only)
                                                              ▼
                                         Cancel order ──▶ OrderService.cancel()  [restore stock, refund if paid]
```

Key rules:

| Topic | Rule |
|---|---|
| Registration | Name, email (unique), Vietnamese phone, optional address, password of at least 8 characters with letters and numbers (with a confirmation field). Every new account gets the `customer` role; admins come only from the seeders / admin area. The new member is logged in immediately and keeps the guest cart. |
| Login and logout | Wrong email or password shows one generic message. Login is limited to 10 attempts per minute. Logging in keeps the cart and switches prices to member prices. Logging out clears the whole session (cart and coupon), and a guest who opened a member page is sent back to it after logging in. |
| Profile | A member edits name, email (still unique), phone and delivery address; the address pre-fills checkout. The role cannot be changed here. |
| Guest vs member price | `Product::priceFor($user)` returns `member_price` for a logged-in user and `guest_price` otherwise. The cart session stores only `product_id => quantity`, so the price is always computed from the database and is identical in cart, checkout and order. The unit price paid is saved in `order_items.price`, so later price changes do not alter old orders. A guest sees how much they would save by logging in. |
| Stock and quantity | A cart line holds at most 20 copies and never more than the stock. Checked when adding, when changing the quantity, when opening checkout, and again inside the order transaction (row lock) so two buyers cannot take the last copies. Stock is decremented when the order is placed. Inactive or out-of-stock books cannot be added; a book that becomes inactive is silently dropped from the cart. |
| Coupon | **Members only**: guests are asked to log in or register. One coupon per order and **one use per member** (identified by account, or by the email on earlier orders; cancelled orders do not count). The code (case-insensitive) must exist, be active, and the subtotal must reach `min_order_value`. Discount = fixed `discount_value` (VND), never more than the subtotal. It is re-validated whenever the cart changes (an invalid coupon is removed with a message) and again at checkout, including against the delivery email. |
| Delivery fee | Fixed 30.000 ₫ for any non-empty cart, 0 for an empty cart (`config/booknest.php`, env `BOOKNEST_DELIVERY_FEE`). |
| Total | `subtotal − discount + delivery_fee` (derived value, not stored – `Order::total_price`, as in the ERD). |
| Guest checkout | Needs only name, email, phone, address (at least 10 characters) and a payment method. The order has `user_id = NULL`. Delivery details are saved in the order's `guest_*` columns for guests **and** members (members get the form pre-filled from their profile and may deliver elsewhere). Prices, status and payment status are never taken from the browser. |
| Phone numbers | Vietnamese format: `0` + 9 digits, `84` + 9 digits or `+84` + 9 digits; spaces, dots, dashes and brackets are ignored. Always stored as `+84xxxxxxxxx`. A phone number may be shared between accounts. |
| Simulated payment | Methods: cash on delivery (`Unpaid`), card (simulated) and e-wallet (simulated) – both marked `Paid` immediately. If a `Paid` order is cancelled it becomes `Refunded`. No real gateway is called. |
| Order status | `Pending → Confirmed → Shipping → Completed`, or `Cancelled`. New orders start as `Pending`; the administrator changes the status and customers see it on the order page (with a progress timeline). |
| Cancelling an order | A member (own orders) or a guest (order placed or looked up in this session) can cancel while the status is `Pending`. The books go back into stock and a simulated `Paid` payment becomes `Refunded`. From `Confirmed` onwards only the administrator handles the order. |
| Who can open an order | A member: only their own orders. A guest: orders placed in the current session, or any order looked up on **Track order** with reference (e.g. `BN-000012`) + email. Anything else returns 404, and the lookup gives the same error for a wrong reference or a wrong email (limited to 10 tries per minute). |
| AJAX | Add to cart, change quantity, remove item and apply/remove coupon work through `fetch` (`public/js/booknest.js`) and update prices without reloading. All of them also work without JavaScript. |
| Validation and messages | Server-side validation with clear messages (e.g. Vietnamese phone format, password rules, delivery address length); success, error, empty-cart and empty-history states are shown. |

### 6.2 Routes

| Method & URL | Access | Purpose |
|---|---|---|
| `GET /` | everyone | Temporary product list (placeholder for the product pages) |
| `GET/POST /register` | guest | Register a member account |
| `GET/POST /login` (10/min), `POST /logout` | guest / member | Log in, log out |
| `GET/PUT /profile` | member | Name, email, phone, delivery address |
| `GET /orders` | member | Order history |
| `GET /orders/{id}` | owner or session | Order details and status |
| `GET /orders/{id}/confirmation` | owner or session | Order confirmation after checkout |
| `POST /orders/{id}/cancel` | owner or session | Cancel a Pending order |
| `GET /orders/track`, `POST /orders/track` (10/min) | everyone | Guest order-status lookup (reference + email) |
| `GET /cart` | everyone | View the cart and price breakdown |
| `POST /cart/add/{product}` | everyone | Add a book |
| `PATCH/DELETE /cart/items/{product}` | everyone | Change quantity / remove |
| `POST/DELETE /cart/coupon` | members (guests get a "please log in" message) | Apply / remove a coupon |
| `GET/POST /checkout` | everyone | Delivery information, payment method, place order |

### 6.3 Database tables used by this flow

| Table | Main columns |
|---|---|
| `users` | id, name, email, phone, address, password, `role` (`customer` / `admin`) |
| `categories` | id, name, slug, description, is_active |
| `products` | id, category_id, name, author, description, `guest_price`, `member_price`, stock, image_url, status |
| `coupons` | id, code, `discount_value`, `min_order_value`, is_active |
| `orders` | id, user_id (nullable), coupon_id (nullable), guest_name/email/phone/address, subtotal, discount, delivery_fee, status, payment_method, payment_status |
| `order_items` | id, order_id, product_id, quantity, price (unit price at purchase) |

`order_items.product_id` uses `restrict`, so a product that appears in an order cannot be deleted (the admin area should
warn about this). Prices are whole VND amounts. Laravel's `sessions` table stores the cart and coupon of every visitor.

### 6.4 Integration notes for teammates
- **Database / admin owner:** the migrations above and the seeders follow the team ERD (primary keys are Laravel's `id`).
  If you already have your own migrations for the same tables, keep one version only. The admin area changes
  `orders.status` / `payment_status`; the customer pages already display them. When an administrator cancels an order,
  the admin code must also return the books to stock (customer cancellation in `OrderService::cancel()` already does).
- **UI owner:** replace `HomeController` and `shop/home.blade.php`. `layouts/app.blade.php` and `public/css/booknest.css`
  are a basic shared layout; keep `@yield('content')`, the flash partial and the cart badge when restyling.
  On product cards / the detail page use `@include('partials.add-to-cart', ['product' => $product])`.

### 6.5 Known limitations
- Payment is simulated; there is no password change/recovery or email sending.
- Customers can cancel only `Pending` orders; later changes are handled by the administrator.
- A guest's order list is not saved across sessions; guests use **Track order** to come back to an order.
- Discount codes are for members only; guests see the member price and a hint to register.

---

## 7. Git workflow and project coordination

**Repository:** <https://github.com/huynhtu30082006-alt/BookNest-Online-Bookstore-E-commerce-Website> ·
**Git coordinator:** Huynh Thanh Tu

### 7.1 Branches and commits
- `main` always contains code that installs and runs (`php artisan migrate:fresh --seed` and `php artisan test` succeed).
- Work happens on a short-lived branch per task, named `feature/<area>-<short-name>` (for example
  `feature/customer-cart`, `feature/admin-orders`, `feature/ui-product-detail`) or `fix/<short-name>`.
- Commit messages start with a type: `feat:`, `fix:`, `test:`, `docs:`, `refactor:` or `chore:`
  (for example `feat: apply coupon on the cart page`).
- Open a pull request into `main`; at least one other member reviews it, and the author runs `php artisan test` first.
- Update your branch from `main` (`git pull origin main`) before merging, and never force-push to `main`.

### 7.2 Who owns what
| Area | Owner | Main files |
|---|---|---|
| Customer flow and business logic | Huynh Thanh Tu | `app/Services`, `app/Http/Controllers` (auth, profile, cart, coupon, checkout, orders), `resources/views` (auth, profile, cart, checkout, orders), `tests/` |
| Database and administrator area | Truong Ho Anh Thu | `database/`, `DATABASE/`, `DATA/`, the admin controllers and views, report writing |
| UI and product browsing | Tran Van Linh | `resources/views/layouts`, `shop/`, `public/css`, `public/js`, product pages |

Shared files (`routes/web.php`, `resources/views/layouts/app.blade.php`, migrations) are changed through a pull request
so that the other members see the change.

### 7.3 What must never be committed
- `CODE/.env` (personal passwords) – commit only `.env.example` with empty `APP_KEY` and `DB_PASSWORD`.
- `CODE/vendor/` and `CODE/node_modules/` – restored with `composer install`.
- Editor history such as the VS Code Local History folder `.history/` and IDE folders (`.idea/`, `.vscode/`).
- Local SQLite files, logs and `.phpunit.result.cache`.

`CODE/.gitignore` already ignores most of these; keep a `.gitignore` at the repository root as well so that folders
next to `CODE/` (for example `.history/`) are ignored too. The test `test_repo_git_does_not_track_secrets_dependencies_or_editor_history`
in `CustomerFlowTest` fails if one of them is tracked.

### 7.4 Before you submit or merge
1. `cd CODE && composer install && php artisan migrate:fresh --seed` works on a clean PostgreSQL database.
2. `php artisan test` passes.
3. `README.md` and `DOCUMENTATION/` match the code (routes, rules, demo accounts).
4. `DOCUMENTATION/STUDENT_INFO.md` lists the repository link and every member; `VIDEO/DEMO_LINK.txt` contains the demo link.

# BIT-Y3-G1-224016016-LaundrySystem
# Online Laundry Service System

A web-based laundry service where customers schedule pickups and track orders, staff process and deliver them, and administrators manage everything. Built with **only** HTML5, CSS3, vanilla JavaScript, core PHP (PDO) and MySQL: no frameworks.

## Main features

- **Customers:** register, log in, update profile, view item prices, schedule a pickup with several items, automatic total, order history, status tracking bar, cancel pending orders.
- **Staff:** dashboard, assigned orders with customer address/phone, confirm pickup, update processing stages, mark out for delivery, confirm delivery, search/filter, order history.
- **Administrators:** dashboard statistics and revenue, full CRUD on customers and orders (with items), register/edit/delete user accounts, assign staff, status reports, **live order search** (Fetch API + JSON).
- **Security:** `password_hash()` / `password_verify()`, PDO prepared statements, `htmlspecialchars()` output escaping, CSRF tokens (`random_bytes()`), session ID regeneration, role-based access control, server-side validation, workflow-checked status changes, order ownership checks.

## Technologies

HTML5, CSS3, vanilla JavaScript (DOM, events, Fetch API), PHP 7.4+ (sessions, includes, PDO, simple MVC), MySQL 8.0.16+ or MariaDB 10.2+ (CHECK constraints must be enforced).

## System requirements

PHP 7.4 or newer with the `pdo_mysql` extension, and MySQL 8.0.16+/MariaDB 10.2+. XAMPP, WAMP or Laragon provide everything.

## Installation

1. Copy the project folder somewhere on your computer (e.g. `C:\xampp\htdocs\online-laundry-system`).
2. Start Apache and MySQL (XAMPP control panel), or just MySQL if you use PHP's built-in server.
3. **Create the database and tables** (this creates `online_laundry_db`; it drops an existing one with the same name):
   ```
   mysql -u root -p < database/schema.sql
   ```
   (phpMyAdmin: *Import* tab, choose `database/schema.sql`.)
4. **Load the sample data:**
   ```
   mysql -u root -p < database/seed.sql
   ```
5. **Database credentials:** `app/config/database.php` defaults to host `127.0.0.1`, user `root`, empty password. For other credentials create `app/config/database.local.php` (it is git-ignored):
   ```php
   <?php
   return ['host' => '127.0.0.1', 'name' => 'online_laundry_db', 'user' => 'your_user', 'pass' => 'your_password'];
   ```
   If the database already exists, import `database/migrations/001_password_resets.sql` to add the recovery table without recreating it.
6. **Start the application** (from the project folder):
   ```
   php -S localhost:8000 -t public
   ```
   then open <http://localhost:8000>. With XAMPP/Apache instead, open `http://localhost/online-laundry-system/public/` (the URL prefix is detected automatically; if styles still do not load, set `base_url` in `app/config/app.php` to e.g. `/online-laundry-system/public`). In production the web server's document root must be the `public` folder so `app/` and `database/` are not reachable.



## Demonstration credentials for testing

| Role          | Email             | Password |

| Administrator | ishimwe@gmail.com | `Ishimwe@12` |
| Staff         | alice@gmail.com   | `Alice@12`   |
| Customers     | moussa@gmail.com  | `Moussa@12`  |

These are demo passwords for local testing.

## Project structure

```
   Existing installations can add the password recovery table without recreating the database by importing `database/migrations/001_password_resets.sql`.
app/config      database.php (PDO), app.php (settings), items.php (item types and prices)
app/models      User, Customer, Order, OrderItem  (all SQL lives here)
app/controllers Auth, Dashboard, Customer, Order, User
app/views       layouts/, partials/, auth/, public/, admin/, staff/, customer/
app/helpers     auth.php, csrf.php, validation.php, view.php, order_status.php
public          web root: page entry points, api/, assets/ (css, js, images)
database        schema.sql, seed.sql
tests           test-plan.md
```

## Design notes

- **Four domain tables + `users`.** The laundry item catalogue is a PHP array (`app/config/items.php`) rather than a fifth table. The price is copied to `order_items.unit_price` when an order is placed, so old orders never change.
- **Order total** is calculated in PHP from the items inside a database transaction (create and update).
- **Dashboard groups:** Pending = `pending`; Processing = `confirmed`, `picked_up`, `processing`; Completed = `ready_for_delivery`, `out_for_delivery`; Delivered = `delivered`. Revenue excludes cancelled orders.
- **Workflow:** Pending → Confirmed → Picked Up → Processing → Ready for Delivery → Out for Delivery → Delivered. Pending/Confirmed orders may also be cancelled (customers: pending only; staff cannot cancel).
- **Delete behaviour:** deleting a customer deletes their account, orders and items (foreign key cascade; the admin sees a confirmation). Deleting a staff user only un-assigns their orders.


## Limitations

- Password reset links are sent with PHP `mail()` and expire after one hour. Configure PHP's mail transport and set `reset_email_from` in `app/config/app.php` to a sender address accepted by that transport. Local XAMPP installations commonly need SMTP configured before reset emails can be delivered.
- No email verification or login rate limiting.
- Payment is not processed; totals are for recording only.
- Item prices are changed by editing `app/config/items.php`.
- Customers cannot edit an order after submitting it (they can cancel pending ones; admins can edit).
- Automated tests are not included; testing is manual per the test plan.

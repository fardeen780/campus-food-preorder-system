# Campus Food Pre-Order System

Online food pre-order system for a university campus cafeteria.
CSE-451 Software Engineering, Independent University, Bangladesh (IUB).

**Live demo:** YOUR_LIVE_URL
(The free hosting plan sleeps after 15 minutes of inactivity. The first load can take about a minute.)

## Demo accounts

| Role | Email |
|---|---|
| Student | student1@iub.test, student2@iub.test |
| Cafeteria staff (JMI outlet) | staff@iub.test |
| Manager | manager@iub.test |
| System administrator | admin@iub.test |

The password is provided separately with the submission.

## Features

**Students**
- Registration, login, food menu with search and filters, cart
- Checkout with pickup time slots (one order and one food token per outlet)
- Order history and cancellation until 30 minutes before pickup (enforced on the server)
- Student-to-student food token transfer: the receiver must accept or reject, and requests expire
- Sent and received transfer requests, in-app notifications with unread count
- Outstanding dues on the dashboard

**Cafeteria staff**
- Order queue for their own outlet, status updates (Pending, Confirmed, Preparing, Ready)
- Token verification and collection, recording cash, bKash or Nagad payments

**Manager**
- Food items and categories (add, edit, price, availability, remove, restore)
- All orders with filters, outstanding dues by student

**System administrator**
- User accounts: create, edit, assign roles and outlets, deactivate, reset passwords
- Activity log for security monitoring

## Key business rules
- Cancellation deadline is always pickup time minus 30 minutes.
- Token transfers never change ownership until the receiver accepts.
- The student who placed an order stays financially responsible for it, even after a token transfer.
- Order status and payment status are separate. `orders.paid_amount` is always the sum of successful payments, updated in the same database transaction as the payment.
- Role-based access is enforced by backend middleware on every route.

## Tech stack
Laravel 13, PHP 8.3 or newer, PostgreSQL, Blade, Bootstrap 5.

## Local setup
1. Clone the repository and open the folder.
2. Install dependencies: `composer install`
3. Copy the environment file and create the app key:
   - macOS/Linux: `cp .env.example .env`
   - Windows PowerShell: `Copy-Item .env.example .env`
   - then `php artisan key:generate`
4. Create a PostgreSQL user and database (in `psql` as the postgres user):
   ```sql
   CREATE ROLE campus_app WITH LOGIN PASSWORD 'your_password';
   CREATE DATABASE campus_food_preorder OWNER campus_app ENCODING 'UTF8';
   ```
5. Put the same password in `DB_PASSWORD` in `.env`.
6. Create the tables and demo data: `php artisan migrate --seed`
7. Start the app: `php artisan serve` and open http://127.0.0.1:8000

For local development only, the demo accounts use the password `password`.

## Deployment
Deployed on Render with the included `Dockerfile` and a Render PostgreSQL database. Production settings come from environment variables (`APP_KEY`, `DB_URL`, `DEMO_PASSWORD`, and others), never from the repository.

## Not implemented (future work)
- Automated test suite, PHPStan and Postman API collection
- Manager sales reports
- Student payment history page and profile editing
- Admin screen for system settings (the settings table and defaults exist)
- Image upload for food items (emoji icons are used)

## Design reference
`design-reference/` holds the original HTML prototype, used as a visual reference.
# Campus Food Pre-Order System

Online food pre-order system for a university campus cafeteria.
CSE-451 Software Engineering, Independent University, Bangladesh (IUB).

## Features (planned)
- Four roles: Student, Cafeteria Staff, Manager, System Administrator
- Food menu, cart, pre-orders with pickup scheduling
- Order cancellation until 30 minutes before pickup
- Student-to-student food token transfer (recipient must accept)
- In-app notifications
- Payments and outstanding dues tracking

## Tech stack
Laravel 13, PHP 8.3+, PostgreSQL 17+, Blade, Bootstrap, Vite

## Requirements
- PHP 8.3 or newer, with extensions: pdo_pgsql, pgsql, mbstring, openssl, curl, fileinfo, intl, zip, gd
- Composer 2
- Node.js and npm
- PostgreSQL

## Local setup
1. Clone the repository:
   ```bash
   git clone https://github.com/tabassumhmm/campus-food-preorder-system.git
   cd campus-food-preorder-system
   ```
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Create your environment file and app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   On Windows PowerShell use `Copy-Item .env.example .env`.
4. Create the database in PostgreSQL (run in `psql` as the postgres user):
   ```sql
   CREATE ROLE campus_app WITH LOGIN PASSWORD 'your_password';
   CREATE DATABASE campus_food_preorder OWNER campus_app ENCODING 'UTF8';
   ```
5. Put the same password in `DB_PASSWORD` inside `.env`.
6. Verify the connection and start the app:
   ```bash
   php artisan db:show
   php artisan serve
   ```

## Design reference
`design-reference/` holds the original HTML prototype (visual reference only).

## Team workflow
- Never commit `.env` or secrets.
- Work on a feature branch, then open a pull request into `main`.
- Use clear commit messages, e.g. `feat: add food menu page`.
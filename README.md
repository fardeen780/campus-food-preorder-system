# Campus Food Pre-Order System

A full-stack Laravel application for managing campus cafeteria food ordering, checkout, pickup operations, and role-based staff administration.

Developed as a software engineering project for Independent University, Bangladesh (IUB), this system models a realistic university food service workflow where students place food orders ahead of time, cafeteria staff process the queue, managers manage menu pricing and availability, and administrators control user access and account activity.

## Why this project exists

Campus food ordering is a common operational pain point: students need a fast, reliable way to pre-order meals, cafeteria staff need a queue and token-based fulfillment process, and managers need a way to manage menu items and monitor dues.

This application brings those workflows into a single system with strong business rules and role-based authorization, making it suitable for academic, demo, and prototype deployment scenarios.

## Key features

### Students
- Register and sign in securely
- Browse outlet menus and food items
- Search and filter menu options by category and outlet
- Add items to a cart and place a pre-order
- Select pickup times and order slots
- View order history and current order status
- Cancel orders before the legal cutoff time
- Transfer food tokens to other students with approval flow
- Receive and manage transfer requests
- View notifications and outstanding dues

### Cafeteria staff
- View incoming orders for their assigned outlet
- Update order status through a processing flow
- Verify food tokens during pickup
- Record payment collection for cash, bKash, and Nagad
- Handle order fulfillment and queue management efficiently

### Managers
- Create, update, and disable food items
- Manage categories and availability
- Review all orders and filter by status or outlet
- Monitor student dues and economic activity across orders
- Maintain outlet-level menu operations

### System administrators
- Manage user accounts
- Assign roles and outlet access
- Activate or deactivate accounts
- Reset passwords and review account changes
- Track user activity through the activity log

## Business logic built into the app

The system is not just a basic CRUD app. It includes important operational rules that make it realistic:

- Order cancellation is blocked after the allowed deadline, which is calculated as pickup time minus 30 minutes.
- Food token transfers are not finalized until the receiving student accepts the request.
- Ownership and financial responsibility remain tied to the original student who placed the order, even after a transfer.
- Order status and payment status are tracked separately to prevent confusion in payment reconciliation.
- Backend access control enforces role-based restrictions on routes and operations.

## Tech stack

- Laravel 13
- PHP 8.3+
- PostgreSQL
- Blade templates
- Bootstrap 5
- Vite
- Composer + npm

## Project structure

```text
campus-food-preorder-system/
├── app/                     # Application logic, controllers, models, enums, policies
├── bootstrap/               # Laravel bootstrap files
├── config/                  # Application configuration
├── database/
│   ├── factories/           # Database factories
│   ├── migrations/          # Schema migrations
│   └── seeders/             # Demo data and seeded users
├── design-reference/        # HTML prototype / design reference
├── public/                  # Public web assets and entry point
├── resources/               # Blade views, CSS, JS, and frontend assets
├── routes/                  # Route definitions
├── storage/                 # Runtime files
├── tests/                   # Automated tests (if added later)
├── .env.example             # Sample environment configuration
├── artisan                  # Laravel CLI entry point
├── composer.json            # PHP dependencies and scripts
├── Dockerfile               # Deployment container setup
├── package.json             # Frontend build scripts
├── phpunit.xml              # Test configuration
├── vite.config.js           # Vite configuration
├── README.md                # Project documentation
└── .gitignore
```

## Prerequisites

Before running the project locally, make sure you have:

- PHP 8.3 or newer
- Composer
- Node.js and npm
- PostgreSQL 14+ (or a compatible version)

## Local setup

1. Clone the repo:

```bash
git clone https://github.com/tabassumhmm/campus-food-preorder-system.git
cd campus-food-preorder-system
```

2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Copy the sample environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

5. Configure the database in `.env`.

Example:

```env
APP_NAME="Campus Food Pre-Order"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=campus_food_preorder
DB_USERNAME=campus_app
DB_PASSWORD=your_password

DEMO_PASSWORD=password
```

6. Create the PostgreSQL database and user:

```sql
CREATE ROLE campus_app WITH LOGIN PASSWORD 'your_password';
CREATE DATABASE campus_food_preorder OWNER campus_app ENCODING 'UTF8';
```

7. Run the database migrations and seed demo data:

```bash
php artisan migrate --seed
```

8. Build frontend assets (or run Vite in development mode):

```bash
npm run build
```

9. Start the app:

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

## Demo accounts

The database seeder creates demo accounts for all roles. The password is controlled by the `DEMO_PASSWORD` environment variable, and defaults to `password` in local development.

| Role | Email | Default password |
|---|---|---|
| Student | student1@iub.test | Same as `DEMO_PASSWORD` (default: `password`) |
| Student | student2@iub.test | Same as `DEMO_PASSWORD` (default: `password`) |
| Cafeteria staff (JMI outlet) | staff@iub.test | Same as `DEMO_PASSWORD` (default: `password`) |
| Manager | manager@iub.test | Same as `DEMO_PASSWORD` (default: `password`) |
| System administrator | admin@iub.test | Same as `DEMO_PASSWORD` (default: `password`) |

Important: in production, the seeder rejects weak demo passwords. Always set a strong value such as `DEMO_PASSWORD=MyCampusFood2025!` before seeding in non-local environments.

## Environment variables

The app depends on these key environment values:

- `APP_KEY`: required for Laravel encryption
- `APP_URL`: base URL for the application
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: PostgreSQL settings
- `DEMO_PASSWORD`: password used for seeded demo accounts
- `APP_DEBUG`: enable or disable detailed error output

## Deployment

This project includes a `Dockerfile` and can be deployed to services such as Render.

Typical production deployment setup:

- Set `APP_KEY`
- Set `APP_ENV=production`
- Set `APP_URL` to the deployment domain
- Set a secure PostgreSQL connection string or environment variables
- Set `DEMO_PASSWORD` to a strong password for seeded accounts
- Run `php artisan migrate --force --seed` after deployment if the environment uses demo data

Do not commit secrets or database credentials into the repository.

## Scripts

Available Laravel scripts include:

```bash
composer install
npm install
php artisan migrate --seed
php artisan serve
php artisan test
npm run build
```

## Design reference

A visual prototype and design reference are included in:

```text
design-reference/
```

This can be useful for understanding the intended UX and interface structure during implementation and review.

## Future improvements

The current project is a strong working prototype, but there are several natural next steps:

- Add automated tests for core business workflows
- Introduce a formal API layer and Postman collection
- Add sales and analytics reports for managers
- Add student payment history and profile editing
- Improve admin system settings management
- Add image upload support for food items and menu content

## License

This project is distributed under the MIT license unless otherwise specified in the repository.

## Contributing

Contributions are welcome. If you would like to improve the app:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run the relevant validation checks
5. Open a pull request with a clear summary

## Contact

For questions or collaboration, use the repository issue tracker or contact the project maintainer through the repository profile.

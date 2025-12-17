# Pharmacy Management System

A comprehensive pharmacy management system built with Laravel 12, featuring point-of-sale (POS), inventory management, purchase orders, sales tracking, and detailed reporting.

## Features

- **Point of Sale (POS)** - Fast and intuitive sales interface with barcode scanning support
- **Inventory Management** - Track products, categories, batches, and stock levels
- **Purchase Orders** - Manage supplier orders and stock replenishment
- **Sales Management** - Process transactions, hold sales, and handle returns
- **Purchase Returns** - Manage product returns to suppliers
- **Reports & Analytics** - Daily sales, profit/loss, sales trends, expiring products, and more
- **User Management** - Role-based access control with customizable permissions
- **Export Capabilities** - Export reports to PDF, Excel, and CSV formats
- **Database Backups** - Built-in backup management

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm/yarn
- MySQL or SQLite

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd pharmacy
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Set up your database in `.env` file

5. Run migrations and seeders:
```bash
php artisan migrate
php artisan db:seed
```

6. Build frontend assets:
```bash
npm run build
```

## Quick Setup

Alternatively, run the setup script:
```bash
composer setup
```

## Development

Start the development server:
```bash
composer dev
```

This runs the Laravel server, queue listener, and Vite dev server concurrently.

## Testing

```bash
composer test
```

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade templates, Tailwind CSS, Vite
- **Authentication**: Laravel Breeze
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel
- **Testing**: Pest PHP

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

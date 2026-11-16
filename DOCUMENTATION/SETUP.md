# Setup Guide 
Explain how to install, configure, and run the application

## Requirements
- PHP 8.4+, Composer, Laravel Herd (or `php artisan serve`)
- Database: PostgreSQL (create an empty database named `booknest`)
- Git

## Install
```bash
cd CODE
composer install
cp .env.example .env   # then set DB_PASSWORD
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve      # or open http://booknest.test with Herd
```
No npm build is needed (Bootstrap is loaded from a CDN).

## Test accounts
- Member: lan@example.com / Password123
- Admin: admin@booknest.test / Password123
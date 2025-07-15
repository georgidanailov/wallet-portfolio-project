# Before running this project, make sure you have the following installed:

PHP 8.1 or higher
MySQL 8.0 or higher
Symfony CLI (optional but recommended)

# Setup guide:
cd wallet-portfolio

# Install dependencies
composer install

# Setup MySQL in .env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/portfolio_db?serverVersion=8.0"

# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (sample data)
php bin/console doctrine:fixtures:load

# Run the application
php -S localhost:8000 -t public/

# Technical Details
Framework: Symfony 6.x
Database: MySQL with Doctrine ORM
Frontend: Twig templating with Bootstrap
Architecture: Following SOLID principles with dependency injection
# Verkoo API Restaurant

API for Verkoo restaurant module.

## Installation

```bash
git clone git@github.com:verkoo/api-restaurant.git
cd api-restaurant && composer install
mv .env.example .env
// fill .env with your DB Settings
php artisan migrate
php artisan key:generate
php artisan db:seed
touch public/log/log.txt
```

* Boot up a server. If you are using Laravel Valet visit api-restaurant.test

Yoy can sign in with admin/admin (please dont share this secure credentials)

If you are in a Mac, you need the right binaries for wkhtmltopdf library
```bash
mv bin/wkhtmltopdf-amd64 vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64 
```

If you want to run the test suite
* create a database with the same name of your main one but ending in '_test'
```bash
php artisan migrate --database=mysql_test
php vendor/bin/phpunit
```
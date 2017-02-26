# festigeekAPI
Back-end system of the geek association "Festigeek".

# Getting Started
- Install PHP ^7.0 and MariaDB ^10.1  
*(we recommend Xampp v7.0 if you are not sure)*
- Connect to your MariaDB (with phpmyadmin, HeidiSQL or even mysql client if you are a true warrior).
- Create a user called `laravel` and grant it all permissions for a `festigeekapi` database.  
  *(you maybe have to create it before)*
- In project folder, run `php composer self-update` and `php composer install`
- You can start the server with `php artisan serve --port=80` (be sure to have a free port 80)

# festigeekAPI
Back-end system of the geek association "Festigeek".

# Getting Started
- Install PHP ^7.0 and MariaDB ^10.1  
*(we recommend Xampp v7.0 if you are not sure)*
- Connect to your MariaDB (with phpmyadmin, HeidiSQL or even mysql client if you are a true warrior).
  - Create a database `festigeekapi`.
  - Create a user called `laravel` and grant it all permissions for the `festigeekapi` database.  
- In project folder, run `php composer self-update` and `php composer install`
- You can start the server with `php artisan serve --port=80` (be sure to have a free port 80)

# Hints
- If the `artisan` command is not available, you can use PHP itself. From PHP7, you can start a light webserver for Laravel with the command `php -S localhost:8000 -t public`. Then go on your browser and look at what Laravel have to say.
> "It's part of the fun"

# LARAVEL KANBAN

Kanban package for Laravel Projects

<br>

## Installation

Use the following commands to install

```bash
composer require xguard/kanban
php artisan migrate
php artisan vendor:publish --provider="Xguard\LaravelKanban\LaravelKanbanServiceProvider" --force
```
Use the following command to create an admin. It will prompt you for an existing email from users table.

```bash
php artisan kanban:create-admin
```
You can now go to the **/kanban** path to use the package. You must first login to access this url. 

<br>

## Develpment 

**Follow these steps to make modifications to the package**

**1:** Firstly, download and drag the laravel-kanban folder inside your package folder at root level. 
Create a "package" folder if you don't have one.


**2:** Then, add line of code in the psr-4 of your root composer.json
```
"psr-4": {
    //...
    "Xguard\\LaravelKanban\\": "package/laravel-kanban/src/"
},
```
**3:** Add the Phone Scheduler Service provider to the config/app.php

```php
return [
    //...
    "providers" => [
        //...
        Xguard\LaravelKanban\LaravelKanbanServiceProvider::class,
    ]
];

```


**4:** run this command
```bash
composer dump-autoload
```

**5:** Navigate to the laravel-kanban package folder in your command line and perform the following commands:
```bash
composer install
npm install
npm run dev
```

**6:** Return to the  and publish the package with the following command:
```bash
php artisan vendor:publish --provider="Xguard\LaravelKanban\LaravelKanbanServiceProvider" --force
```

**7:** To run package migrations
```bash
php artisan migrate --path=package/laravel-kanban/src/database/migrations
```

**7:** To run seeder for testing
```bash
php artisan db:seed --class="Xguard\LaravelKanban\database\seeds\EmployeeSeeder"
```

<br>

## License
Let's go ahead and say we make it open source? Liscensed under the [MIT license](https://choosealicense.com/licenses/mit/)

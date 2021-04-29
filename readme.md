# Phone Scheduler

This is a phone scheduling application that generates data to be fetched by twilio. 

## Installation

Use the following commands to install

```bash
composer require xguard/kanban
php artisan migrate
php artisan vendor:publish --provider="Xguard\PhoneScheduler\PhoneSchedulerServiceProvider" --force
```
Use the following command to create an admin. It will prompt you for basic info including an ERP email.

```bash
php artisan phone-scheduler:create-admin
```
You can now go to the **/phone-schduler** path to use the pacakge. You must first login to the ERP. 

To get a formatted json of the phone scheduler you can use the following API endpoint path:

```bash
/api/formatted-phone-line-data/{id}
```
To get current available dispatcher via a "level" you can use the following API endpoint path:

```bash
/api/get-available-agent/{id}/{level}
```


## Develpment 

**Follow these steps to make modifications to the package**

**1:** Firstly, download and drag the phone-scheduler folder inside your package folder at root level. 
Create a "package" folder if you don't have one.


**2:** Then, add line of code in the psr-4 of your root composer.json
```json
"psr-4": {
    //...
    "Xguard\\PhoneScheduler\\": "package/kanban/src/"
},
```
**3:** Add the Phone Scheduler Service provider to the config/app.php

```php
return [
    //...
    "providers" => [
        //...
        Xguard\PhoneScheduler\LaravelKanbanServiceProvider::class,
    ]
];

```

**4:** Navigate to the phone-scheduler package folder in your command line and perform the following commands:
```bash
composer install
npm install
npm run dev
```

**5:** Return to the  and publish the package with the following command:
```bash
php artisan vendor:publish --provider="Xguard\PhoneScheduler\PhoneSchedulerServiceProvider" --force
```

**6:** To run package migrations
```bash
php artisan migrate --path=package/kanban/src/database/migrations
```



## License
Lets go ahead and say we make it open source? Liscensed under the [MIT license](https://choosealicense.com/licenses/mit/)

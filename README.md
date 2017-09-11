laravel-admin-ext/api-tester
============================

Inspired by [laravel-api-tester](https://github.com/asvae/laravel-api-tester).

## Screenshot

![wx20170809-164424](https://user-images.githubusercontent.com/1479100/29112946-1e32971c-7d22-11e7-8cc0-5b7ad25d084e.png)

## Installation

```
$ composer require laravel-admin-ext/api-tester -vvv

$ php artisan vendor:publish --tag=api-tester

```

Then last run flowing command to import menu and permission: 

```
$ php artisan admin:import api-tester
```

Finally open `http://localhost/admin/api-tester`.

## Configuration

`api-tester` supports 3 configuration, open `config/admin.php` find `extensions`:
```php

    'extensions' => [
    
        'api-tester' => [
        
            // route prefix for APIs
            'prefix' => 'api',

            // auth guard for api
            'guard'  => 'api',

            // If you are not using the default user model as the authentication model, set it up
            'user_retriever' => function ($id) {
                return \App\User::find($id);
            },
        ]
    ]

```

License
------------
Licensed under [The MIT License (MIT)](LICENSE).

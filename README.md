API tester for laravel-admin
============================

[![StyleCI](https://styleci.io/repos/99563385/shield?branch=master)](https://styleci.io/repos/99563385)
[![Packagist](https://img.shields.io/packagist/l/laravel-admin-ext/api-tester.svg?maxAge=2592000)](https://packagist.org/packages/laravel-admin-ext/api-tester)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-admin-ext/api-tester.svg?style=flat-square)](https://packagist.org/packages/laravel-admin-ext/api-tester)
[![Pull request welcome](https://img.shields.io/badge/pr-welcome-green.svg?style=flat-square)]()

Inspired by [laravel-api-tester](https://github.com/asvae/laravel-api-tester).

[Documentation](http://laravel-admin.org/docs/#/en/extension-api-tester) | [中文文档](http://laravel-admin.org/docs/#/zh/extension-api-tester)

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

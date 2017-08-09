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

License
------------
Licensed under [The MIT License (MIT)](LICENSE).

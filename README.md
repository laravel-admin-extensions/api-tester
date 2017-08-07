laravel-admin-ext/api-tester
============================

Inspired by [laravel-api-tester](https://github.com/asvae/laravel-api-tester).

## Installation

```
$ composer require laravel-admin-ext/api-tester -vvv

$ php artisan vendor:publish --tag=api-tester

```

Open `app/Providers/AppServiceProvider.php`, and call the `ApiTester::boot` method within the `boot` method:

```php
<?php

namespace App\Providers;

use Encore\Admin\ApiTester\ApiTester;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        ApiTester::boot();
    }
}
```

At last run flowing command to import menu and permission: 

```
$ php artisan admin:import api-tester
```

Finally open `http://localhost/admin/api-tester`.

License
------------
Licensed under [The MIT License (MIT)](LICENSE).

[![Total Downloads](https://poser.pugx.org/jn-jairo/laravel-eloquent-cast/downloads)](https://packagist.org/packages/jn-jairo/laravel-eloquent-cast)
[![Latest Stable Version](https://poser.pugx.org/jn-jairo/laravel-eloquent-cast/v/stable)](https://packagist.org/packages/jn-jairo/laravel-eloquent-cast)
[![License](https://poser.pugx.org/jn-jairo/laravel-eloquent-cast/license)](https://packagist.org/packages/jn-jairo/laravel-eloquent-cast)

# Cast for Laravel Eloquent

This package extends the built-in [attribute casting](https://laravel.com/docs/eloquent-mutators#attribute-casting)
with the [jn-jairo/laravel-cast](https://github.com/jn-jairo/laravel-cast) package.

## Version Compatibility

 Laravel  | Eloquent Cast
:---------|:----------
  5.8.x   | 1.x
  6.x     | 1.x
  7.x     | 2.x
  8.x     | 3.x
  9.x     | 3.x
 10.x     | 3.x

## Installation

You can install the package via composer:

```bash
composer require jn-jairo/laravel-eloquent-cast
```
## Usage

Use the trait `\JnJairo\Laravel\EloquentCast\HasAttributesCast` in a `\Illuminate\Database\Eloquent\Model`.
In the `$casts` property of the model set the attribute name as the key and the type:format as the value.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;

class Foo extends Model
{
    use HasAttributesCast;

    protected $casts = [
        'uuid' => 'uuid',
        'is_admin' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
```

The attribute is casted for a `PHP` type in the getter and/or with a suffix, for a `database` type in the setter, and for a `json` type when serialized to `array`/`json`.

## Example

```php
$foo = new Foo();
$foo->uuid = '72684d25-b173-468d-8d45-2a10b2cc3e9f';
$foo->is_admin = 1;
$foo->created_at = '2000-01-01 00:00:00';

print_r(gettype($foo->uuid));
// string

print_r(get_class($foo->uuid_));
// Ramsey\Uuid\Uuid

var_dump($foo->is_admin);
// bool(true)

print_r($foo->created_at);
Illuminate\Support\Carbon Object
(
    [date] => 2000-01-01 00:00:00.000000
    [timezone_type] => 3
    [timezone] => UTC
)
```

## Configuration

Publish the configuration to `config/eloquent-cast.php`.

```bash
php artisan vendor:publish --provider=JnJairo\\Laravel\\EloquentCast\\EloquentCastServiceProvider
```

In the default configuration the attribute is casted for a `PHP` type in the getter and with the `_` suffix, except for the type `uuid` which is casted only with the `_` suffix,
because to cast a primary/foreign key directly in the getter brakes the relations between the eloquent models.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

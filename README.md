[![Build Status](https://travis-ci.com/jn-jairo/laravel-eloquent-cast.svg?branch=master)](https://travis-ci.com/jn-jairo/laravel-eloquent-cast)
[![Total Downloads](https://poser.pugx.org/jn-jairo/laravel-eloquent-cast/downloads)](https://packagist.org/packages/jn-jairo/laravel-eloquent-cast)
[![Latest Stable Version](https://poser.pugx.org/jn-jairo/laravel-eloquent-cast/v/stable)](https://packagist.org/packages/jn-jairo/laravel-eloquent-cast)
[![License](https://poser.pugx.org/jn-jairo/laravel-eloquent-cast/license)](https://packagist.org/packages/jn-jairo/laravel-eloquent-cast)

# Custom cast for Laravel Eloquent

This package allows you to create custom cast for Laravel Eloquent attributes.

## Requirements

- Laravel Framework >= 5.5

## Installation

You can install the package via composer:

```bash
composer require jn-jairo/laravel-eloquent-cast
```

## Example

Create a `\App\Model` that extends the `\Illuminate\Database\Eloquent\Model` and use the `\JnJairo\Laravel\EloquentCast\HasAttributesCast`.

Then write the cast and uncast methods in the format `castMyCustomTypeAttribute` and `uncastMyCustomTypeAttribute` where `MyCustomType` is the "studly" cased name of the type, in this example `my_custom_type`.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;

class Model extends IlluminateModel
{
    use HasAttributesCast;

    /**
     * Cast my_custom_type attribute.
     *
     * @param mixed  $value      The value to be casted.
     * @param string $format     Optional format.
     * @param bool   $serialized If need to be casted to serialize as array/json.
     * @return mixed
     */
    protected function castMyCustomTypeAttribute($value, $format = '', $serialized = false)
    {
        // The code to cast here
    }

    /**
     * Uncast my_custom_type attribute.
     *
     * @param mixed  $value  The value to be uncasted.
     * @param string $format Optional format.
     * @return mixed
     */
    protected function uncastMyCustomTypeAttribute($value, $format = '')
    {
        // The code to uncast here
    }
}
```

So just use the `\App\Model` instead of the `\Illuminate\Database\Eloquent\Model` and your custom casts will be available.

```php
<?php

namespace App;

use App\Model;

class Foo extends Model
{
    protected $casts = [
        'foo' => 'my_custom_type',
        'bar' => 'my_custom_type:someformat',
    ];
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

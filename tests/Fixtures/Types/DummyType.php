<?php

namespace JnJairo\Laravel\EloquentCast\Tests\Fixtures\Types;

use Illuminate\Support\Str;
use JnJairo\Laravel\Cast\Types\Type;

class DummyType extends Type
{
    /**
     * Cast to PHP type.
     *
     * @param mixed $value
     * @param string $format
     * @return mixed
     */
    public function cast(mixed $value, string $format = ''): mixed
    {
        if (is_null($value) || ! is_string($value)) {
            return null;
        }

        if ($format === 'case') {
            return Str::upper($value);
        }

        return Str::studly($value);
    }

    /**
     * Cast to database type.
     *
     * @param mixed $value
     * @param string $format
     * @return mixed
     */
    public function castDb(mixed $value, string $format = ''): mixed
    {
        if (is_null($value) || ! is_string($value)) {
            return null;
        }

        if ($format === 'case') {
            return Str::lower($value);
        }

        return Str::snake($value);
    }
}

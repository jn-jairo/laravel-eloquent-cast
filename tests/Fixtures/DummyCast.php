<?php

namespace JnJairo\Laravel\EloquentCast\Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

/**
 * @implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes<mixed, mixed>
 */
class DummyCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<array-key, mixed> $attributes
     * @return mixed
     */
    public function get($model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || ! is_string($value)) {
            return $value;
        }

        return Str::studly($value);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<array-key, mixed> $attributes
     * @return mixed
     */
    public function set($model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || ! is_string($value)) {
            return $value;
        }

        return Str::snake($value);
    }

    /**
     * Serialize the attribute when converting the model to an array.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<array-key, mixed> $attributes
     * @return mixed
     */
    public function serialize($model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || ! is_string($value)) {
            return $value;
        }

        return Str::slug($value);
    }
}

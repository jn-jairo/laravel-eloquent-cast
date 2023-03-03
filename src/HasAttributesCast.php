<?php

namespace JnJairo\Laravel\EloquentCast;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JnJairo\Laravel\Cast\Facades\Cast;
use UnitEnum;

trait HasAttributesCast
{
    /**
     * Get the type of cast for a model attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getCastType($key): string
    {
        $type = '';

        $casts = $this->getCasts();

        if (isset($casts[$key])) {
            $type = explode(':', $casts[$key], 2)[0];
        }

        return $type;
    }

    /**
     * Get the format of cast for a model attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getCastFormat(string $key): string
    {
        $format = '';

        $casts = $this->getCasts();

        if (isset($casts[$key])) {
            $typeFormat = explode(':', $casts[$key], 2);

            if (isset($typeFormat[1])) {
                $format = $typeFormat[1];
            }
        }

        return $format;
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castAttribute($key, mixed $value): mixed
    {
        $keyBase = $this->getBaseAttributeName($key);

        $type = $this->getCastType($keyBase);
        $format = $this->getCastFormat($keyBase);

        if ($type) {
            $mode = config('eloquent-cast.mode');
            $suffixOnly = (array) config('eloquent-cast.suffix_only');

            $isSuffix = $key !== $keyBase;
            $isSuffixOnly = in_array($type, $suffixOnly);

            if (
                $mode === 'getter' && ! $isSuffix
                || $mode === 'suffix' && $isSuffix
                || $mode === 'auto' && ($isSuffix || ! $isSuffixOnly)
            ) {
                return Cast::cast($value, $type, $format);
            }
        }

        return $value;
    }

    /**
     * Cast an attribute to a database type.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castDbAttribute(string $key, mixed $value): mixed
    {
        $type = $this->getCastType($key);
        $format = $this->getCastFormat($key);

        return $type ? Cast::castDb($value, $type, $format) : $value;
    }

    /**
     * Cast an attribute to a json type.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castJsonAttribute(string $key, mixed $value): mixed
    {
        $type = $this->getCastType($key);
        $format = $this->getCastFormat($key);

        return $type ? Cast::castJson($value, $type, $format) : $value;
    }

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param string $key
     * @return bool
     */
    public function originalIsEquivalent($key): bool
    {
        if (! array_key_exists($key, $this->original)) {
            return false;
        }

        $attribute = Arr::get($this->attributes, $key);
        $original = Arr::get($this->original, $key);

        if ($attribute === $original) {
            return true;
        } elseif (is_null($attribute)) {
            return false;
        } elseif ($this->isDateAttribute($key) || $this->isDateCastableWithCustomFormat($key)) {
            return $this->fromDateTime($attribute) ===
                $this->fromDateTime($original);
        } elseif (
            $this->hasCast($key)
            && ! $this->isEnumCastable($key)
            && ! $this->isClassCastable($key)
        ) {
            return $this->castAttribute($key, $attribute) === $this->castAttribute($key, $original)
                || $this->castDbAttribute($key, $attribute) === $this->castDbAttribute($key, $original)
                || $this->castJsonAttribute($key, $attribute) === $this->castJsonAttribute($key, $original);
        }

        return is_numeric($attribute) && is_numeric($original)
                && strcmp((string) $attribute, (string) $original) === 0;
    }

    /**
     * Add the casted attributes to the attributes array.
     *
     * @param array<array-key, mixed> $attributes
     * @param array<array-key, mixed> $mutatedAttributes
     * @return array<array-key, mixed>
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes): array
    {
        foreach ($this->getCasts() as $key => $value) {
            if (
                ! array_key_exists($key, $attributes)
                || in_array($key, $mutatedAttributes)
                || $this->isEnumCastable($key)
            ) {
                continue;
            }

            if ($this->isClassCastable($key)) {
                if (method_exists($this, 'serializeClassCastableAttribute')) {
                    if ($this->isClassSerializable($key)) {
                        $attributes[$key] = $this->serializeClassCastableAttribute($key, $attributes[$key]);
                    }
                }
            } else {
                $attributes[$key] = $this->castJsonAttribute($key, $attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * Get the base name for the attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getBaseAttributeName(string $key): string
    {
        $mode = config('eloquent-cast.mode');

        if ($mode === 'auto' || $mode === 'suffix') {
            /**
             * @var string $suffix
             */
            $suffix = config('eloquent-cast.suffix');
            if (Str::endsWith($key, $suffix)) {
                return Str::replaceLast($suffix, '', $key);
            }
        }

        return $key;
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        if (! $key) {
            return null;
        }

        if (
            array_key_exists($key, $this->getAttributes())
            || $this->hasGetMutator($key)
            || $this->isEnumCastable($key)
            || $this->isClassCastable($key)
        ) {
            return $this->getAttributeValue($key);
        }

        $keyBase = $this->getBaseAttributeName($key);

        if (array_key_exists($keyBase, $this->attributes)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return null;
        }

        return $this->getRelationValue($key);
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeValue($key): mixed
    {
        $keyBase = $this->getBaseAttributeName($key);

        $value = $this->getAttributeFromArray($keyBase);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        if (
            $this->isEnumCastable($key)
            && method_exists($this, 'getEnumCastableAttributeValue')
        ) {
            return $this->getEnumCastableAttributeValue($key, $value);
        }

        if ($this->isClassCastable($key)) {
            return $this->getClassCastableAttributeValue($key, $value);
        }

        if ($this->hasCast($keyBase)) {
            return $this->castAttribute($key, $value);
        }

        if (
            in_array($key, $this->getDates())
            && ! is_null($value)
        ) {
            return $this->asDateTime($value);
        }

        return $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, mixed $value): mixed
    {
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        } elseif (
            $this->isEnumCastable($key)
            && method_exists($this, 'setEnumCastableAttribute')
            && (is_null($value) || is_int($value) || is_string($value) || $value instanceof UnitEnum)
        ) {
            if (! is_null($value)) {
                $this->setEnumCastableAttribute($key, $value);
                return $this;
            }
        } elseif ($this->isClassCastable($key)) {
            $this->setClassCastableAttribute($key, $value);
            return $this;
        } elseif ($this->hasCast($key)) {
            $value = $this->castDbAttribute($key, $value);
        }

        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Determine if the given key is cast using a custom class.
     *
     * @param string $key
     * @return bool
     */
    protected function isClassCastable($key): bool
    {
        if (! array_key_exists($key, $this->getCasts())) {
            return false;
        }

        $castType = $this->parseCasterClass($this->getCasts()[$key]);

        if (in_array($castType, static::$primitiveCastTypes)) {
            return false;
        }

        if (class_exists($castType)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the given key is cast using an enum.
     *
     * @param string $key
     * @return bool
     */
    protected function isEnumCastable($key): bool
    {
        if (! array_key_exists($key, $this->getCasts())) {
            return false;
        }

        $castType = $this->getCasts()[$key];

        if (in_array($castType, static::$primitiveCastTypes)) {
            return false;
        }

        if (function_exists('enum_exists') && enum_exists($castType)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding(mixed $value, $field = null): ?Model
    {
        $value = $this->castDbAttribute($field ?? $this->getRouteKeyName(), $value);
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }
}

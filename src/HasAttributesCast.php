<?php

namespace JnJairo\Laravel\EloquentCast;

use Illuminate\Support\Str;
use JnJairo\Laravel\Cast\Facades\Cast;

trait HasAttributesCast
{
    /**
     * Get the type of cast for a model attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getCastType($key) : string
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
    protected function getCastFormat($key) : string
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
    protected function castAttribute($key, $value)
    {
        $keyBase = $this->getBaseAttributeName($key);

        $type = $this->getCastType($keyBase);
        $format = $this->getCastFormat($keyBase);

        if ($type) {
            $mode = config('eloquent-cast.mode');
            $suffixOnly = config('eloquent-cast.suffix_only');

            $isSuffix = $key !== $keyBase;
            $isSuffixOnly = in_array($type, $suffixOnly);

            if ($mode === 'getter' && ! $isSuffix
                || $mode === 'suffix' && $isSuffix
                || $mode === 'auto' && ($isSuffix || ! $isSuffixOnly)) {
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
    protected function castDbAttribute($key, $value)
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
    protected function castJsonAttribute($key, $value)
    {
        $type = $this->getCastType($key);
        $format = $this->getCastFormat($key);

        return $type ? Cast::castJson($value, $type, $format) : $value;
    }

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param string $key
     * @param mixed $current
     * @return bool
     */
    public function originalIsEquivalent($key, $current) : bool
    {
        if (! array_key_exists($key, $this->original)) {
            return false;
        }

        $original = $this->getOriginal($key);

        if ($current === $original) {
            return true;
        } elseif (is_null($current)) {
            return false;
        } elseif ($this->hasCast($key)) {
            return $this->castAttribute($key, $current) === $this->castAttribute($key, $original)
                || $this->castDbAttribute($key, $current) === $this->castDbAttribute($key, $original)
                || $this->castJsonAttribute($key, $current) === $this->castJsonAttribute($key, $original);
        }

        return is_numeric($current) && is_numeric($original)
                && strcmp((string) $current, (string) $original) === 0;
    }

    /**
     * Add the casted attributes to the attributes array.
     *
     * @param array $attributes
     * @param array $mutatedAttributes
     * @return array
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes) : array
    {
        foreach ($this->getCasts() as $key => $value) {
            if (! array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes)) {
                continue;
            }

            $attributes[$key] = $this->castJsonAttribute($key, $attributes[$key]);
        }

        return $attributes;
    }

    /**
     * Get the base name for the attribute.
     *
     * @param string $key
     * @return mixed
     */
    protected function getBaseAttributeName(string $key)
    {
        $mode = config('eloquent-cast.mode');

        if ($mode === 'auto' || $mode === 'suffix') {
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
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        $keyBase = $this->getBaseAttributeName($key);

        if (array_key_exists($keyBase, $this->attributes)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $keyBase = $this->getBaseAttributeName($key);

        $value = $this->getAttributeFromArray($keyBase);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        if ($this->hasCast($keyBase)) {
            return $this->castAttribute($key, $value);
        }

        if (in_array($key, $this->getDates()) &&
            ! is_null($value)) {
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
    public function setAttribute($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
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
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        $value = $this->castDbAttribute($this->getRouteKeyName(), $value);
        return $this->where($this->getRouteKeyName(), $value)->first();
    }
}

<?php

namespace JnJairo\Laravel\EloquentCast;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Str;

trait HasAttributesCast
{
    use HasAttributes {
        HasAttributes::addCastAttributesToArray as protected hasAttributesAddCastAttributesToArray;
        HasAttributes::castAttribute as protected hasAttributesCastAttribute;
        HasAttributes::getCastType as protected hasAttributesGetCastType;
        HasAttributes::originalIsEquivalent as protected hasAttributesOriginalIsEquivalent;
        HasAttributes::setAttribute as protected hasAttributesSetAttribute;
    }

    /**
     * Determine if a cast exists for a type.
     *
     * @param string $type
     * @return bool
     */
    protected function hasCastAttributeType($type)
    {
        return method_exists($this, 'cast' . Str::studly($type) . 'Attribute');
    }

    /**
     * Determine if a uncast exists for a type.
     *
     * @param string $type
     * @return bool
     */
    protected function hasUncastAttributeType($type)
    {
        return method_exists($this, 'uncast' . Str::studly($type) . 'Attribute');
    }

    /**
     * Cast an attribute value to a type.
     *
     * @param string $type
     * @param mixed $value
     * @param string $format
     * @param bool $serialized
     * @return mixed
     */
    protected function castAttributeType($type, $value, $format = '', $serialized = false)
    {
        if ($this->hasCastAttributeType($type)) {
            $value = $this->{'cast' . Str::studly($type) . 'Attribute'}($value, $format, $serialized);
        }

        return $value;
    }

    /**
     * Uncast an attribute value from a type.
     *
     * @param string $type
     * @param mixed $value
     * @param string $format
     * @param bool $serialized
     * @return mixed
     */
    protected function uncastAttributeType($type, $value, $format = '')
    {
        if ($this->hasCastAttributeType($type)) {
            $value = $this->{'uncast' . Str::studly($type) . 'Attribute'}($value, $format);
        }

        return $value;
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param string $key
     * @return string
     */
    protected function getCastType($key)
    {
        list($type, $format) = $this->getCastTypeFormat($key);

        return $type;
    }

    /**
     * Get the type and format of cast for a model attribute.
     *
     * @param string $key
     * @return array
     */
    protected function getCastTypeFormat($key)
    {
        $type = trim(strtolower($this->getCasts()[$key]));
        $format = '';

        if (strpos($type, ':') !== false) {
            list($type, $format) = explode(':', $type, 2);
        }

        if (!$this->hasCastAttributeType($type)) {
            $type = $this->hasAttributesGetCastType($key);
        }

        return array($type, $format);
    }

    /**
     * Cast an attribute.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $serialized
     * @return mixed
     */
    protected function castAttribute($key, $value, $serialized = false)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($this->hasCast($key)) {
            list($type, $format) = $this->getCastTypeFormat($key);

            if ($this->hasCastAttributeType($type)) {
                return $this->castAttributeType($type, $value, $format, $serialized);
            }
        }

        return $this->hasAttributesCastAttribute($key, $value);
    }

    /**
     * Uncast an attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function uncastAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($this->hasCast($key)) {
            list($type, $format) = $this->getCastTypeFormat($key);

            if ($this->hasUncastAttributeType($type)) {
                $value = $this->uncastAttributeType($type, $value, $format);
            }
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
        if (!$this->hasSetMutator($key)
            && $this->hasCast($key)
            && $this->hasUncastAttributeType($this->getCastType($key))) {
            $this->attributes[$key] = $this->uncastAttribute($key, $value);
            return $this;
        }

        return $this->hasAttributesSetAttribute($key, $value);
    }

    /**
     * Add the casted attributes to the attributes array.
     *
     * @param array $attributes
     * @param array $mutatedAttributes
     * @return array
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ($this->getCasts() as $key => $value) {
            if (!array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes)) {
                continue;
            }

            if ($this->hasCastAttributeType($this->getCastType($key))) {
                $attributes[$key] = $this->castAttribute($key, $attributes[$key], true);
                $mutatedAttributes[] = $key;
            }
        }

        return $this->hasAttributesAddCastAttributesToArray($attributes, $mutatedAttributes);
    }

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param string $key
     * @param mixed $current
     * @return bool
     */
    protected function originalIsEquivalent($key, $current)
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $original = $this->getOriginal($key);

        if ($current === $original) {
            return true;
        } elseif (is_null($current)) {
            return false;
        } elseif ($this->hasCast($key) && $this->hasCastAttributeType($this->getCastType($key))) {
            return $this->castAttribute($key, $current) === $this->castAttribute($key, $original)
                || $this->castAttribute($key, $current, true) === $this->castAttribute($key, $original, true);
        }

        return $this->hasAttributesOriginalIsEquivalent($key, $current);
    }
}

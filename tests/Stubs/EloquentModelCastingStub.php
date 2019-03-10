<?php

namespace JnJairo\Laravel\EloquentCast\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;

class EloquentModelStub extends Model
{
    use HasAttributesCast;

    protected $guarded = [];
}

class CustomType
{
    public $foo;
    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

trait CustomTypeCast
{
    protected function castCustomTypeAttribute($value, $format = '', $serialized = false)
    {
        if (is_string($value)) {
            $value = $this->fromJson($value);
        }

        if (is_array($value)) {
            $value = new CustomType($value['foo'], $value['bar']);
        }

        switch ($format) {
            case 'studly':
                $value->foo = studly_case($value->foo);
                break;
            default:
                $value->foo = camel_case($value->foo);
                break;
        }

        $value->bar = (int) $value->bar;

        if ($serialized) {
            $value = ['foo' => $value->foo, 'bar' => $value->bar];
        }

        return $value;
    }

    protected function uncastCustomTypeAttribute($value, $format = '')
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $value = new CustomType($value['foo'], $value['bar']);
        }

        $value->foo = snake_case($value->foo);
        $value->bar = (int) $value->bar;

        $value = ['foo' => $value->foo, 'bar' => $value->bar];

        return $this->asJson($value);
    }
}

trait SimpleTypeCast
{
    protected function castSimpleTypeAttribute($value, $format = '', $serialized = false)
    {
        switch ($format) {
            case 'studly':
                $value = studly_case($value);
                break;
            default:
                $value = camel_case($value);
                break;
        }

        return $value;
    }

    protected function uncastSimpleTypeAttribute($value, $format = '')
    {
        $value = snake_case($value);

        return $value;
    }
}

class EloquentModelCastingStub extends EloquentModelStub
{
    use CustomTypeCast;
    use SimpleTypeCast;

    protected $casts = [
        'id' => 'simple_type',
        'int_attribute' => 'int',
        'float_attribute' => 'float',
        'string_attribute' => 'string',
        'bool_attribute' => 'bool',
        'boolean_attribute' => 'boolean',
        'object_attribute' => 'object',
        'array_attribute' => 'array',
        'json_attribute' => 'json',
        'date_attribute' => 'date',
        'datetime_attribute' => 'datetime',
        'timestamp_attribute' => 'timestamp',
        'custom_attribute' => 'custom_type',
        'custom_formatted_attribute' => 'custom_type:studly',
        'simple_attribute' => 'simple_type',
        'simple_formatted_attribute' => 'simple_type:studly',
    ];

    public function getMutatedAttributeAttribute($value)
    {
        return studly_case($value);
    }

    public function setMutatedAttributeAttribute($value)
    {
        $this->attributes['mutated_attribute'] = snake_case($value);
    }

    public function getMutatedArrayAttributeAttribute($value, $serialized = false)
    {
        if (!$serialized) {
            return collect($this->fromJson($value));
        }

        return $this->fromJson($value);
    }

    public function setMutatedArrayAttributeAttribute($value)
    {
        $this->attributes['mutated_array_attribute'] = $this->asJson($value);
    }
}

class EloquentModelMorphPivotStub extends MorphPivot
{
    use HasAttributesCast;

    protected $guarded = [];
}

class EloquentModelCastingMorphPivotStub extends EloquentModelMorphPivotStub
{
    use SimpleTypeCast;

    protected $casts = [
        'id' => 'simple_type',
    ];
}

class EloquentModelPivotStub extends Pivot
{
    use HasAttributesCast;

    protected $guarded = [];
}

class EloquentModelCastingPivotStub extends EloquentModelPivotStub
{
    use SimpleTypeCast;

    protected $casts = [
        'id' => 'simple_type',
    ];
}

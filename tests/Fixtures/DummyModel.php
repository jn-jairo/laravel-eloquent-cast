<?php

namespace JnJairo\Laravel\EloquentCast\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyArrayableEnum;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyIntegerEnum;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyJsonableEnum;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyStringEnum;

class DummyModel extends Model
{
    use HasAttributesCast;

    protected $table = 'dummy';

    protected $primaryKey = 'uuid';

    protected $keyType = 'uuid';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'uuid' => 'uuid',
        'boolean' => 'bool',
        'integer' => 'int',
        'float' => 'float',
        'decimal' => 'decimal:2',
        'date' => 'date',
        'datetime' => 'datetime',
        'datetime_custom' => 'datetime:Y-m-d H:i:s.uO',
        'timestamp' => 'timestamp',
        'json' => 'json',
        'array' => 'array',
        'object' => 'object',
        'collection' => 'collection',
        'text' => 'text',
        'enum_string' => 'enum:' . DummyStringEnum::class,
        'enum_integer' => 'enum:' . DummyIntegerEnum::class,
        'enum_arrayable' => 'enum:' . DummyArrayableEnum::class,
        'enum_jsonable' => 'enum:' . DummyJsonableEnum::class,
        'enum_string_laravel' => DummyStringEnum::class,
        'enum_integer_laravel' => DummyIntegerEnum::class,
        'class_cast' => DummyCast::class,
        'encrypted' => 'encrypted',
    ];

    protected $dates = [
        'seen_at',
    ];

    public function getFooAttribute($value) : string
    {
        return $value;
    }

    public function setFooAttribute($value) : void
    {
        $this->attributes['foo'] = $value;
    }

    public function bar() : string
    {
        return 'bar';
    }
}

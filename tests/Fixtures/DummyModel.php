<?php

namespace JnJairo\Laravel\EloquentCast\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyIntegerEnum;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyStringEnum;

class DummyModel extends Model
{
    use HasAttributesCast;

    protected $table = 'dummy';

    protected $primaryKey = 'cast';

    protected $keyType = 'dummy';

    public $incrementing = false;

    protected $guarded = [];

    /**
     * @var array<array-key, mixed>
     */
    protected $casts = [
        'cast' => 'dummy',
        'cast_format' => 'dummy:case',
        'array' => 'array',
        'enum_string_laravel' => DummyStringEnum::class,
        'enum_integer_laravel' => DummyIntegerEnum::class,
        'class_cast' => DummyCast::class,
    ];

    /**
     * @var array<array-key, mixed>
     */
    protected $dates = [
        'seen_at',
    ];

    public function getFooAttribute(?string $value): ?string
    {
        return $value;
    }

    public function setFooAttribute(?string $value): void
    {
        $this->attributes['foo'] = $value;
    }

    public function bar(): string
    {
        return 'bar';
    }
}

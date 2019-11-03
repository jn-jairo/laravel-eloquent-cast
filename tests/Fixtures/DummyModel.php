<?php

namespace JnJairo\Laravel\EloquentCast\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;

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

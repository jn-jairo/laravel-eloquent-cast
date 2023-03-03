<?php

/**
 * @var \JnJairo\Laravel\EloquentCast\Tests\TestCase $this
 */

use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use JnJairo\Laravel\Cast\Facades\Cast;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\DummyModel;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyIntegerEnum;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Enums\DummyStringEnum;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\Types\DummyType;

beforeEach(function () {
    config([
        'database.default' => 'testbench',
        'database.connections.testbench' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ],
        'eloquent-cast.mode' => 'auto',
        'eloquent-cast.suffix' => '_',
        'eloquent-cast.suffix_only' => [],
        'cast.types.dummy' => DummyType::class,
    ]);

    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
});

$datetime = Carbon::createFromFormat('Y-m-d H:i:s', '1969-07-20 22:56:00', 'UTC');

$removeCast = function (array $array) {
    return array_filter($array, function (array $value, string $key) {
        return ! in_array($key, [
            'class_cast',
            'enum_string_laravel',
            'enum_integer_laravel',
        ]);
    }, ARRAY_FILTER_USE_BOTH);
};

$removeDeprecated = function (array $array) {
    $deprecated = [];

    if (version_compare(Application::VERSION, '10.0.0', '>=')) {
        $deprecated[] = 'mutated_date';
    }

    if (empty($deprecated)) {
        return $array;
    }

    return array_filter($array, function (array $value, string $key) use ($deprecated) {
        return ! in_array($key, $deprecated);
    }, ARRAY_FILTER_USE_BOTH);
};

$dataset = [
    'cast' => ['cast', 'FooBar'],
    'cast_format' => ['cast_format', 'FOO'],
    'no_cast' => ['no_cast', 1],
    'class_cast' => ['class_cast', 'FooBar'],
    'mutated' => ['foo', 'Foo'],
    'mutated_date' => ['seen_at', $datetime],
    'timestamp' => ['created_at', $datetime],
    'enum_string_laravel' => ['enum_string_laravel', DummyStringEnum::foo],
    'enum_integer_laravel' => ['enum_integer_laravel', DummyIntegerEnum::one],
];

$dataset = $removeDeprecated($dataset);

$datasetCast = $removeCast($dataset);

$datasetDb = [
    'cast' => ['cast', 'FooBar', 'foo_bar'],
    'cast_format' => ['cast_format', 'FOO', 'foo'],
    'no_cast' => ['no_cast', 1, 1],
    'class_cast' => ['class_cast', 'FooBar', 'foo_bar'],
    'mutated' => ['foo', 'Foo', 'Foo'],
    'mutated_date' => ['seen_at', $datetime, '1969-07-20 22:56:00'],
    'timestamp' => ['created_at', $datetime, '1969-07-20 22:56:00'],
    'enum_string_laravel' => ['enum_string_laravel', DummyStringEnum::foo, 'foo'],
    'enum_integer_laravel' => ['enum_integer_laravel', DummyIntegerEnum::one, 1],
];

$datasetDb = $removeDeprecated($datasetDb);

$datasetCastDb = $removeCast($datasetDb);

$datasetJson = [
    'cast' => ['cast', 'FooBar', 'FooBar'],
    'cast_format' => ['cast_format', 'FOO', 'FOO'],
    'no_cast' => ['no_cast', 1, 1],
    'class_cast' => ['class_cast', 'FooBar', 'foo-bar'],
    'mutated' => ['foo', 'Foo', 'Foo'],
    'mutated_date' => ['seen_at', $datetime, '1969-07-20T22:56:00.000000Z'],
    'timestamp' => ['created_at', $datetime, '1969-07-20T22:56:00.000000Z'],
    'enum_string_laravel' => ['enum_string_laravel', DummyStringEnum::foo, 'foo'],
    'enum_integer_laravel' => ['enum_integer_laravel', DummyIntegerEnum::one, 1],
];

$datasetJson = $removeDeprecated($datasetJson);

$datasetEquivalentValue = [
    'cast' => ['cast', 'FooBar', 'foo_bar'],
    'cast_format' => ['cast_format', 'FOO', 'foo'],
    'no_cast' => ['no_cast', 1, '1'],
    'class_cast' => ['class_cast', 'FooBar', 'foo_bar'],
    'mutated' => ['foo', 'Foo', 'Foo'],
    'mutated_date' => ['seen_at', $datetime, '1969-07-20 22:56:00'],
    'timestamp' => ['created_at', $datetime, '1969-07-20 22:56:00'],
    'enum_string_laravel' => ['enum_string_laravel', DummyStringEnum::foo, 'foo'],
    'enum_integer_laravel' => ['enum_integer_laravel', DummyIntegerEnum::one, 1],
];

$datasetEquivalentValue = $removeDeprecated($datasetEquivalentValue);

$datasetDifferentValue = [
    'cast' => ['cast', 'FooBar', 'bar_foo'],
    'cast_format' => ['cast_format', 'FOO', 'bar'],
    'no_cast' => ['no_cast', 1, '2'],
    'class_cast' => ['class_cast', 'FooBar', 'bar_foo'],
    'mutated' => ['foo', 'Foo', 'Bar'],
    'mutated_date' => ['seen_at', $datetime, '1969-07-21 22:56:01'],
    'timestamp' => ['created_at', $datetime, '1969-07-21 22:56:01'],
    'enum_string_laravel' => ['enum_string_laravel', DummyStringEnum::foo, 'bar'],
    'enum_integer_laravel' => ['enum_integer_laravel', DummyIntegerEnum::one, 2],
];

$datasetDifferentValue = $removeDeprecated($datasetDifferentValue);

it('is not dirty when set an equivalent value', function (
    string $attribute,
    mixed $value,
    mixed $newValue,
) {
    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    expect($model->isDirty($attribute))
        ->toBeFalse();
    expect($model->isDirty())
        ->toBeFalse();

    $model->$attribute = $newValue;

    expect($model->isDirty($attribute))
        ->toBeFalse();
    expect($model->isDirty())
        ->toBeFalse();
})->with($datasetEquivalentValue);

it('is dirty when set a different value', function (
    string $attribute,
    mixed $value,
    mixed $newValue,
) {
    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    expect($model->isDirty($attribute))
        ->toBeFalse();
    expect($model->isDirty())
        ->toBeFalse();

    $model->$attribute = $newValue;

    expect($model->isDirty($attribute))
        ->toBeTrue();
    expect($model->isDirty())
        ->toBeTrue();
})->with($datasetDifferentValue);

it('is dirty when set a new attribute', function (
    string $attribute,
    mixed $value,
) {
    $model = new DummyModel();
    $model->syncOriginal();

    expect($model->isDirty($attribute))
        ->toBeFalse();
    expect($model->isDirty())
        ->toBeFalse();

    $model->$attribute = $value;

    expect($model->isDirty($attribute))
        ->toBeTrue();
    expect($model->isDirty())
        ->toBeTrue();
})->with($dataset);

it('is dirty when set null', function (
    string $attribute,
    mixed $value,
) {
    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    expect($model->isDirty($attribute))
        ->toBeFalse();
    expect($model->isDirty())
        ->toBeFalse();

    $model->$attribute = null;

    expect($model->isDirty($attribute))
        ->toBeTrue();
    expect($model->isDirty())
        ->toBeTrue();
})->with($dataset);

it('can get the attribute', function (
    string $attribute,
    mixed $value,
) {
    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    if (is_object($value)) {
        expect($model->$attribute)
            ->toEqual($value);
    } else {
        expect($model->$attribute)
            ->toBe($value);
    }
})->with($dataset);

it('can get the attribute using the suffix', function (
    string $attribute,
    mixed $value,
) {
    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    $attributeSuffix = $attribute . '_';

    expect($model->$attributeSuffix)
        ->toBe($value);
})->with($datasetCast);

it('can get the attributes as array', function (
    string $attribute,
    mixed $value,
    mixed $arrayValue,
) {
    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    $array = $model->toArray();

    expect($array)
        ->toHaveKey($attribute);

    if (is_object($value) || is_object($arrayValue)) {
        expect($array[$attribute])
            ->toEqual($arrayValue);
    } else {
        expect($array[$attribute])
            ->toBe($arrayValue);
    }
})->with($datasetJson);

it('can get the attribute with configuration mode getter', function (
    string $attribute,
    mixed $value,
) {
    config(['eloquent-cast.mode' => 'getter']);

    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    if (is_object($value)) {
        expect($model->$attribute)
            ->toEqual($value);
    } else {
        expect($model->$attribute)
            ->toBe($value);
    }
})->with($dataset);

it('can get the attribute using the suffix with configuration mode suffix', function (
    string $attribute,
    mixed $value,
) {
    config(['eloquent-cast.mode' => 'suffix']);

    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    $attributeSuffix = $attribute . '_';

    expect($model->$attributeSuffix)
        ->toBe($value);
})->with($datasetCast);

it('can get the attribute with configuration suffix only', function (
    string $attribute,
    mixed $value,
    mixed $dbValue,
) {
    /**
     * @var array<string, mixed> $types
     */
    $types = config('cast.types');

    config([
        'eloquent-cast.suffix_only' => array_keys($types),
    ]);

    $model = new DummyModel([
        $attribute => $value,
    ]);
    $model->syncOriginal();

    $attributeSuffix = $attribute . '_';

    if (is_object($value) || is_object($dbValue)) {
        expect($model->$attribute)
            ->toEqual($dbValue);
        expect($model->$attributeSuffix)
            ->toEqual($value);
    } else {
        expect($model->$attribute)
            ->toBe($dbValue);
        expect($model->$attributeSuffix)
            ->toBe($value);
    }
})->with($datasetCastDb);

it('returns null for invalid attributes', function () {
    $model = new DummyModel();

    expect($model->getAttribute(''))
        ->toBeNull();
    expect($model->getAttribute('bar'))
        ->toBeNull();
    expect($model->bar)
        ->toBeNull();
    expect($model->asdf)
        ->toBeNull();
});

it('can set json', function () {
    $model = new DummyModel([
        'array' => [
            'foo' => 'bar',
        ],
    ]);
    $model->syncOriginal();

    $model->setAttribute('array->foo', 'baz');

    expect($model->array)
        ->toBe([
            'foo' => 'baz',
        ]);
});

it('can resolve route binding', function () {
    $model = new DummyModel([
        'cast' => 'FooBar',
    ]);
    $model->save();

    /**
     * @var \Illuminate\Database\Eloquent\Model $dummy
     */
    $dummy = $model->resolveRouteBinding('FooBar');

    expect($dummy)
        ->toBeInstanceOf(DummyModel::class);
    expect($dummy->cast)
        ->toBe('FooBar');

    /**
     * @var \Illuminate\Database\Eloquent\Model $dummy
     */
    $dummy = $model->resolveRouteBinding('foo_bar');

    expect($dummy)
        ->toBeInstanceOf(DummyModel::class);
    expect($dummy->cast)
        ->toBe('FooBar');
});

it('passes the format', function () {
    $attribute = 'dummy';
    $type = 'dummy';
    $format = '|foo|bar|foo:<>';
    $value = 'foo';
    $dbValue = 'bar';

    Cast::shouldReceive('castDb')
        ->once()
        ->with($value, $type, $format)
        ->andReturn($dbValue);

    Cast::shouldReceive('cast')
        ->once()
        ->with($dbValue, $type, $format)
        ->andReturn($value);

    $model = new DummyModel();
    $model->mergeCasts([
        $attribute => $type . ':' . $format,
    ]);

    $model->$attribute = $value;

    expect($model->$attribute)
        ->toBe($value);
});

<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use JnJairo\Laravel\Cast\Facades\Cast;
use JnJairo\Laravel\EloquentCast\Tests\Fixtures\DummyModel;
use JnJairo\Laravel\EloquentCast\Tests\OrchestraTestCase as TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @testdox Has attributes cast
 */
class HasAttributesCastTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup encrypter
        $app['config']->set('app.key', 'base64:' . base64_encode(
            Encrypter::generateKey('AES-256-CBC')
        ));
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }

    public function setUp() : void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        config([
            'eloquent-cast.mode' => 'auto',
            'eloquent-cast.suffix' => '_',
            'eloquent-cast.suffix_only' => [],
        ]);
    }

    public function getValues() : array
    {
        $php = [
            'uuid' => Cast::cast('', 'uuid'),
            'boolean' => Cast::cast(true, 'boolean'),
            'integer' => Cast::cast(123, 'integer'),
            'float' => Cast::cast(123.45, 'float'),
            'decimal' => Cast::cast(123.45, 'decimal', '2'),
            'date' => Cast::cast('1969-07-20', 'date'),
            'datetime' => Cast::cast('1969-07-20 22:56:00', 'datetime'),
            'datetime_custom' => Cast::cast('1969-07-20 22:56:00.571140+0000', 'datetime', 'Y-m-d H:i:s.uO'),
            'timestamp' => Cast::cast('1969-07-20 22:56:00', 'timestamp'),
            'json' => Cast::cast(['foo' => 'bar'], 'json'),
            'array' => Cast::cast(['foo' => 'bar'], 'array'),
            'object' => Cast::cast(['foo' => 'bar'], 'object'),
            'collection' => Cast::cast(['foo' => 'bar'], 'collection'),
            'text' => Cast::cast(123, 'text'),
            'class_cast' => 'FooBar',
            'encrypted' => 'FooBar',
            'no_cast' => '1',
        ];

        $db = [
            'uuid' => Cast::castDb($php['uuid'], 'uuid'),
            'boolean' => Cast::castDb($php['boolean'], 'boolean'),
            'integer' => Cast::castDb($php['integer'], 'integer'),
            'float' => Cast::castDb($php['float'], 'float'),
            'decimal' => Cast::castDb($php['decimal'], 'decimal', '2'),
            'date' => Cast::castDb($php['date'], 'date'),
            'datetime' => Cast::castDb($php['datetime'], 'datetime'),
            'datetime_custom' => Cast::castDb($php['datetime_custom'], 'datetime', 'Y-m-d H:i:s.uO'),
            'timestamp' => Cast::castDb($php['timestamp'], 'timestamp'),
            'json' => Cast::castDb($php['json'], 'json'),
            'array' => Cast::castDb($php['array'], 'array'),
            'object' => Cast::castDb($php['object'], 'object'),
            'collection' => Cast::castDb($php['collection'], 'collection'),
            'text' => Cast::castDb($php['text'], 'text'),
            'class_cast' => 'foo_bar',
            'encrypted' => Crypt::encrypt('FooBar', false),
            'no_cast' => $php['no_cast'],
        ];

        $json = [
            'uuid' => Cast::castJson($php['uuid'], 'uuid'),
            'boolean' => Cast::castJson($php['boolean'], 'boolean'),
            'integer' => Cast::castJson($php['integer'], 'integer'),
            'float' => Cast::castJson($php['float'], 'float'),
            'decimal' => Cast::castJson($php['decimal'], 'decimal', '2'),
            'date' => Cast::castJson($php['date'], 'date'),
            'datetime' => Cast::castJson($php['datetime'], 'datetime'),
            'datetime_custom' => Cast::castJson($php['datetime_custom'], 'datetime', 'Y-m-d H:i:s.uO'),
            'timestamp' => Cast::castJson($php['timestamp'], 'timestamp'),
            'json' => Cast::castJson($php['json'], 'json'),
            'array' => Cast::castJson($php['array'], 'array'),
            'object' => Cast::castJson($php['object'], 'object'),
            'collection' => Cast::castJson($php['collection'], 'collection'),
            'text' => Cast::castJson($php['text'], 'text'),
            'class_cast' => 'foo-bar',
            'encrypted' => 'FooBar',
            'no_cast' => $php['no_cast'],
        ];

        if (! method_exists(HasAttributes::class, 'serializeClassCastableAttribute')) {
            $json['class_cast'] = 'foo_bar';
        }

        return [
            'php' => $php,
            'db' => $db,
            'json' => $json,
        ];
    }

    public function getNewValues() : array
    {
        $php = [
            'uuid' => Cast::cast('', 'uuid'),
            'boolean' => Cast::cast(false, 'boolean'),
            'integer' => Cast::cast(1234, 'integer'),
            'float' => Cast::cast(1234.56, 'float'),
            'decimal' => Cast::cast(1234.56, 'decimal', '2'),
            'date' => Cast::cast('1969-07-21', 'date'),
            'datetime' => Cast::cast('1969-07-21 22:56:01', 'datetime'),
            'datetime_custom' => Cast::cast('1969-07-20 22:56:01.571141+0000', 'datetime', 'Y-m-d H:i:s.uO'),
            'timestamp' => Cast::cast('1969-07-21 22:56:01', 'timestamp'),
            'json' => Cast::cast(['foo' => 'baz'], 'json'),
            'array' => Cast::cast(['foo' => 'baz'], 'array'),
            'object' => Cast::cast(['foo' => 'baz'], 'object'),
            'collection' => Cast::cast(['foo' => 'baz'], 'collection'),
            'text' => Cast::cast(1234, 'text'),
            'class_cast' => 'BarFoo',
            'encrypted' => 'BarFoo',
            'no_cast' => '2',
            'not_exists' => 'foo',
            'foo' => 'foo',
        ];

        $db = [
            'uuid' => Cast::castDb($php['uuid'], 'uuid'),
            'boolean' => Cast::castDb($php['boolean'], 'boolean'),
            'integer' => Cast::castDb($php['integer'], 'integer'),
            'float' => Cast::castDb($php['float'], 'float'),
            'decimal' => Cast::castDb($php['decimal'], 'decimal', '2'),
            'date' => Cast::castDb($php['date'], 'date'),
            'datetime' => Cast::castDb($php['datetime'], 'datetime'),
            'datetime_custom' => Cast::castDb($php['datetime_custom'], 'datetime', 'Y-m-d H:i:s.uO'),
            'timestamp' => Cast::castDb($php['timestamp'], 'timestamp'),
            'json' => Cast::castDb($php['json'], 'json'),
            'array' => Cast::castDb($php['array'], 'array'),
            'object' => Cast::castDb($php['object'], 'object'),
            'collection' => Cast::castDb($php['collection'], 'collection'),
            'text' => Cast::castDb($php['text'], 'text'),
            'class_cast' => 'bar_foo',
            'encrypted' => Crypt::encrypt('BarFoo', false),
            'no_cast' => $php['no_cast'],
            'not_exists' => $php['not_exists'],
            'foo' => $php['foo'],
        ];

        $json = [
            'uuid' => Cast::castJson($php['uuid'], 'uuid'),
            'boolean' => Cast::castJson($php['boolean'], 'boolean'),
            'integer' => Cast::castJson($php['integer'], 'integer'),
            'float' => Cast::castJson($php['float'], 'float'),
            'decimal' => Cast::castJson($php['decimal'], 'decimal', '2'),
            'date' => Cast::castJson($php['date'], 'date'),
            'datetime' => Cast::castJson($php['datetime'], 'datetime'),
            'datetime_custom' => Cast::castJson($php['datetime_custom'], 'datetime', 'Y-m-d H:i:s.uO'),
            'timestamp' => Cast::castJson($php['timestamp'], 'timestamp'),
            'json' => Cast::castJson($php['json'], 'json'),
            'array' => Cast::castJson($php['array'], 'array'),
            'object' => Cast::castJson($php['object'], 'object'),
            'collection' => Cast::castJson($php['collection'], 'collection'),
            'text' => Cast::castJson($php['text'], 'text'),
            'class_cast' => 'bar-foo',
            'encrypted' => 'BarFoo',
            'no_cast' => $php['no_cast'],
            'not_exists' => $php['not_exists'],
            'foo' => $php['foo'],
        ];

        if (! method_exists(HasAttributes::class, 'serializeClassCastableAttribute')) {
            $json['class_cast'] = 'bar_foo';
        }

        return [
            'php' => $php,
            'db' => $db,
            'json' => $json,
        ];
    }

    public function test_dirty() : void
    {
        $values = $this->getValues();
        $newValues = $this->getNewValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        foreach ($values['php'] as $key => $value) {
            $model->$key = $value;
        }

        $this->assertFalse($model->isDirty(), 'Same value');

        foreach ($values['php'] as $key => $value) {
            $this->assertFalse($model->isDirty($key), 'Same value ' . $key);
        }

        foreach ($newValues['php'] as $key => $value) {
            $model->$key = $value;
        }

        $this->assertTrue($model->isDirty(), 'New value');

        foreach ($newValues['php'] as $key => $value) {
            $this->assertTrue($model->isDirty($key), 'New value ' . $key);
        }

        foreach ($newValues['php'] as $key => $value) {
            $model->$key = null;
        }

        $this->assertTrue($model->isDirty(), 'Null value');

        foreach ($newValues['php'] as $key => $value) {
            $this->assertTrue($model->isDirty($key), 'Null value ' . $key);
        }
    }

    public function test_get_attribute() : void
    {
        $values = $this->getValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $this->assertInstanceOf(UuidInterface::class, $model->uuid, 'uuid class');
        $this->assertSame($values['php']['uuid']->toString(), $model->uuid->toString(), 'uuid');
        $this->assertSame($values['php']['boolean'], $model->boolean, 'boolean');
        $this->assertSame($values['php']['integer'], $model->integer, 'integer');
        $this->assertSame($values['php']['float'], $model->float, 'float');
        $this->assertEquals($values['php']['decimal'], $model->decimal, 'decimal');
        $this->assertInstanceOf(Carbon::class, $model->date, 'date class');
        $this->assertSame($values['php']['date']->toDateString(), $model->date->toDateString(), 'date');
        $this->assertInstanceOf(Carbon::class, $model->datetime, 'datetime class');
        $this->assertSame(
            $values['php']['datetime']->toDateTimeString(),
            $model->datetime->toDateTimeString(),
            'datetime'
        );
        $this->assertInstanceOf(Carbon::class, $model->datetime_custom, 'datetime custom class');
        $this->assertTrue($values['php']['datetime_custom']->equalTo($model->datetime_custom), 'datetime custom');
        $this->assertIsInt($model->timestamp, 'timestamp type');
        $this->assertSame($values['php']['timestamp'], $model->timestamp, 'timestamp');
        $this->assertSame($values['php']['json'], $model->json, 'json');
        $this->assertSame($values['php']['array'], $model->array, 'array');
        $this->assertEquals($values['php']['object'], $model->object, 'object');
        $this->assertEquals($values['php']['collection'], $model->collection, 'collection');
        $this->assertSame($values['php']['text'], $model->text, 'text');
        $this->assertSame($values['php']['class_cast'], $model->class_cast, 'class cast');
        $this->assertSame($values['php']['encrypted'], $model->encrypted, 'encrypted');
        $this->assertSame($values['php']['no_cast'], $model->no_cast, 'no cast');
    }

    public function test_get_attribute_suffix() : void
    {
        $values = $this->getValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $this->assertInstanceOf(UuidInterface::class, $model->uuid_, 'uuid class');
        $this->assertSame($values['php']['uuid']->toString(), $model->uuid_->toString(), 'uuid');
        $this->assertSame($values['php']['boolean'], $model->boolean_, 'boolean');
        $this->assertSame($values['php']['integer'], $model->integer_, 'integer');
        $this->assertSame($values['php']['float'], $model->float_, 'float');
        $this->assertEquals($values['php']['decimal'], $model->decimal_, 'decimal');
        $this->assertInstanceOf(Carbon::class, $model->date_, 'date class');
        $this->assertSame($values['php']['date']->toDateString(), $model->date_->toDateString(), 'date');
        $this->assertInstanceOf(Carbon::class, $model->datetime_, 'datetime class');
        $this->assertSame(
            $values['php']['datetime']->toDateTimeString(),
            $model->datetime_->toDateTimeString(),
            'datetime'
        );
        $this->assertInstanceOf(Carbon::class, $model->datetime_custom_, 'datetime custom class');
        $this->assertTrue($values['php']['datetime_custom']->equalTo($model->datetime_custom_), 'datetime custom');
        $this->assertIsInt($model->timestamp_, 'timestamp type');
        $this->assertSame($values['php']['timestamp'], $model->timestamp_, 'timestamp');
        $this->assertSame($values['php']['json'], $model->json_, 'json');
        $this->assertSame($values['php']['array'], $model->array_, 'array');
        $this->assertEquals($values['php']['object'], $model->object_, 'object');
        $this->assertEquals($values['php']['collection'], $model->collection_, 'collection');
        $this->assertSame($values['php']['text'], $model->text_, 'text');
        $this->assertSame($values['php']['encrypted'], $model->encrypted_, 'encrypted');
    }

    public function test_get_attribute_array() : void
    {
        $values = $this->getValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $array = $model->toArray();

        $this->assertSame($values['json']['uuid'], $array['uuid'], 'uuid');
        $this->assertSame($values['json']['boolean'], $array['boolean'], 'boolean');
        $this->assertSame($values['json']['integer'], $array['integer'], 'integer');
        $this->assertSame($values['json']['float'], $array['float'], 'float');
        $this->assertSame($values['json']['decimal'], $array['decimal'], 'decimal');
        $this->assertSame($values['json']['date'], $array['date'], 'date');
        $this->assertSame($values['json']['datetime'], $array['datetime'], 'datetime');
        $this->assertSame($values['json']['datetime_custom'], $array['datetime_custom'], 'datetime custom');
        $this->assertSame($values['json']['timestamp'], $array['timestamp'], 'timestamp');
        $this->assertSame($values['json']['json'], $array['json'], 'json');
        $this->assertSame($values['json']['array'], $array['array'], 'array');
        $this->assertSame($values['json']['object'], $array['object'], 'object');
        $this->assertSame($values['json']['collection'], $array['collection'], 'collection');
        $this->assertSame($values['json']['text'], $array['text'], 'text');
        $this->assertSame($values['json']['class_cast'], $array['class_cast'], 'class cast');
        $this->assertSame($values['json']['encrypted'], $array['encrypted'], 'encrypted');
        $this->assertSame($values['json']['no_cast'], $array['no_cast'], 'no cast');
    }

    public function test_config_mode_getter() : void
    {
        config(['eloquent-cast.mode' => 'getter']);

        $values = $this->getValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $this->assertInstanceOf(UuidInterface::class, $model->uuid, 'uuid class');
        $this->assertSame($values['php']['uuid']->toString(), $model->uuid->toString(), 'uuid');
        $this->assertSame($values['php']['boolean'], $model->boolean, 'boolean');
        $this->assertSame($values['php']['integer'], $model->integer, 'integer');
        $this->assertSame($values['php']['float'], $model->float, 'float');
        $this->assertEquals($values['php']['decimal'], $model->decimal, 'decimal');
        $this->assertInstanceOf(Carbon::class, $model->date, 'date class');
        $this->assertSame($values['php']['date']->toDateString(), $model->date->toDateString(), 'date');
        $this->assertInstanceOf(Carbon::class, $model->datetime, 'datetime class');
        $this->assertSame(
            $values['php']['datetime']->toDateTimeString(),
            $model->datetime->toDateTimeString(),
            'datetime'
        );
        $this->assertInstanceOf(Carbon::class, $model->datetime_custom, 'datetime custom class');
        $this->assertTrue($values['php']['datetime_custom']->equalTo($model->datetime_custom), 'datetime custom');
        $this->assertIsInt($model->timestamp, 'timestamp type');
        $this->assertSame($values['php']['timestamp'], $model->timestamp, 'timestamp');
        $this->assertSame($values['php']['json'], $model->json, 'json');
        $this->assertSame($values['php']['array'], $model->array, 'array');
        $this->assertEquals($values['php']['object'], $model->object, 'object');
        $this->assertEquals($values['php']['collection'], $model->collection, 'collection');
        $this->assertSame($values['php']['text'], $model->text, 'text');
        $this->assertSame($values['php']['class_cast'], $model->class_cast, 'class cast');
        $this->assertSame($values['php']['encrypted'], $model->encrypted, 'encrypted');
        $this->assertSame($values['php']['no_cast'], $model->no_cast, 'no cast');

        $this->assertNull($model->uuid_, 'uuid');
        $this->assertNull($model->boolean_, 'boolean');
        $this->assertNull($model->integer_, 'integer');
        $this->assertNull($model->float_, 'float');
        $this->assertNull($model->decimal_, 'decimal');
        $this->assertNull($model->date_, 'date');
        $this->assertNull($model->datetime_, 'datetime');
        $this->assertNull($model->datetime_custom_, 'datetime');
        $this->assertNull($model->timestamp_, 'timestamp');
        $this->assertNull($model->json_, 'json');
        $this->assertNull($model->array_, 'array');
        $this->assertNull($model->object_, 'object');
        $this->assertNull($model->collection_, 'collection');
        $this->assertNull($model->text_, 'text');
        $this->assertNull($model->encrypted_, 'encrypted');
    }

    public function test_config_mode_suffix() : void
    {
        config(['eloquent-cast.mode' => 'suffix']);

        $values = $this->getValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $this->assertSame($values['db']['uuid'], $model->uuid, 'uuid');
        $this->assertSame($values['db']['boolean'], $model->boolean, 'boolean');
        $this->assertSame($values['db']['integer'], $model->integer, 'integer');
        $this->assertSame($values['db']['float'], $model->float, 'float');
        $this->assertSame($values['db']['decimal'], $model->decimal, 'decimal');
        $this->assertSame($values['db']['date'], $model->date, 'date');
        $this->assertSame($values['db']['datetime'], $model->datetime, 'datetime');
        $this->assertSame($values['db']['datetime_custom'], $model->datetime_custom, 'datetime custom');
        $this->assertSame($values['db']['timestamp'], $model->timestamp, 'timestamp');
        $this->assertSame($values['db']['json'], $model->json, 'json');
        $this->assertSame($values['db']['array'], $model->array, 'array');
        $this->assertSame($values['db']['object'], $model->object, 'object');
        $this->assertSame($values['db']['collection'], $model->collection, 'collection');
        $this->assertSame($values['db']['text'], $model->text, 'text');
        $this->assertSame(
            Crypt::decrypt($values['db']['encrypted'], false),
            Crypt::decrypt($model->encrypted, false),
            'encrypted'
        );

        $this->assertInstanceOf(UuidInterface::class, $model->uuid_, 'uuid class');
        $this->assertSame($values['php']['uuid']->toString(), $model->uuid_->toString(), 'uuid');
        $this->assertSame($values['php']['boolean'], $model->boolean_, 'boolean');
        $this->assertSame($values['php']['integer'], $model->integer_, 'integer');
        $this->assertSame($values['php']['float'], $model->float_, 'float');
        $this->assertEquals($values['php']['decimal'], $model->decimal_, 'decimal');
        $this->assertInstanceOf(Carbon::class, $model->date_, 'date class');
        $this->assertSame($values['php']['date']->toDateString(), $model->date_->toDateString(), 'date');
        $this->assertInstanceOf(Carbon::class, $model->datetime_, 'datetime class');
        $this->assertSame(
            $values['php']['datetime']->toDateTimeString(),
            $model->datetime_->toDateTimeString(),
            'datetime'
        );
        $this->assertInstanceOf(Carbon::class, $model->datetime_custom_, 'datetime custom class');
        $this->assertTrue($values['php']['datetime_custom']->equalTo($model->datetime_custom_), 'datetime custom');
        $this->assertIsInt($model->timestamp_, 'timestamp type');
        $this->assertSame($values['php']['timestamp'], $model->timestamp_, 'timestamp');
        $this->assertSame($values['php']['json'], $model->json_, 'json');
        $this->assertSame($values['php']['array'], $model->array_, 'array');
        $this->assertEquals($values['php']['object'], $model->object_, 'object');
        $this->assertEquals($values['php']['collection'], $model->collection_, 'collection');
        $this->assertSame($values['php']['text'], $model->text_, 'text');
        $this->assertSame($values['php']['encrypted'], $model->encrypted_, 'encrypted');
    }

    public function test_config_mode_suffix_only() : void
    {
        $values = $this->getValues();

        config(['eloquent-cast.suffix_only' => array_keys($values['php'])]);

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $this->assertSame($values['db']['uuid'], $model->uuid, 'uuid');
        $this->assertSame($values['db']['boolean'], $model->boolean, 'boolean');
        $this->assertSame($values['db']['integer'], $model->integer, 'integer');
        $this->assertSame($values['db']['float'], $model->float, 'float');
        $this->assertSame($values['db']['decimal'], $model->decimal, 'decimal');
        $this->assertSame($values['db']['date'], $model->date, 'date');
        $this->assertSame($values['db']['datetime'], $model->datetime, 'datetime');
        $this->assertSame($values['db']['datetime_custom'], $model->datetime_custom, 'datetime custom');
        $this->assertSame($values['db']['timestamp'], $model->timestamp, 'timestamp');
        $this->assertSame($values['db']['json'], $model->json, 'json');
        $this->assertSame($values['db']['array'], $model->array, 'array');
        $this->assertSame($values['db']['object'], $model->object, 'object');
        $this->assertSame($values['db']['collection'], $model->collection, 'collection');
        $this->assertSame($values['db']['text'], $model->text, 'text');
        $this->assertSame($values['db']['no_cast'], $model->no_cast, 'no cast');
        $this->assertSame(
            Crypt::decrypt($values['db']['encrypted'], false),
            Crypt::decrypt($model->encrypted, false),
            'encrypted'
        );

        $this->assertInstanceOf(UuidInterface::class, $model->uuid_, 'uuid class');
        $this->assertSame($values['php']['uuid']->toString(), $model->uuid_->toString(), 'uuid');
        $this->assertSame($values['php']['boolean'], $model->boolean_, 'boolean');
        $this->assertSame($values['php']['integer'], $model->integer_, 'integer');
        $this->assertSame($values['php']['float'], $model->float_, 'float');
        $this->assertEquals($values['php']['decimal'], $model->decimal_, 'decimal');
        $this->assertInstanceOf(Carbon::class, $model->date_, 'date class');
        $this->assertSame($values['php']['date']->toDateString(), $model->date_->toDateString(), 'date');
        $this->assertInstanceOf(Carbon::class, $model->datetime_, 'datetime class');
        $this->assertSame(
            $values['php']['datetime']->toDateTimeString(),
            $model->datetime_->toDateTimeString(),
            'datetime'
        );
        $this->assertInstanceOf(Carbon::class, $model->datetime_custom_, 'datetime custom class');
        $this->assertTrue($values['php']['datetime_custom']->equalTo($model->datetime_custom_), 'datetime custom');
        $this->assertIsInt($model->timestamp_, 'timestamp type');
        $this->assertSame($values['php']['timestamp'], $model->timestamp_, 'timestamp');
        $this->assertSame($values['php']['json'], $model->json_, 'json');
        $this->assertSame($values['php']['array'], $model->array_, 'array');
        $this->assertEquals($values['php']['object'], $model->object_, 'object');
        $this->assertEquals($values['php']['collection'], $model->collection_, 'collection');
        $this->assertSame($values['php']['text'], $model->text_, 'text');
        $this->assertSame($values['php']['encrypted'], $model->encrypted_, 'encrypted');
    }

    public function test_get_invalid_attribute() : void
    {
        $model = new DummyModel();

        $this->assertNull($model->getAttribute(''), 'Empty attribute name');
        $this->assertNull($model->bar, 'Method');
    }

    public function test_mutated_attribute() : void
    {
        $values = $this->getValues();
        $newValues = $this->getNewValues();

        $model = new DummyModel([
            'foo' => 'foo',
            'seen_at' => $values['db']['datetime'],
        ]);
        $model->syncOriginal();

        $this->assertSame('foo', $model->foo, 'Get foo');
        $this->assertInstanceOf(Carbon::class, $model->seen_at, 'Get datetime class');
        $this->assertSame($values['db']['datetime'], $model->seen_at->toDateTimeString(), 'Get datetime');

        $array = $model->toArray();

        $this->assertSame('foo', $array['foo'], 'Get array foo');
        $this->assertSame(Carbon::parse($values['db']['datetime'])->toJSON(), $array['seen_at'], 'Get array datetime');

        $model->foo = 'bar';
        $model->seen_at = $newValues['php']['datetime'];

        $this->assertSame('bar', $model->foo, 'Set foo');
        $this->assertInstanceOf(Carbon::class, $model->seen_at, 'Set datetime class');
        $this->assertSame($newValues['db']['datetime'], $model->seen_at->toDateTimeString(), 'Set datetime');

        $array = $model->toArray();

        $this->assertSame('bar', $array['foo'], 'Set array foo');
        $this->assertSame(
            Carbon::parse($newValues['db']['datetime'])->toJSON(),
            $array['seen_at'],
            'Set array datetime'
        );
    }

    public function test_set_json() : void
    {
        $values = $this->getValues();
        $newValues = $this->getNewValues();

        $model = new DummyModel($values['db']);
        $model->syncOriginal();

        $model->setAttribute('array->foo', $newValues['php']['array']['foo']);
        $this->assertSame($newValues['php']['array'], $model->array, 'Array');
    }

    public function test_resolve_route_binding() : void
    {
        $values = $this->getValues();

        $model = new DummyModel($values['db']);
        $model->save();

        $dummy = $model->resolveRouteBinding($values['php']['uuid']);

        $this->assertInstanceOf(DummyModel::class, $dummy, 'Model class');
        $this->assertInstanceOf(UuidInterface::class, $dummy->uuid, 'Key class');
        $this->assertSame($model->uuid->toString(), $dummy->uuid->toString(), 'Same model');
    }
}

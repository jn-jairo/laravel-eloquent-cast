<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use JnJairo\Laravel\EloquentCast\Tests\Stubs\EloquentModelCastingStub;
use JnJairo\Laravel\EloquentCast\Tests\Stubs\EloquentModelCastingMorphPivotStub;
use JnJairo\Laravel\EloquentCast\Tests\Stubs\EloquentModelCastingPivotStub;
use JnJairo\Laravel\EloquentCast\Tests\Stubs\CustomType;
use JnJairo\Laravel\EloquentCast\Tests\TestCase;
use stdClass;

/**
 * @testdox Has attributes cast
 */
class HasAttributesCastTest extends TestCase
{
    public function test_dirty_attributes()
    {
        $model = new EloquentModelCastingStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
        $model->syncOriginal();
        $model->foo = 1;
        $model->bar = 20;
        $model->baz = 30;
        $model->fooBar = 4;

        $this->assertTrue($model->isDirty());
        $this->assertFalse($model->isDirty('foo'));
        $this->assertTrue($model->isDirty('bar'));
        $this->assertTrue($model->isDirty('foo', 'bar'));
        $this->assertTrue($model->isDirty(['foo', 'bar']));
        $this->assertTrue($model->isDirty('fooBar'));
    }

    public function test_dirty_on_cast_or_date_attributes()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->bool_attribute = 1;
        $model->foo = 1;
        $model->bar = '2017-03-18';
        $model->date_attribute = '2017-03-18';
        $model->datetime_attribute = '2017-03-23 22:17:00';
        $model->custom_attribute = '{"foo": "foo_bar", "bar": "123"}';
        $model->custom_formatted_attribute = '{"foo": "foo_bar", "bar": "123"}';
        $model->simple_attribute = 'foo_bar';
        $model->simple_formatted_attribute = 'foo_bar';
        $model->syncOriginal();

        $model->bool_attribute = true;
        $model->foo = true;
        $model->bar = '2017-03-18 00:00:00';
        $model->date_attribute = '2017-03-18 00:00:00';
        $model->datetime_attribute = null;
        $model->custom_attribute = ['foo' => 'fooBar', 'bar' => 123];
        $model->custom_formatted_attribute = ['foo' => 'FooBar', 'bar' => 123];
        $model->simple_attribute = 'fooBar';
        $model->simple_formatted_attribute = 'FooBar';

        $this->assertTrue($model->isDirty());
        $this->assertTrue($model->isDirty('foo'));
        $this->assertTrue($model->isDirty('bar'));
        $this->assertFalse($model->isDirty('bool_attribute'));
        $this->assertFalse($model->isDirty('date_attribute'));
        $this->assertTrue($model->isDirty('datetime_attribute'));
        $this->assertFalse($model->isDirty('custom_attribute'));
        $this->assertFalse($model->isDirty('custom_formatted_attribute'));
        $this->assertFalse($model->isDirty('simple_attribute'));
        $this->assertFalse($model->isDirty('simple_formatted_attribute'));
    }

    public function test_model_attributes_are_casted_when_present_in_casts_array()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->int_attribute = '3';
        $model->float_attribute = '4.0';
        $model->string_attribute = 2.5;
        $model->bool_attribute = 1;
        $model->boolean_attribute = 0;
        $model->object_attribute = ['foo' => 'bar'];
        $obj = new stdClass;
        $obj->foo = 'bar';
        $model->array_attribute = $obj;
        $model->json_attribute = ['foo' => 'bar'];
        $model->date_attribute = '1969-07-20';
        $model->datetime_attribute = '1969-07-20 22:56:00';
        $model->timestamp_attribute = '1969-07-20 22:56:00';
        $model->custom_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->custom_formatted_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->simple_attribute = 'foo_bar';
        $model->simple_formatted_attribute = 'foo_bar';

        $this->assertIsInt($model->int_attribute);
        $this->assertIsFloat($model->float_attribute);
        $this->assertIsString($model->string_attribute);
        $this->assertIsBool($model->bool_attribute);
        $this->assertIsBool($model->boolean_attribute);
        $this->assertIsObject($model->object_attribute);
        $this->assertIsArray($model->array_attribute);
        $this->assertIsArray($model->json_attribute);
        $this->assertTrue($model->bool_attribute);
        $this->assertFalse($model->boolean_attribute);
        $this->assertEquals($obj, $model->object_attribute);
        $this->assertEquals(['foo' => 'bar'], $model->array_attribute);
        $this->assertEquals(['foo' => 'bar'], $model->json_attribute);
        $this->assertEquals('{"foo":"bar"}', $model->getRawAttribute('json_attribute'));
        $this->assertInstanceOf(Carbon::class, $model->date_attribute);
        $this->assertInstanceOf(Carbon::class, $model->datetime_attribute);
        $this->assertEquals('1969-07-20', $model->date_attribute->toDateString());
        $this->assertEquals('1969-07-20 22:56:00', $model->datetime_attribute->toDateTimeString());
        $this->assertEquals(-14173440, $model->timestamp_attribute);
        $this->assertInstanceOf(CustomType::class, $model->custom_attribute);
        $this->assertSame('fooBar', $model->custom_attribute->foo);
        $this->assertSame(123, $model->custom_attribute->bar);
        $this->assertInstanceOf(CustomType::class, $model->custom_formatted_attribute);
        $this->assertSame('FooBar', $model->custom_formatted_attribute->foo);
        $this->assertSame(123, $model->custom_formatted_attribute->bar);
        $this->assertSame('fooBar', $model->simple_attribute);
        $this->assertSame('FooBar', $model->simple_formatted_attribute);

        $arr = $model->toArray();

        $this->assertIsInt($arr['int_attribute']);
        $this->assertIsFloat($arr['float_attribute']);
        $this->assertIsString($arr['string_attribute']);
        $this->assertIsBool($arr['bool_attribute']);
        $this->assertIsBool($arr['boolean_attribute']);
        $this->assertIsObject($arr['object_attribute']);
        $this->assertIsArray($arr['array_attribute']);
        $this->assertIsArray($arr['json_attribute']);
        $this->assertTrue($arr['bool_attribute']);
        $this->assertFalse($arr['boolean_attribute']);
        $this->assertEquals($obj, $arr['object_attribute']);
        $this->assertEquals(['foo' => 'bar'], $arr['array_attribute']);
        $this->assertEquals(['foo' => 'bar'], $arr['json_attribute']);
        $this->assertEquals('1969-07-20 00:00:00', $arr['date_attribute']);
        $this->assertEquals('1969-07-20 22:56:00', $arr['datetime_attribute']);
        $this->assertEquals(-14173440, $arr['timestamp_attribute']);
        $this->assertIsArray($arr['custom_attribute']);
        $this->assertSame('fooBar', $arr['custom_attribute']['foo']);
        $this->assertSame(123, $arr['custom_attribute']['bar']);
        $this->assertIsArray($arr['custom_formatted_attribute']);
        $this->assertSame('FooBar', $arr['custom_formatted_attribute']['foo']);
        $this->assertSame(123, $arr['custom_formatted_attribute']['bar']);
        $this->assertSame('fooBar', $arr['simple_attribute']);
        $this->assertSame('FooBar', $arr['simple_formatted_attribute']);
    }

    public function test_model_date_attribute_casting_resets_time()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->date_attribute = '1969-07-20 22:56:00';

        $this->assertEquals('1969-07-20 00:00:00', $model->date_attribute->toDateTimeString());

        $arr = $model->toArray();
        $this->assertEquals('1969-07-20 00:00:00', $arr['date_attribute']);
    }

    public function test_model_attribute_casting_preserves_null()
    {
        $model = new EloquentModelCastingStub;
        $model->int_attribute = null;
        $model->float_attribute = null;
        $model->string_attribute = null;
        $model->bool_attribute = null;
        $model->boolean_attribute = null;
        $model->object_attribute = null;
        $model->array_attribute = null;
        $model->json_attribute = null;
        $model->date_attribute = null;
        $model->datetime_attribute = null;
        $model->timestamp_attribute = null;
        $model->custom_attribute = null;
        $model->custom_formatted_attribute = null;
        $model->simple_attribute = null;
        $model->simple_formatted_attribute = null;

        $attributes = $model->getAttributes();

        $this->assertNull($attributes['int_attribute']);
        $this->assertNull($attributes['float_attribute']);
        $this->assertNull($attributes['string_attribute']);
        $this->assertNull($attributes['bool_attribute']);
        $this->assertNull($attributes['boolean_attribute']);
        $this->assertNull($attributes['object_attribute']);
        $this->assertNull($attributes['array_attribute']);
        $this->assertNull($attributes['json_attribute']);
        $this->assertNull($attributes['date_attribute']);
        $this->assertNull($attributes['datetime_attribute']);
        $this->assertNull($attributes['timestamp_attribute']);
        $this->assertNull($attributes['custom_attribute']);
        $this->assertNull($attributes['custom_formatted_attribute']);
        $this->assertNull($attributes['simple_attribute']);
        $this->assertNull($attributes['simple_formatted_attribute']);

        $this->assertNull($model->int_attribute);
        $this->assertNull($model->float_attribute);
        $this->assertNull($model->string_attribute);
        $this->assertNull($model->bool_attribute);
        $this->assertNull($model->boolean_attribute);
        $this->assertNull($model->object_attribute);
        $this->assertNull($model->array_attribute);
        $this->assertNull($model->json_attribute);
        $this->assertNull($model->date_attribute);
        $this->assertNull($model->datetime_attribute);
        $this->assertNull($model->timestamp_attribute);
        $this->assertNull($model->custom_attribute);
        $this->assertNull($model->custom_formatted_attribute);
        $this->assertNull($model->simple_attribute);
        $this->assertNull($model->simple_formatted_attribute);

        $array = $model->toArray();

        $this->assertNull($array['int_attribute']);
        $this->assertNull($array['float_attribute']);
        $this->assertNull($array['string_attribute']);
        $this->assertNull($array['bool_attribute']);
        $this->assertNull($array['boolean_attribute']);
        $this->assertNull($array['object_attribute']);
        $this->assertNull($array['array_attribute']);
        $this->assertNull($array['json_attribute']);
        $this->assertNull($array['date_attribute']);
        $this->assertNull($array['datetime_attribute']);
        $this->assertNull($array['timestamp_attribute']);
        $this->assertNull($array['custom_attribute']);
        $this->assertNull($array['custom_formatted_attribute']);
        $this->assertNull($array['simple_attribute']);
        $this->assertNull($array['simple_formatted_attribute']);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\JsonEncodingException
     * @expectedExceptionMessage Unable to encode attribute [object_attribute] for model
     *                           [JnJairo\Laravel\EloquentCast\Tests\EloquentModelCastingStub] to JSON:
     *                           Malformed UTF-8 characters, possibly incorrectly encoded.
     */
    public function test_model_attribute_casting_fails_on_unencodable_data()
    {
        $model = new EloquentModelCastingStub;
        $model->object_attribute = ['foo' => "b\xF8r"];
        $obj = new stdClass;
        $obj->foo = "b\xF8r";
        $model->array_attribute = $obj;
        $model->json_attribute = ['foo' => "b\xF8r"];

        $model->getAttributes();
    }

    /**
     * @requires function \Illuminate\Database\Eloquent\Concerns\HasAttributes::fromFloat
     */
    public function test_model_attribute_casting_with_special_float_values()
    {
        $model = new EloquentModelCastingStub;

        $model->float_attribute = 0;
        $this->assertSame(0.0, $model->float_attribute);

        $model->float_attribute = 'Infinity';
        $this->assertSame(INF, $model->float_attribute);

        $model->float_attribute = INF;
        $this->assertSame(INF, $model->float_attribute);

        $model->float_attribute = '-Infinity';
        $this->assertSame(-INF, $model->float_attribute);

        $model->float_attribute = -INF;
        $this->assertSame(-INF, $model->float_attribute);

        $model->float_attribute = 'NaN';
        $this->assertNan($model->float_attribute);

        $model->float_attribute = NAN;
        $this->assertNan($model->float_attribute);
    }

    public function test_model_get_attribute()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->int_attribute = '3';
        $model->float_attribute = '4.0';
        $model->string_attribute = 2.5;
        $model->bool_attribute = 1;
        $model->boolean_attribute = 0;
        $model->object_attribute = ['foo' => 'bar'];
        $obj = new stdClass;
        $obj->foo = 'bar';
        $model->array_attribute = $obj;
        $model->json_attribute = ['foo' => 'bar'];
        $model->date_attribute = '1969-07-20';
        $model->datetime_attribute = '1969-07-20 22:56:00';
        $model->timestamp_attribute = '1969-07-20 22:56:00';
        $model->custom_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->custom_formatted_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->simple_attribute = 'foo_bar';
        $model->simple_formatted_attribute = 'foo_bar';
        $model->mutated_attribute = 'foo_bar';
        $model->mutated_array_attribute = ['foo' => 'bar'];
        $model->withoutCastAttribute = 123;

        $this->assertSame(3, $model->getAttribute('int_attribute'));
        $this->assertSame(4.0, $model->getAttribute('float_attribute'));
        $this->assertSame('2.5', $model->getAttribute('string_attribute'));
        $this->assertTrue($model->getAttribute('bool_attribute'));
        $this->assertFalse($model->getAttribute('boolean_attribute'));
        $this->assertEquals($obj, $model->getAttribute('object_attribute'));
        $this->assertSame(['foo' => 'bar'], $model->getAttribute('array_attribute'));
        $this->assertSame(['foo' => 'bar'], $model->getAttribute('json_attribute'));
        $this->assertInstanceOf(Carbon::class, $model->getAttribute('date_attribute'));
        $this->assertInstanceOf(Carbon::class, $model->getAttribute('datetime_attribute'));
        $this->assertSame('1969-07-20', $model->getAttribute('date_attribute')->toDateString());
        $this->assertSame('1969-07-20 22:56:00', $model->getAttribute('datetime_attribute')->toDateTimeString());
        $this->assertSame(-14173440, $model->getAttribute('timestamp_attribute'));
        $this->assertInstanceOf(CustomType::class, $model->getAttribute('custom_attribute'));
        $this->assertSame('fooBar', $model->getAttribute('custom_attribute')->foo);
        $this->assertSame(123, $model->getAttribute('custom_attribute')->bar);
        $this->assertInstanceOf(CustomType::class, $model->getAttribute('custom_formatted_attribute'));
        $this->assertSame('FooBar', $model->getAttribute('custom_formatted_attribute')->foo);
        $this->assertSame(123, $model->getAttribute('custom_formatted_attribute')->bar);
        $this->assertSame('fooBar', $model->getAttribute('simple_attribute'));
        $this->assertSame('FooBar', $model->getAttribute('simple_formatted_attribute'));
        $this->assertSame('FooBar', $model->getAttribute('mutated_attribute'));
        $this->assertInstanceOf(Arrayable::class, $model->getAttribute('mutated_array_attribute'));
        $this->assertSame(['foo' => 'bar'], $model->getAttribute('mutated_array_attribute')->toArray());
        $this->assertSame(123, $model->getAttribute('withoutCastAttribute'));
        $this->assertNull($model->getAttribute('invalidAttribute'));
        $this->assertNull($model->getAttribute(null));
    }

    public function test_model_get_raw_attribute()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->int_attribute = '3';
        $model->float_attribute = '4.0';
        $model->string_attribute = 2.5;
        $model->bool_attribute = 1;
        $model->boolean_attribute = 0;
        $model->object_attribute = ['foo' => 'bar'];
        $obj = new stdClass;
        $obj->foo = 'bar';
        $model->array_attribute = $obj;
        $model->json_attribute = ['foo' => 'bar'];
        $model->date_attribute = '1969-07-20';
        $model->datetime_attribute = '1969-07-20 22:56:00';
        $model->timestamp_attribute = '1969-07-20 22:56:00';
        $model->custom_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->custom_formatted_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->simple_attribute = 'foo_bar';
        $model->simple_formatted_attribute = 'foo_bar';
        $model->mutated_attribute = 'foo_bar';
        $model->mutated_array_attribute = ['foo' => 'bar'];
        $model->withoutCastAttribute = 123;

        $this->assertSame('3', $model->getRawAttribute('int_attribute'));
        $this->assertSame('4.0', $model->getRawAttribute('float_attribute'));
        $this->assertSame(2.5, $model->getRawAttribute('string_attribute'));
        $this->assertSame(1, $model->getRawAttribute('bool_attribute'));
        $this->assertSame(0, $model->getRawAttribute('boolean_attribute'));
        $this->assertEquals('{"foo":"bar"}', $model->getRawAttribute('object_attribute'));
        $this->assertSame('{"foo":"bar"}', $model->getRawAttribute('array_attribute'));
        $this->assertSame('{"foo":"bar"}', $model->getRawAttribute('json_attribute'));
        $this->assertSame('1969-07-20 00:00:00', $model->getRawAttribute('date_attribute'));
        $this->assertSame('1969-07-20 22:56:00', $model->getRawAttribute('datetime_attribute'));
        $this->assertSame('1969-07-20 22:56:00', $model->getRawAttribute('timestamp_attribute'));
        $this->assertSame('{"foo":"foo_bar","bar":123}', $model->getRawAttribute('custom_attribute'));
        $this->assertSame('{"foo":"foo_bar","bar":123}', $model->getRawAttribute('custom_formatted_attribute'));
        $this->assertSame('foo_bar', $model->getRawAttribute('simple_attribute'));
        $this->assertSame('foo_bar', $model->getRawAttribute('simple_formatted_attribute'));
        $this->assertSame('foo_bar', $model->getRawAttribute('mutated_attribute'));
        $this->assertSame('{"foo":"bar"}', $model->getRawAttribute('mutated_array_attribute'));
        $this->assertSame(123, $model->getRawAttribute('withoutCastAttribute'));
        $this->assertNull($model->getRawAttribute('invalidAttribute'));
        $this->assertNull($model->getRawAttribute(null));
    }

    public function test_model_get_serialized_attribute()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->int_attribute = '3';
        $model->float_attribute = '4.0';
        $model->string_attribute = 2.5;
        $model->bool_attribute = 1;
        $model->boolean_attribute = 0;
        $model->object_attribute = ['foo' => 'bar'];
        $obj = new stdClass;
        $obj->foo = 'bar';
        $model->array_attribute = $obj;
        $model->json_attribute = ['foo' => 'bar'];
        $model->date_attribute = '1969-07-20';
        $model->datetime_attribute = '1969-07-20 22:56:00';
        $model->timestamp_attribute = '1969-07-20 22:56:00';
        $model->custom_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->custom_formatted_attribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->simple_attribute = 'foo_bar';
        $model->simple_formatted_attribute = 'foo_bar';
        $model->mutated_attribute = 'foo_bar';
        $model->mutated_array_attribute = ['foo' => 'bar'];
        $model->withoutCastAttribute = 123;

        $this->assertSame(3, $model->getSerializedAttribute('int_attribute'));
        $this->assertSame(4.0, $model->getSerializedAttribute('float_attribute'));
        $this->assertSame('2.5', $model->getSerializedAttribute('string_attribute'));
        $this->assertTrue($model->getSerializedAttribute('bool_attribute'));
        $this->assertFalse($model->getSerializedAttribute('boolean_attribute'));
        $this->assertEquals($obj, $model->getSerializedAttribute('object_attribute'));
        $this->assertSame(['foo' => 'bar'], $model->getSerializedAttribute('array_attribute'));
        $this->assertSame(['foo' => 'bar'], $model->getSerializedAttribute('json_attribute'));
        $this->assertInstanceOf(Carbon::class, $model->getSerializedAttribute('date_attribute'));
        $this->assertInstanceOf(Carbon::class, $model->getSerializedAttribute('datetime_attribute'));
        $this->assertSame('1969-07-20', $model->getSerializedAttribute('date_attribute')->toDateString());
        $this->assertSame(
            '1969-07-20 22:56:00',
            $model->getSerializedAttribute('datetime_attribute')->toDateTimeString()
        );
        $this->assertSame(-14173440, $model->getSerializedAttribute('timestamp_attribute'));
        $this->assertSame(['foo' => 'fooBar', 'bar' => 123], $model->getSerializedAttribute('custom_attribute'));
        $this->assertSame(
            ['foo' => 'FooBar', 'bar' => 123],
            $model->getSerializedAttribute('custom_formatted_attribute')
        );
        $this->assertSame('fooBar', $model->getSerializedAttribute('simple_attribute'));
        $this->assertSame('FooBar', $model->getSerializedAttribute('simple_formatted_attribute'));
        $this->assertSame('FooBar', $model->getSerializedAttribute('mutated_attribute'));
        $this->assertSame(['foo' => 'bar'], $model->getSerializedAttribute('mutated_array_attribute'));
        $this->assertSame(123, $model->getSerializedAttribute('withoutCastAttribute'));
        $this->assertNull($model->getSerializedAttribute('invalidAttribute'));
        $this->assertNull($model->getSerializedAttribute(null));

        $arr = $model->toArray();

        $this->assertSame(3, $arr['int_attribute']);
        $this->assertSame(4.0, $arr['float_attribute']);
        $this->assertSame('2.5', $arr['string_attribute']);
        $this->assertTrue($arr['bool_attribute']);
        $this->assertFalse($arr['boolean_attribute']);
        $this->assertEquals($obj, $arr['object_attribute']);
        $this->assertSame(['foo' => 'bar'], $arr['array_attribute']);
        $this->assertSame(['foo' => 'bar'], $arr['json_attribute']);
        $this->assertSame('1969-07-20 00:00:00', $arr['date_attribute']);
        $this->assertSame('1969-07-20 22:56:00', $arr['datetime_attribute']);
        $this->assertSame(-14173440, $arr['timestamp_attribute']);
        $this->assertSame(['foo' => 'fooBar', 'bar' => 123], $arr['custom_attribute']);
        $this->assertSame(
            ['foo' => 'FooBar', 'bar' => 123],
            $arr['custom_formatted_attribute']
        );
        $this->assertSame('fooBar', $arr['simple_attribute']);
        $this->assertSame('FooBar', $arr['simple_formatted_attribute']);
        $this->assertSame('FooBar', $arr['mutated_attribute']);
        $this->assertSame(['foo' => 'bar'], $arr['mutated_array_attribute']);
        $this->assertSame(123, $arr['withoutCastAttribute']);
        $this->assertArrayNotHasKey('invalidAttribute', $arr);
    }

    public function test_model_get_queueable_id()
    {
        $model = new EloquentModelCastingStub;
        $model->id = 'foo_bar';
        $this->assertSame('fooBar', $model->getQueueableId());

        $morphPivot = new EloquentModelCastingMorphPivotStub();
        $morphPivot->id = 'foo_bar';
        $this->assertSame('fooBar', $morphPivot->getQueueableId());

        $morphPivot = new EloquentModelCastingMorphPivotStub();
        $this->assertSame(':::::', $morphPivot->getQueueableId());

        $pivot = new EloquentModelCastingPivotStub();
        $pivot->id = 'foo_bar';
        $this->assertSame('fooBar', $pivot->getQueueableId());

        $pivot = new EloquentModelCastingPivotStub();
        $this->assertSame(':::', $pivot->getQueueableId());
    }
}

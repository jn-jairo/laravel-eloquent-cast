<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;
use JnJairo\Laravel\EloquentCast\Tests\TestCase;
use stdClass;

/**
 * @testdox Has attributes cast
 */
class HasAttributesCastTest extends TestCase
{
    public function test_dirty_attributes()
    {
        $model = new EloquentModelStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
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
        $model->boolAttribute = 1;
        $model->foo = 1;
        $model->bar = '2017-03-18';
        $model->dateAttribute = '2017-03-18';
        $model->datetimeAttribute = '2017-03-23 22:17:00';
        $model->myCustomAttribute = '{"foo": "foo_bar", "bar": "123"}';
        $model->myCustomFormattedAttribute = '{"foo": "foo_bar", "bar": "123"}';
        $model->syncOriginal();

        $model->boolAttribute = true;
        $model->foo = true;
        $model->bar = '2017-03-18 00:00:00';
        $model->dateAttribute = '2017-03-18 00:00:00';
        $model->datetimeAttribute = null;
        $model->myCustomAttribute = ['foo' => 'fooBar', 'bar' => 123];
        $model->myCustomFormattedAttribute = ['foo' => 'FooBar', 'bar' => 123];

        $this->assertTrue($model->isDirty());
        $this->assertTrue($model->isDirty('foo'));
        $this->assertTrue($model->isDirty('bar'));
        $this->assertFalse($model->isDirty('boolAttribute'));
        $this->assertFalse($model->isDirty('dateAttribute'));
        $this->assertTrue($model->isDirty('datetimeAttribute'));
        $this->assertFalse($model->isDirty('myCustomAttribute'));
        $this->assertFalse($model->isDirty('myCustomFormattedAttribute'));
    }

    public function test_model_attributes_are_casted_when_present_in_casts_array()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->intAttribute = '3';
        $model->floatAttribute = '4.0';
        $model->stringAttribute = 2.5;
        $model->boolAttribute = 1;
        $model->booleanAttribute = 0;
        $model->objectAttribute = ['foo' => 'bar'];
        $obj = new stdClass;
        $obj->foo = 'bar';
        $model->arrayAttribute = $obj;
        $model->jsonAttribute = ['foo' => 'bar'];
        $model->dateAttribute = '1969-07-20';
        $model->datetimeAttribute = '1969-07-20 22:56:00';
        $model->timestampAttribute = '1969-07-20 22:56:00';
        $model->myCustomAttribute = ['foo' => 'foo_bar', 'bar' => '123'];
        $model->myCustomFormattedAttribute = ['foo' => 'foo_bar', 'bar' => '123'];

        $this->assertIsInt($model->intAttribute);
        $this->assertIsFloat($model->floatAttribute);
        $this->assertIsString($model->stringAttribute);
        $this->assertIsBool($model->boolAttribute);
        $this->assertIsBool($model->booleanAttribute);
        $this->assertIsObject($model->objectAttribute);
        $this->assertIsArray($model->arrayAttribute);
        $this->assertIsArray($model->jsonAttribute);
        $this->assertTrue($model->boolAttribute);
        $this->assertFalse($model->booleanAttribute);
        $this->assertEquals($obj, $model->objectAttribute);
        $this->assertEquals(['foo' => 'bar'], $model->arrayAttribute);
        $this->assertEquals(['foo' => 'bar'], $model->jsonAttribute);
        $this->assertEquals('{"foo":"bar"}', $model->jsonAttributeValue());
        $this->assertInstanceOf(Carbon::class, $model->dateAttribute);
        $this->assertInstanceOf(Carbon::class, $model->datetimeAttribute);
        $this->assertEquals('1969-07-20', $model->dateAttribute->toDateString());
        $this->assertEquals('1969-07-20 22:56:00', $model->datetimeAttribute->toDateTimeString());
        $this->assertEquals(-14173440, $model->timestampAttribute);
        $this->assertInstanceOf(MyCustomType::class, $model->myCustomAttribute);
        $this->assertSame('fooBar', $model->myCustomAttribute->foo);
        $this->assertSame(123, $model->myCustomAttribute->bar);
        $this->assertInstanceOf(MyCustomType::class, $model->myCustomFormattedAttribute);
        $this->assertSame('FooBar', $model->myCustomFormattedAttribute->foo);
        $this->assertSame(123, $model->myCustomFormattedAttribute->bar);

        $arr = $model->toArray();

        $this->assertIsInt($arr['intAttribute']);
        $this->assertIsFloat($arr['floatAttribute']);
        $this->assertIsString($arr['stringAttribute']);
        $this->assertIsBool($arr['boolAttribute']);
        $this->assertIsBool($arr['booleanAttribute']);
        $this->assertIsObject($arr['objectAttribute']);
        $this->assertIsArray($arr['arrayAttribute']);
        $this->assertIsArray($arr['jsonAttribute']);
        $this->assertTrue($arr['boolAttribute']);
        $this->assertFalse($arr['booleanAttribute']);
        $this->assertEquals($obj, $arr['objectAttribute']);
        $this->assertEquals(['foo' => 'bar'], $arr['arrayAttribute']);
        $this->assertEquals(['foo' => 'bar'], $arr['jsonAttribute']);
        $this->assertEquals('1969-07-20 00:00:00', $arr['dateAttribute']);
        $this->assertEquals('1969-07-20 22:56:00', $arr['datetimeAttribute']);
        $this->assertEquals(-14173440, $arr['timestampAttribute']);
        $this->assertIsArray($arr['myCustomAttribute']);
        $this->assertSame('fooBar', $arr['myCustomAttribute']['foo']);
        $this->assertSame(123, $arr['myCustomAttribute']['bar']);
        $this->assertIsArray($arr['myCustomFormattedAttribute']);
        $this->assertSame('FooBar', $arr['myCustomFormattedAttribute']['foo']);
        $this->assertSame(123, $arr['myCustomFormattedAttribute']['bar']);
    }

    public function test_model_date_attribute_casting_resets_time()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->dateAttribute = '1969-07-20 22:56:00';

        $this->assertEquals('1969-07-20 00:00:00', $model->dateAttribute->toDateTimeString());

        $arr = $model->toArray();
        $this->assertEquals('1969-07-20 00:00:00', $arr['dateAttribute']);
    }

    public function test_model_attribute_casting_preserves_null()
    {
        $model = new EloquentModelCastingStub;
        $model->intAttribute = null;
        $model->floatAttribute = null;
        $model->stringAttribute = null;
        $model->boolAttribute = null;
        $model->booleanAttribute = null;
        $model->objectAttribute = null;
        $model->arrayAttribute = null;
        $model->jsonAttribute = null;
        $model->dateAttribute = null;
        $model->datetimeAttribute = null;
        $model->timestampAttribute = null;
        $model->myCustomAttribute = null;
        $model->myCustomFormattedAttribute = null;

        $attributes = $model->getAttributes();

        $this->assertNull($attributes['intAttribute']);
        $this->assertNull($attributes['floatAttribute']);
        $this->assertNull($attributes['stringAttribute']);
        $this->assertNull($attributes['boolAttribute']);
        $this->assertNull($attributes['booleanAttribute']);
        $this->assertNull($attributes['objectAttribute']);
        $this->assertNull($attributes['arrayAttribute']);
        $this->assertNull($attributes['jsonAttribute']);
        $this->assertNull($attributes['dateAttribute']);
        $this->assertNull($attributes['datetimeAttribute']);
        $this->assertNull($attributes['timestampAttribute']);
        $this->assertNull($attributes['myCustomAttribute']);
        $this->assertNull($attributes['myCustomFormattedAttribute']);

        $this->assertNull($model->intAttribute);
        $this->assertNull($model->floatAttribute);
        $this->assertNull($model->stringAttribute);
        $this->assertNull($model->boolAttribute);
        $this->assertNull($model->booleanAttribute);
        $this->assertNull($model->objectAttribute);
        $this->assertNull($model->arrayAttribute);
        $this->assertNull($model->jsonAttribute);
        $this->assertNull($model->dateAttribute);
        $this->assertNull($model->datetimeAttribute);
        $this->assertNull($model->timestampAttribute);
        $this->assertNull($model->myCustomAttribute);
        $this->assertNull($model->myCustomFormattedAttribute);

        $array = $model->toArray();

        $this->assertNull($array['intAttribute']);
        $this->assertNull($array['floatAttribute']);
        $this->assertNull($array['stringAttribute']);
        $this->assertNull($array['boolAttribute']);
        $this->assertNull($array['booleanAttribute']);
        $this->assertNull($array['objectAttribute']);
        $this->assertNull($array['arrayAttribute']);
        $this->assertNull($array['jsonAttribute']);
        $this->assertNull($array['dateAttribute']);
        $this->assertNull($array['datetimeAttribute']);
        $this->assertNull($array['timestampAttribute']);
        $this->assertNull($array['myCustomAttribute']);
        $this->assertNull($array['myCustomFormattedAttribute']);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\JsonEncodingException
     * @expectedExceptionMessage Unable to encode attribute [objectAttribute] for model
     *                           [JnJairo\Laravel\EloquentCast\Tests\EloquentModelCastingStub] to JSON:
     *                           Malformed UTF-8 characters, possibly incorrectly encoded.
     */
    public function test_model_attribute_casting_fails_on_unencodable_data()
    {
        $model = new EloquentModelCastingStub;
        $model->objectAttribute = ['foo' => "b\xF8r"];
        $obj = new stdClass;
        $obj->foo = "b\xF8r";
        $model->arrayAttribute = $obj;
        $model->jsonAttribute = ['foo' => "b\xF8r"];

        $model->getAttributes();
    }

    /**
     * @requires function \Illuminate\Database\Eloquent\Concerns\HasAttributes::fromFloat
     */
    public function test_model_attribute_casting_with_special_float_values()
    {
        $model = new EloquentModelCastingStub;

        $model->floatAttribute = 0;
        $this->assertSame(0.0, $model->floatAttribute);

        $model->floatAttribute = 'Infinity';
        $this->assertSame(INF, $model->floatAttribute);

        $model->floatAttribute = INF;
        $this->assertSame(INF, $model->floatAttribute);

        $model->floatAttribute = '-Infinity';
        $this->assertSame(-INF, $model->floatAttribute);

        $model->floatAttribute = -INF;
        $this->assertSame(-INF, $model->floatAttribute);

        $model->floatAttribute = 'NaN';
        $this->assertNan($model->floatAttribute);

        $model->floatAttribute = NAN;
        $this->assertNan($model->floatAttribute);
    }
}

class EloquentModelStub extends Model
{
    use HasAttributesCast;

    protected $guarded = [];
}

class MyCustomType
{
    public $foo;
    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

trait MyCustomTypeCast
{
    protected function castMyCustomTypeAttribute($value, $format = '', $serialized = false)
    {
        if (is_string($value)) {
            $value = $this->fromJson($value);
        }

        if (is_array($value)) {
            $value = new MyCustomType($value['foo'], $value['bar']);
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

    protected function uncastMyCustomTypeAttribute($value, $format = '')
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $value = new MyCustomType($value['foo'], $value['bar']);
        }

        $value->foo = snake_case($value->foo);
        $value->bar = (int) $value->bar;

        $value = ['foo' => $value->foo, 'bar' => $value->bar];

        return $this->asJson($value);
    }
}

class EloquentModelCastingStub extends EloquentModelStub
{
    use MyCustomTypeCast;

    protected $casts = [
        'intAttribute' => 'int',
        'floatAttribute' => 'float',
        'stringAttribute' => 'string',
        'boolAttribute' => 'bool',
        'booleanAttribute' => 'boolean',
        'objectAttribute' => 'object',
        'arrayAttribute' => 'array',
        'jsonAttribute' => 'json',
        'dateAttribute' => 'date',
        'datetimeAttribute' => 'datetime',
        'timestampAttribute' => 'timestamp',
        'myCustomAttribute' => 'my_custom_type',
        'myCustomFormattedAttribute' => 'my_custom_type:studly',
    ];

    public function jsonAttributeValue()
    {
        return $this->attributes['jsonAttribute'];
    }
}

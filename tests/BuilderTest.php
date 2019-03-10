<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use JnJairo\Laravel\EloquentCast\HasAttributesCast;
use JnJairo\Laravel\EloquentCast\Tests\Stubs\EloquentModelCastingStub;
use JnJairo\Laravel\EloquentCast\Tests\TestCase;

/**
 * @testdox Eloquent builder
 */
class BuilderTest extends TestCase
{
    public function test_where()
    {
        $model = new EloquentModelCastingStub();
        $this->mockConnectionForModel($model, '');

        $builder = $model->where('simple_attribute', 'fooBar');
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" = ?',
            $builder->toSql(),
            'Simple where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'Simple where binding'
        );

        $builder = $model->where('simple_attribute', 'fooBar')->orWhere('simple_attribute', 'barFoo');
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" = ? or "simple_attribute" = ?',
            $builder->toSql(),
            'Simple or where SQL'
        );
        $this->assertSame(
            ['foo_bar', 'bar_foo'],
            $builder->getBindings(),
            'Simple or where binding'
        );

        $builder = $model->where(['simple_attribute' => 'fooBar']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where ("simple_attribute" = ?)',
            $builder->toSql(),
            'Array where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'Array where binding'
        );

        $builder = $model->where([['simple_attribute', 'fooBar']]);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where ("simple_attribute" = ?)',
            $builder->toSql(),
            'Array of array where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'Array of array where binding'
        );

        $builder = $model->where(function ($query) {
            $query->where('simple_attribute', 'fooBar');
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where ("simple_attribute" = ?)',
            $builder->toSql(),
            'Nested where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'Nested where binding'
        );

        $builder = $model->where('simple_attribute', 'fooBar', 'invalid');
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" = ?',
            $builder->toSql(),
            'Invalid operator where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'Invalid operator where binding'
        );

        $builder = $model->where('simple_attribute', '!=', 'fooBar');
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" != ?',
            $builder->toSql(),
            'Cast operator where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'Cast operator where binding'
        );

        $builder = $model->where('simple_attribute', 'like', '%fooBar%');
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" like ?',
            $builder->toSql(),
            'No cast operator where SQL'
        );
        $this->assertSame(
            ['%fooBar%'],
            $builder->getBindings(),
            'No cast operator where binding'
        );

        $builder = $model->where('simple_attribute', null);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" is null',
            $builder->toSql(),
            'Is null where SQL'
        );
        $this->assertEmpty(
            $builder->getBindings(),
            'Is null where binding'
        );

        $builder = $model->where('simple_attribute', function ($query) use ($model) {
            $query->from('eloquent_model_casting_stubs')
                ->select('simple_attribute')
                ->where('simple_attribute', 'fooBar')
                ->limit(1);
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" = ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs" where "simple_attribute" = ? limit 1)',
            $builder->toSql(),
            'Sub select where SQL'
        );
        $this->assertSame(
            ['fooBar'],
            $builder->getBindings(),
            'Sub select where binding'
        );

        $builder = $model->where('simple_attribute', function ($query) use ($model) {
            return $query->from('eloquent_model_casting_stubs')
                ->select('simple_attribute')
                ->where('simple_attribute', 'fooBar')
                ->limit(1);
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" = ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs" where "simple_attribute" = ? limit 1)',
            $builder->toSql(),
            'New sub select where SQL'
        );
        $this->assertSame(
            ['fooBar'],
            $builder->getBindings(),
            'New sub select where binding'
        );

        $builder = $model->where('simple_attribute', function ($query) use ($model) {
            return $model->select('simple_attribute')->where('simple_attribute', 'fooBar')->limit(1);
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" = ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs" where "simple_attribute" = ? limit 1)',
            $builder->toSql(),
            'New eloquent sub select where SQL'
        );
        $this->assertSame(
            ['foo_bar'],
            $builder->getBindings(),
            'New eloquent sub select where binding'
        );

        $modelJson = new EloquentModelCastingStub();
        $this->mockConnectionForModel($modelJson, 'SQLite');

        $builder = $model->where('custom_attribute->foo', 'fooBar');
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where json_extract("custom_attribute", \'$."foo"\') = ?',
            $builder->toSql(),
            'JSON where SQL'
        );
        $this->assertSame(
            ['fooBar'],
            $builder->getBindings(),
            'JSON where binding'
        );

        $builder = $model->where('custom_attribute->bar', true);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where json_extract("custom_attribute", \'$."bar"\') = true',
            $builder->toSql(),
            'JSON bool where SQL'
        );
        $this->assertEmpty(
            $builder->getBindings(),
            'JSON bool where binding'
        );
    }

    public function test_where_in()
    {
        $model = new EloquentModelCastingStub();
        $this->mockConnectionForModel($model, '');

        $builder = $model->whereIn('simple_attribute', ['fooBar', 'FooBar']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in (?, ?)',
            $builder->toSql(),
            'Simple where in SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar'],
            $builder->getBindings(),
            'Simple where in binding'
        );

        $builder = $model->whereIn('simple_attribute', ['fooBar', 'FooBar'])
            ->orWhereIn('simple_attribute', ['barFoo', 'BarFoo']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in (?, ?) or ' .
            '"simple_attribute" in (?, ?)',
            $builder->toSql(),
            'Simple or where in SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar', 'bar_foo', 'bar_foo'],
            $builder->getBindings(),
            'Simple or where in binding'
        );

        $builder = $model->whereNotIn('simple_attribute', ['fooBar', 'FooBar']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" not in (?, ?)',
            $builder->toSql(),
            'Simple where not in SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar'],
            $builder->getBindings(),
            'Simple where not in binding'
        );

        $builder = $model->whereNotIn('simple_attribute', ['fooBar', 'FooBar'])
            ->orWhereNotIn('simple_attribute', ['barFoo', 'BarFoo']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" not in (?, ?) or ' .
            '"simple_attribute" not in (?, ?)',
            $builder->toSql(),
            'Simple or where not in SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar', 'bar_foo', 'bar_foo'],
            $builder->getBindings(),
            'Simple or where not in binding'
        );

        $builder = $model->whereIn('simple_attribute', collect(['fooBar', 'FooBar']));
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in (?, ?)',
            $builder->toSql(),
            'Arrayable where in SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar'],
            $builder->getBindings(),
            'Arrayable where in binding'
        );

        $builder = $model->whereIn('simple_attribute', function ($query) {
            $query->from('eloquent_model_casting_stubs')
                ->select('simple_attribute')
                ->whereIn('simple_attribute', ['fooBar', 'FooBar']);
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs" where "simple_attribute" in (?, ?))',
            $builder->toSql(),
            'Sub select where in SQL'
        );
        $this->assertSame(
            ['fooBar', 'FooBar'],
            $builder->getBindings(),
            'Sub select where in binding'
        );

        $builder = $model->whereIn('simple_attribute', function ($query) {
            return $query->from('eloquent_model_casting_stubs')
                ->select('simple_attribute')
                ->whereIn('simple_attribute', ['fooBar', 'FooBar']);
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs" where "simple_attribute" in (?, ?))',
            $builder->toSql(),
            'New sub select where in SQL'
        );
        $this->assertSame(
            ['fooBar', 'FooBar'],
            $builder->getBindings(),
            'New sub select where in binding'
        );

        $builder = $model->whereIn('simple_attribute', function ($query) use ($model) {
            return $model->select('simple_attribute')
                ->whereIn('simple_attribute', ['fooBar', 'FooBar']);
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs" where "simple_attribute" in (?, ?))',
            $builder->toSql(),
            'Eloquent sub select where in SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar'],
            $builder->getBindings(),
            'Eloquent sub select where in binding'
        );

        $builder = $model->whereIn('simple_attribute', function ($query) {
            return $query->from('eloquent_model_casting_stubs')
                ->select('simple_attribute')
                ->toSql();
        });
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" in ' .
            '(select "simple_attribute" from "eloquent_model_casting_stubs")',
            $builder->toSql(),
            'String sub select where in SQL'
        );
        $this->assertEmpty(
            $builder->getBindings(),
            'String sub select where in binding'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_where_in_invalid_sub_select()
    {
        $model = new EloquentModelCastingStub();
        $this->mockConnectionForModel($model, '');

        $builder = $model->whereIn('simple_attribute', function ($query) {
            return array('invalid');
        });
    }

    public function test_where_between()
    {
        $model = new EloquentModelCastingStub();
        $this->mockConnectionForModel($model, '');

        $builder = $model->whereBetween('simple_attribute', ['fooBar', 'FooBar']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" between ? and ?',
            $builder->toSql(),
            'Simple where between SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar'],
            $builder->getBindings(),
            'Simple where between binding'
        );

        $builder = $model->whereBetween('simple_attribute', ['fooBar', 'FooBar'])
            ->orWhereBetween('simple_attribute', ['barFoo', 'BarFoo']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" between ? and ? or ' .
            '"simple_attribute" between ? and ?',
            $builder->toSql(),
            'Simple or where between SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar', 'bar_foo', 'bar_foo'],
            $builder->getBindings(),
            'Simple or where between binding'
        );

        $builder = $model->whereNotBetween('simple_attribute', ['fooBar', 'FooBar']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" not between ? and ?',
            $builder->toSql(),
            'Simple where not between SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar'],
            $builder->getBindings(),
            'Simple where not between binding'
        );

        $builder = $model->whereNotBetween('simple_attribute', ['fooBar', 'FooBar'])
            ->orWhereNotBetween('simple_attribute', ['barFoo', 'BarFoo']);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" not between ? and ? or ' .
            '"simple_attribute" not between ? and ?',
            $builder->toSql(),
            'Simple or where not between SQL'
        );
        $this->assertSame(
            ['foo_bar', 'foo_bar', 'bar_foo', 'bar_foo'],
            $builder->getBindings(),
            'Simple or where not between binding'
        );

        $builder = $model->whereBetween('simple_attribute', [new Expression('fooBar'), new Expression('FooBar')]);
        $this->assertSame(
            'select * from "eloquent_model_casting_stubs" where "simple_attribute" between fooBar and FooBar',
            $builder->toSql(),
            'Raw where between SQL'
        );
        $this->assertEmpty(
            $builder->getBindings(),
            'Raw where between binding'
        );
    }

    protected function mockConnectionForModel($model, $database)
    {
        $grammarClass = 'Illuminate\Database\Query\Grammars\\'.$database.'Grammar';
        $processorClass = 'Illuminate\Database\Query\Processors\\'.$database.'Processor';
        $grammar = new $grammarClass;
        $processor = new $processorClass;
        $connection = $this->prophesize(Connection::class);
        $connection->getQueryGrammar()->willReturn($grammar);
        $connection->getPostProcessor()->willReturn($processor);
        $connection->query()->will(function () use ($connection, $grammar, $processor) {
            return new BaseBuilder($connection->reveal(), $grammar, $processor);
        });
        $resolver = $this->prophesize(ConnectionResolverInterface::class);
        $resolver->connection(\Prophecy\Argument::any())->willReturn($connection->reveal());
        $class = get_class($model);
        $class::setConnectionResolver($resolver->reveal());
    }
}

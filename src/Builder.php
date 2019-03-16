<?php

namespace JnJairo\Laravel\EloquentCast;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Builder extends EloquentBuilder
{
    /**
     * List of the operators to uncast in the where.
     *
     * @var array
     */
    public $whereCastOperators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
    ];

    /**
     * Add a basic where clause to the query.
     *
     * @param string|array|\Closure $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            $this->addArrayOfWheres($column, $boolean);
            return $this;
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->query->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        // If the columns is actually a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parenthesis.
        // We'll add that Closure to the query then return back out immediately.
        if ($column instanceof Closure) {
            $this->whereNested($column, $boolean);
            return $this;
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is a Closure, it means the developer is performing an entire
        // sub-select within the query and we will need to compile the sub-select
        // within the where clause to get the appropriate query record results.
        if ($value instanceof Closure) {
            $this->whereSub($column, $operator, $value, $boolean);
            return $this;
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            parent::whereNull($column, $boolean, $operator !== '=');
            return $this;
        }

        $type = 'Basic';

        // If the column is making a JSON reference we'll check to see if the value
        // is a boolean. If it is, we'll add the raw boolean string as an actual
        // value to the query to ensure this is properly handled by the query.
        if (Str::contains($column, '->') && is_bool($value)) {
            $value = new Expression($value ? 'true' : 'false');

            if (is_string($column) && method_exists($this->query->getGrammar(), 'whereJsonBoolean')) {
                $type = 'JsonBoolean';
            }
        }

        if (! $value instanceof Expression &&
            ! $column instanceof Expression &&
            in_array($operator, $this->whereCastOperators)) {
            [$qualifiedTable, $qualifiedColumn] = explode('.', $this->model->qualifyColumn($column), 2);
            if ($qualifiedTable === $this->model->getTable()) {
                $value = $this->model->uncastAttribute($qualifiedColumn, $value);
            }
        }

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $this->query->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $value instanceof Expression) {
            $this->query->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // If the value is a query builder instance we will assume the developer wants to
        // look for any values that exists within this given query. So we will add the
        // query accordingly so that this query is properly executed when it is run.
        if ($values instanceof EloquentBuilder ||
            $values instanceof QueryBuilder ||
            $values instanceof Closure) {
            [$query, $bindings] = $this->createSub($values);

            $values = [new Expression($query)];

            $this->query->addBinding($bindings, 'where');
        }

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        if (! $column instanceof Expression && is_array($values)) {
            [$qualifiedTable, $qualifiedColumn] = explode('.', $this->model->qualifyColumn($column), 2);
            if ($qualifiedTable === $this->model->getTable()) {
                $values = array_map(function ($value) use ($qualifiedColumn) {
                    if (! $value instanceof Expression) {
                        $value = $this->model->uncastAttribute($qualifiedColumn, $value);
                    }
                    return $value;
                }, $values);
            }
        }

        $this->query->wheres[] = compact('type', 'column', 'values', 'boolean');

        // Finally we'll add a binding for each values unless that value is an expression
        // in which case we will just skip over it since it will be the query as a raw
        // string and not as a parameterized place-holder to be replaced by the PDO.
        $this->query->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a where between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        if (! $column instanceof Expression) {
            [$qualifiedTable, $qualifiedColumn] = explode('.', $this->model->qualifyColumn($column), 2);
            if ($qualifiedTable === $this->model->getTable()) {
                $values = array_map(function ($value) use ($qualifiedColumn) {
                    if (! $value instanceof Expression) {
                        $value = $this->model->uncastAttribute($qualifiedColumn, $value);
                    }
                    return $value;
                }, $values);
            }
        }

        $this->query->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->query->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function whereNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function orWhereNotBetween($column, array $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param \Closure $callback
     * @param string $boolean
     * @return \JnJairo\Laravel\EloquentCast\Builder|static
     */
    public function whereNested(Closure $callback, $boolean = 'and')
    {
        call_user_func($callback, $query = $this->model->newModelQuery());

        $this->query->addNestedWhereQuery($query->getQuery(), $boolean);

        return $this;
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param array $column
     * @param string $boolean
     * @param string $method
     * @return $this
     */
    protected function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param string $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! in_array(strtolower($operator), $this->query->operators, true) &&
            ! in_array(strtolower($operator), $this->query->grammar->getOperators(), true);
    }

    /**
     * Add a full sub-select to the query.
     *
     * @param string $column
     * @param string $operator
     * @param \Closure $callback
     * @param string $boolean
     * @return $this
     */
    protected function whereSub($column, $operator, Closure $callback, $boolean)
    {
        $type = 'Sub';

        // Once we have the query instance we can simply execute it so it can add all
        // of the sub-select's conditions to itself, and then we can cache it off
        // in the array of where clauses for the "main" parent query instance.
        $newQuery = call_user_func($callback, $query = $this->query->newQuery());

        if ($newQuery instanceof EloquentBuilder) {
            $query = $newQuery->getQuery();
        } elseif ($newQuery instanceof QueryBuilder) {
            $query = $newQuery;
        }

        $this->query->wheres[] = compact('type', 'column', 'operator', 'query', 'boolean');

        $this->query->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Creates a subquery and parse it.
     *
     * @param \Closure|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|string $query
     * @return array
     */
    protected function createSub($query)
    {
        // If the given query is a Closure, we will execute it while passing in a new
        // query instance to the Closure. This will give the developer a chance to
        // format and work with the query before we cast it to a raw SQL string.
        if ($query instanceof Closure) {
            $callback = $query;

            $newQuery = call_user_func($callback, $query = $this->query->newQuery());

            if ($newQuery instanceof EloquentBuilder) {
                $query = $newQuery->getQuery();
            } elseif (! is_null($newQuery)) {
                $query = $newQuery;
            }
        }

        return $this->parseSub($query);
    }

    /**
     * Parse the subquery into SQL and bindings.
     *
     * @param mixed $query
     * @return array
     */
    protected function parseSub($query)
    {
        if ($query instanceof EloquentBuilder || $query instanceof QueryBuilder) {
            return [$query->toSql(), $query->getBindings()];
        } elseif (is_string($query)) {
            return [$query, []];
        } else {
            throw new InvalidArgumentException;
        }
    }

    /**
     * Remove all of the expressions from a list of bindings.
     *
     * @param array $bindings
     * @return array
     */
    protected function cleanBindings(array $bindings)
    {
        return array_values(array_filter($bindings, function ($binding) {
            return ! $binding instanceof Expression;
        }));
    }
}

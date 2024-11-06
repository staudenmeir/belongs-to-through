<?php

namespace Znck\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\Relation<TRelatedModel, TDeclaringModel, ?TRelatedModel>
 */
class BelongsToThrough extends Relation
{
    use SupportsDefaultModels;

    /**
     * The column alias for the local key on the first "through" parent model.
     *
     * @var string
     */
    public const THROUGH_KEY = 'laravel_through_key';

    /**
     * The "through" parent model instances.
     *
     * @var non-empty-list<\Illuminate\Database\Eloquent\Model>
     */
    protected $throughParents;

    /**
     * The foreign key prefix for the first "through" parent model.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The custom foreign keys on the relationship.
     *
     * @var array<string, string>
     */
    protected $foreignKeyLookup;

    /**
     * The custom local keys on the relationship.
     *
     * @var array<string, string>
     */
    protected $localKeyLookup;

    /**
     * Create a new belongs to through relationship instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder<TRelatedModel> $query
     * @param TDeclaringModel $parent
     * @param non-empty-list<\Illuminate\Database\Eloquent\Model> $throughParents
     * @param string|null $localKey
     * @param string $prefix
     * @param array<string, string> $foreignKeyLookup
     * @param array<string, string> $localKeyLookup
     * @return void
     *
     * @phpstan-ignore constructor.unusedParameter($localKey)
     */
    public function __construct(
        Builder $query,
        Model $parent,
        array $throughParents,
        $localKey = null,
        $prefix = '',
        array $foreignKeyLookup = [],
        array $localKeyLookup = []
    ) {
        $this->throughParents = $throughParents;
        $this->prefix = $prefix;
        $this->foreignKeyLookup = $foreignKeyLookup;
        $this->localKeyLookup = $localKeyLookup;

        parent::__construct($query, $parent);
    }

    /** @inheritDoc */
    public function addConstraints()
    {
        $this->performJoins();

        if (static::$constraints) {
            $localValue = $this->parent[$this->getFirstForeignKeyName()];

            $this->query->where($this->getQualifiedFirstLocalKeyName(), '=', $localValue);
        }
    }

    /**
     * Set the join clauses on the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|null $query
     * @return void
     */
    protected function performJoins(?Builder $query = null)
    {
        $query = $query ?: $this->query;

        foreach ($this->throughParents as $i => $model) {
            $predecessor = $i > 0 ? $this->throughParents[$i - 1] : $this->related;

            $first = $model->qualifyColumn($this->getForeignKeyName($predecessor));

            $second = $predecessor->qualifyColumn($this->getLocalKeyName($predecessor));

            $query->join($model->getTable(), $first, '=', $second);

            if ($this->hasSoftDeletes($model)) {
                /** @phpstan-ignore method.notFound */
                $column = $model->getQualifiedDeletedAtColumn();

                $query->withGlobalScope(__CLASS__ . ":{$column}", function (Builder $query) use ($column) {
                    $query->whereNull($column);
                });
            }
        }
    }

    /**
     * Get the foreign key for a model.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $model
     * @return string
     */
    public function getForeignKeyName(?Model $model = null)
    {
        $table = explode(' as ', ($model ?? $this->parent)->getTable())[0];

        if (array_key_exists($table, $this->foreignKeyLookup)) {
            return $this->foreignKeyLookup[$table];
        }

        return Str::singular($table) . '_id';
    }

    /**
     * Get the local key for a model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    public function getLocalKeyName(Model $model): string
    {
        $table = explode(' as ', $model->getTable())[0];

        if (array_key_exists($table, $this->localKeyLookup)) {
            return $this->localKeyLookup[$table];
        }

        return $model->getKeyName();
    }

    /**
     * Determine whether a model uses SoftDeletes.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    public function hasSoftDeletes(Model $model)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }

    /** @inheritDoc */
    public function addEagerConstraints(array $models)
    {
        $keys = $this->getKeys($models, $this->getFirstForeignKeyName());

        $this->query->whereIn($this->getQualifiedFirstLocalKeyName(), $keys);
    }

    /** @inheritDoc */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /** @inheritDoc */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model[$this->getFirstForeignKeyName()];

            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, TRelatedModel> $results
     * @return TRelatedModel[]
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result[static::THROUGH_KEY]] = $result;

            unset($result[static::THROUGH_KEY]);
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return TRelatedModel|object|static|null
     */
    public function getResults()
    {
        return $this->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param string[] $columns
     * @return TRelatedModel|object|static|null
     */
    public function first($columns = ['*'])
    {
        if ($columns === ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return $this->query->first($columns);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param list<string> $columns
     * @return \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>
     */
    public function get($columns = ['*'])
    {
        $columns = $this->query->getQuery()->columns ? [] : $columns;

        if ($columns === ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        $columns[] = $this->getQualifiedFirstLocalKeyName() . ' as ' . static::THROUGH_KEY;

        $this->query->addSelect($columns);

        return $this->query->get();
    }

    /** @inheritDoc */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $this->performJoins($query);

        $from = $parentQuery->getQuery()->from;

        if ($from instanceof Expression) {
            $from = $from->getValue(
                $parentQuery->getGrammar()
            );
        }

        $foreignKey = $from . '.' . $this->getFirstForeignKeyName();

        /** @var \Illuminate\Database\Eloquent\Builder<TRelatedModel> $query */
        $query = $query->select($columns)->whereColumn(
            $this->getQualifiedFirstLocalKeyName(),
            '=',
            $foreignKey
        );

        return $query;
    }

    /**
     * Restore soft-deleted models.
     *
     * @param string[]|string ...$columns
     * @return $this
     */
    public function withTrashed(...$columns)
    {
        if (empty($columns)) {
            /** @phpstan-ignore method.notFound */
            $this->query->withTrashed();

            return $this;
        }

        if (is_array($columns[0])) {
            $columns = $columns[0];
        }

        /** @var string[] $columns */
        foreach ($columns as $column) {
            $this->query->withoutGlobalScope(__CLASS__ . ":$column");
        }

        return $this;
    }

    /**
     * Get the "through" parent model instances.
     *
     * @return list<\Illuminate\Database\Eloquent\Model>
     */
    public function getThroughParents()
    {
        return $this->throughParents;
    }

    /**
     * Get the foreign key for the first "through" parent model.
     *
     * @return string
     */
    public function getFirstForeignKeyName()
    {
        $firstThroughParent = end($this->throughParents);

        return $this->prefix . $this->getForeignKeyName($firstThroughParent);
    }

    /**
     * Get the qualified local key for the first "through" parent model.
     *
     * @return string
     */
    public function getQualifiedFirstLocalKeyName()
    {
        $firstThroughParent = end($this->throughParents);

        return $firstThroughParent->qualifyColumn($this->getLocalKeyName($firstThroughParent));
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $parent
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }
}

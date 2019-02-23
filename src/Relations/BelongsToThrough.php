<?php

namespace Znck\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

class BelongsToThrough extends Relation
{
    /**
     * Column alias for matching eagerly loaded models.
     *
     * @var string
     */
    const RELATED_THROUGH_KEY = '__deep_related_through_key';

    /**
     * List of intermediate model instances.
     *
     * @var \Illuminate\Database\Eloquent\Model[]
     */
    protected $models;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    /**
     * TODO
     *
     * @var string
     */
    private $prefix;

    /**
     * An array of table names and their foreign keys.
     *
     * @var array
     */
    private $foreignKeyLookup;

    /**
     * Create a new belongs to through relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @param  string|null  $localKey
     * @param  string  $prefix
     * @param  array  $foreignKeyLookup
     * @return void
     */
    public function __construct(Builder $query, Model $parent, array $models, $localKey = null, $prefix = '', $foreignKeyLookup = [])
    {
        $this->models = $models;
        $this->localKey = $localKey ?: $parent->getKeyName();
        $this->prefix = $prefix;
        $this->foreignKeyLookup = $foreignKeyLookup;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $this->setJoins();

        $this->query->select([$this->getRelated()->getTable().'.*']);

        if (static::$constraints) {
            $this->query->where($this->getQualifiedParentKeyName(), '=', $this->parent[$this->localKey]);

            $this->query->whereNotNull($this->getQualifiedParentKeyName());

            $this->setSoftDeletes();
        }
    }

    /**
     * Set the required joins on the relation query.
     *
     * @return void
     */
    protected function setJoins()
    {
        $one = $this->getRelated()->getQualifiedKeyName();
        $prev = $this->getForeignKey($this->getRelated());
        $lastIndex = count($this->models) - 1;

        foreach ($this->models as $index => $model) {
            if ($lastIndex === $index) {
                $prev = $this->prefix.$prev;
            }

            $other = $model->getTable().'.'.$prev;

            $this->query->leftJoin($model->getTable(), $one, '=', $other);

            $prev = $this->getForeignKey($model);

            $one = $model->getQualifiedKeyName();
        }
    }

    /**
     * Get foreign key column name for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function getForeignKey(Model $model)
    {
        $table = $model->getTable();

        if (array_key_exists($table, $this->foreignKeyLookup)) {
            return $this->foreignKeyLookup[$table];
        }

        return Str::singular($table).'_id';
    }

    /**
     * Set the soft deleting constraints on the relation query.
     *
     * @return void
     */
    protected function setSoftDeletes()
    {
        foreach ($this->models as $model) {
            if ($model === $this->parent) {
                continue;
            }

            if ($this->hasSoftDeletes($model)) {
                $this->query->whereNull($model->getQualifiedDeletedAtColumn());
            }
        }
    }

    /**
     * Determine whether the model uses Soft Deletes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function hasSoftDeletes(Model $model)
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_class($model)));
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->addSelect([
            $this->getParent()->getQualifiedKeyName().' as '.static::RELATED_THROUGH_KEY,
        ]);

        $this->query->whereIn($this->getParent()->getQualifiedKeyName(), $this->getKeys($models, $this->localKey));
    }

    /**
     * TODO
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    protected function setRelationQueryConstraints(Builder $query)
    {
        $one = $this->getRelated()->getQualifiedKeyName();
        $prev = $this->getForeignKey($this->getRelated());
        $alias = null;
        $lastIndex = count($this->models);
        foreach ($this->models as $index => $model) {
            if ($lastIndex === $index) {
                $prev = $this->prefix.$prev; // TODO: Check if this line is really necessary. Its not covered by any of the tests.
            }
            if ($this->getParent()->getTable() === $model->getTable()) {
                $alias = $model->getTable().'_'.time();
                $other = $alias.'.'.$prev;
                $query->leftJoin(new Expression($model->getTable().' as '.$alias), $one, '=', $other);
            } else {
                $other = $model->getTable().'.'.$prev;
                $query->leftJoin($model->getTable(), $one, '=', $other);
            }

            $prev = $this->getForeignKey($model);
            $one = $model->getQualifiedKeyName();
        }

        $key = $this->parent
            ->newQueryWithoutScopes()
            ->getQuery()
            ->getGrammar()
            ->wrap($this->getQualifiedParentKeyName());

        $query->where(new Expression($alias.'.'.$this->getParent()->getKeyName()), '=', new Expression($key));
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $parent
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parent, $columns = ['*'])
    {
        $query->select($columns);

        $this->setRelationQueryConstraints($query);

        return $query;
    }

    /**
     * Get the results of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getResults()
    {
        return $this->query->first();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getRelated());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->localKey};

            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{static::RELATED_THROUGH_KEY}] = $result;

            unset($result[static::RELATED_THROUGH_KEY]);
        }

        return $dictionary;
    }
}

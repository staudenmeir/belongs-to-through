<?php namespace Znck\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class BelongsToThroughDeep
 *
 * @package Znck\Eloquent\Relations
 */
class BelongsToThrough extends Relation
{
    /**
     *
     */
    const RELATED_THROUGH_KEY = '__deep_related_through_key';
    /**
     * @type array|\Illuminate\Database\Eloquent\Model[]
     */
    protected $models;
    /**
     * @type string|null
     */
    protected $localKey;

    /**
     * BelongsToThroughDeep constructor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $parent
     * @param \Illuminate\Database\Eloquent\Model[] $models
     * @param string|null $localKey
     */
    public function __construct(Builder $query, Model $parent, array $models, $localKey = null)
    {
        $this->models = $models;
        $this->localKey = $localKey ?: $parent->getKeyName();

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

        $this->getQuery()->select([$this->getRelated()->getTable() . '.*']);

        if (static::$constraints) {
            $this->getQuery()->where($this->getQualifiedParentKeyName(), '=', $this->parent[$this->localKey]);
        }

        if ($this->parentSoftDeletes()) {
            $this->setSoftDeletingConstraints();
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     *
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->getQuery()->addSelect([$this->getParent()->getQualifiedKeyName() . ' as ' . self::RELATED_THROUGH_KEY]);
        $this->getQuery()->whereIn($this->getParent()->getQualifiedKeyName(), $this->getKeys($models, $this->localKey));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  \Illuminate\Database\Eloquent\Model[] $models
     * @param  string $relation
     *
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
     * @param  array $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->localKey};

            if (isset($dictionary[$key])) {
                $val = $dictionary[$key];

                $model->setRelation($relation, $val);
            }
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->getQuery()->first();
    }

    /**
     * @return void
     */
    protected function setJoins()
    {
        $one = $this->getRelated()->getQualifiedKeyName();
        $prev = $this->getRelated()->getForeignKey();
        foreach ($this->models as $key => $model) {
            $other = $model->getTable() . '.' . $prev;
            $this->getQuery()->leftJoin($model->getTable(), $one, '=', $other);

            $prev = $model->getForeignKey();
            $one = $model->getQualifiedKeyName();
        }
    }

    /**
     * @return bool
     */
    public function parentSoftDeletes()
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes',
            class_uses_recursive(get_class($this->getParent())));
    }

    /**
     * @return void
     */
    protected function setSoftDeletingConstraints()
    {
        $this->getQuery()->whereNull($this->getParent()->getQualifiedDeletedAtColumn());
        // Note: Don't know if there is need to check intermediate models.
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $results
     *
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{static::RELATED_THROUGH_KEY}] = $result;
            $result->offsetUnset(static::RELATED_THROUGH_KEY);
        }

        return $dictionary;
    }
}
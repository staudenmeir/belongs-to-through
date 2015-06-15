<?php namespace Znck\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * This file belongs to server.
 *
 * Author: Rahul Kadyan, <hi@znck.me>
 * Find license in root directory of this project.
 */
class BelongsToThrough extends Relation
{
    /**
     * The distance child model instance.
     *
     * @type \Illuminate\Database\Eloquent\Model
     */
    protected $farChild;
    /**
     * The near key on the relationship.
     *
     * @type string
     */
    protected $firstKey;
    /**
     * The local key on the relationship.
     *
     * @type string
     */
    protected $localKey;

    /**
     * Create a new relation instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model   $farChild
     * @param \Illuminate\Database\Eloquent\Model   $parent
     * @param string                                $firstKey
     * @param string                                $localKey
     */
    function __construct(Builder $query, Model $farChild, Model $parent, $firstKey, $localKey)
    {
        $this->farChild = $farChild;
        $this->firstKey = $firstKey;
        $this->localKey = $localKey;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $parentTable = $this->parent->getTable();

        $localValue = $this->farChild[$this->localKey];

        $this->setJoin();

        $this->query->addSelect([$this->related->getTable() . '.*']);

        if (static::$constraints) {
            $this->query->where($parentTable . '.' . $this->parent->getKeyName(), '=', $localValue);
        }
    }

    /**
     * Set the join clause on the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null $query
     *
     * @return void
     */
    protected function setJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;

        $parentTable = $this->parent->getTable();

        $foreignKey = $parentTable . '.' . $this->firstKey;

        $query->join($parentTable, $this->related->getQualifiedKeyName(), '=', $foreignKey);

        if ($this->parentSoftDeletes()) {
            $query->whereNull($this->parent->getQualifiedDeletedAtColumn());
        }
    }

    /**
     * Determine whether close parent of the relation uses Soft Deletes.
     *
     * @return bool
     */
    public function parentSoftDeletes()
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(get_class($this->parent)));
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
        $table = $this->parent->getTable();

        $this->query->addSelect([$this->parent->getTable() . '.' . $this->parent->getKeyName() .' as __related_through_key']);

        $this->query->whereIn($table . '.' . $this->parent->getKeyName(), $this->getKeys($models, $this->localKey));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related);
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array                                    $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string                                   $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the child models to
        // link them up with their parent using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->{$this->localKey};

            if (isset($dictionary[$key])) {
                $value = $dictionary[$key];

                $model->setRelation($relation, $value);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $results
     *
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        $foreign = '__related_through_key';

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            $dictionary[$result->{$foreign}] = $result;
            $result->offsetUnset($foreign);
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->first();
    }

    public function get()
    {
        return $this->query->get();
    }
}
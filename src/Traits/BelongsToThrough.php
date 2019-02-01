<?php

namespace Znck\Eloquent\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Relations\BelongsToThrough as Relation;

trait BelongsToThrough
{
    /**
     * Define a belongs-to-through relationship.
     *
     * @param  string  $related
     * @param  array|string  $through
     * @param  string|null  $localKey
     * @param  string  $prefix
     * @param  array  $foreignKeyLookup
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function belongsToThrough($related, $through, $localKey = null, $prefix = '', $foreignKeyLookup = [])
    {
        /** @var \Illuminate\Database\Eloquent\Model $relatedInstance */
        $relatedInstance = new $related;
        $models = [];
        $foreignKeys = [];

        foreach ((array) $through as $model) {
            $foreignKey = null;

            if (is_array($model)) {
                $foreignKey = $model[1];

                $model = $model[0];
            }

            /** @var \Illuminate\Database\Eloquent\Model $instance */
            $instance = new $model;

            if ($foreignKey) {
                $foreignKeys[$instance->getTable()] = $foreignKey;
            }

            $models[] = $instance;
        }

        $models[] = $this;

        foreach ($foreignKeyLookup as $model => $foreignKey) {
            $instance = new $model;

            if ($foreignKey) {
                $foreignKeys[$instance->getTable()] = $foreignKey;
            }
        }

        return $this->newBelongsToThrough($relatedInstance->newQuery(), $this, $models, $localKey, $prefix, $foreignKeys);
    }

    /**
     * Instantiate a new BelongsToThrough relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @param  string  $localKey
     * @param  string  $prefix
     * @param  array  $foreignKeyLookup
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    protected function newBelongsToThrough(Builder $query, Model $parent, array $models, $localKey, $prefix, array $foreignKeyLookup)
    {
        return new Relation($query, $parent, $models, $localKey, $prefix, $foreignKeyLookup);
    }
}

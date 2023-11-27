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
     * @param string $related
     * @param array|string $through
     * @param string|null $localKey
     * @param string $prefix
     * @param array $foreignKeyLookup
     * @param array  $localKeyLookup
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function belongsToThrough(
        $related,
        $through,
        $localKey = null,
        $prefix = '',
        $foreignKeyLookup = [],
        array $localKeyLookup = []
    ) {
        $relatedInstance = $this->newRelatedInstance($related);
        $throughParents  = [];
        $foreignKeys     = [];

        foreach ((array) $through as $model) {
            $foreignKey = null;

            if (is_array($model)) {
                $foreignKey = $model[1];

                $model = $model[0];
            }

            $instance = $this->belongsToThroughParentInstance($model);

            if ($foreignKey) {
                $foreignKeys[$instance->getTable()] = $foreignKey;
            }

            $throughParents[] = $instance;
        }

        $foreignKeys = array_merge($foreignKeys, $this->mapKeys($foreignKeyLookup));

        $localKeys = $this->mapKeys($localKeyLookup);

        return $this->newBelongsToThrough(
            $relatedInstance->newQuery(),
            $this,
            $throughParents,
            $localKey,
            $prefix,
            $foreignKeys,
            $localKeys
        );
    }

    /**
     * Map keys to an associative array where the key is the table name and the value is the key from the lookup.
     *
     * @param array $keyLookup
     * @return array
     */
    protected function mapKeys(array $keyLookup): array
    {
        $keys = [];

        // Iterate over each model and key in the key lookup
        foreach ($keyLookup as $model => $key) {
            // Create a new instance of the model
            $instance = new $model();

            // Add the table name and key to the keys array
            $keys[$instance->getTable()] = $key;
        }

        return $keys;
    }

    /**
     * Create a through parent instance for a belongs-to-through relationship.
     *
     * @param string $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function belongsToThroughParentInstance($model)
    {
        $segments = preg_split('/\s+as\s+/i', $model);

        /** @var \Illuminate\Database\Eloquent\Model $instance */
        $instance = new $segments[0]();

        if (isset($segments[1])) {
            $instance->setTable($instance->getTable() . ' as ' . $segments[1]);
        }

        return $instance;
    }

    /**
     * Instantiate a new BelongsToThrough relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $parent
     * @param \Illuminate\Database\Eloquent\Model[] $throughParents
     * @param string $localKey
     * @param string $prefix
     * @param array $foreignKeyLookup
     * @param array $localKeyLookup
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    protected function newBelongsToThrough(
        Builder $query,
        Model $parent,
        array $throughParents,
        $localKey,
        $prefix,
        array $foreignKeyLookup,
        array $localKeyLookup
    ) {
        return new Relation($query, $parent, $throughParents, $localKey, $prefix, $foreignKeyLookup, $localKeyLookup);
    }
}

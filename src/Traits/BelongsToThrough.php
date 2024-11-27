<?php

namespace Znck\Eloquent\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Relations\BelongsToThrough as Relation;

/**
 * @phpstan-ignore trait.unused
 */
trait BelongsToThrough
{
    /**
     * Define a belongs-to-through relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param class-string<TRelatedModel> $related
     * @param non-empty-list<class-string<\Illuminate\Database\Eloquent\Model>>|non-empty-list<array{0: class-string<\Illuminate\Database\Eloquent\Model>, 1: string}>|class-string<\Illuminate\Database\Eloquent\Model> $through
     * @param string|null $localKey
     * @param string $prefix
     * @param array<int, class-string<\Illuminate\Database\Eloquent\Model>, string> $foreignKeyLookup
     * @param array<int, class-string<\Illuminate\Database\Eloquent\Model>, string> $localKeyLookup
     * @return \Znck\Eloquent\Relations\BelongsToThrough<TRelatedModel, $this>
     */
    public function belongsToThrough(
        $related,
        $through,
        $localKey = null,
        $prefix = '',
        $foreignKeyLookup = [],
        array $localKeyLookup = []
    ) {
        /** @var TRelatedModel $relatedInstance */
        $relatedInstance = $this->newRelatedInstance($related);

        /** @var list<\Illuminate\Database\Eloquent\Model> $throughParents */
        $throughParents  = [];
        $foreignKeys     = [];

        foreach ((array) $through as $model) {
            $foreignKey = null;

            if (is_array($model)) {
                /** @var string $foreignKey */
                $foreignKey = $model[1];

                /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
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
     * @param array<int, class-string<\Illuminate\Database\Eloquent\Model>, string> $keyLookup
     * @return array<string, string>
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
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param class-string<TModel> $model
     * @return TModel
     */
    protected function belongsToThroughParentInstance($model)
    {
        /** @var array{0: class-string<TModel>, 1?: string} $segments */
        $segments = preg_split('/\s+as\s+/i', $model);

        /** @var TModel $instance */
        $instance = new $segments[0]();

        if (isset($segments[1])) {
            $instance->setTable($instance->getTable() . ' as ' . $segments[1]);
        }

        return $instance;
    }

    /**
     * Instantiate a new BelongsToThrough relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Illuminate\Database\Eloquent\Builder<TRelatedModel> $query
     * @param TDeclaringModel $parent
     * @param non-empty-list<\Illuminate\Database\Eloquent\Model> $throughParents
     * @param string|null $localKey
     * @param string $prefix
     * @param array<string, string> $foreignKeyLookup
     * @param array<string, string> $localKeyLookup
     * @return \Znck\Eloquent\Relations\BelongsToThrough<TRelatedModel, TDeclaringModel>
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

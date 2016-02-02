<?php

namespace Znck\Eloquent\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Znck\Eloquent\Relations\BelongsToThrough as Relation;

/**
 * Class BelongsToThrough.
 *
 * @author Rahul Kadyan <hi@znck.me>
 */
trait BelongsToThrough
{
    /**
     * Define a belongs-to-through relationship.
     *
     * @param string       $related
     * @param string|array $through
     * @param string|null  $localKey Primary Key (Default: id)
     * @param string       $prefix   Foreign key prefix
     *
     * @throws \Exception
     *
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function belongsToThrough($related, $through, $localKey = null, $prefix = '')
    {
        if (!$this instanceof Model) {
            throw new Exception('belongsToThrough can used on '.Model::class.' only.');
        }

        /** @var \Illuminate\Database\Eloquent\Model $relatedModel */
        $relatedModel = new $related();
        $models = [];
        $foreignKeys = [];
        foreach ((array) $through as $key => $model) {
            $foreignKey = null;

            if (is_array($model)) {
                $foreignKey = $model[1];
                $model = $model[0];
            }

            $object = new $model();

            if (!$object instanceof Model) {
                throw new InvalidArgumentException('Through model should be instance of '.Model::class.'.');
            }

            if ($foreignKey) {
                $foreignKeys[$object->getTable()] = $foreignKey;
            }

            $models[] = $object;
        }

        if (empty($through)) {
            throw new InvalidArgumentException('Provide one or more through model.');
        }

        $models[] = $this;

        return new Relation($relatedModel->newQuery(), $this, $models, $localKey, $prefix, $foreignKeys);
    }
}

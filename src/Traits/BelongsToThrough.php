<?php namespace Znck\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Relations\BelongsToThrough as Relation;

trait BelongsToThrough
{

    /**
     * Define a belongs-to-through relationship.
     *
     * @param string  $related
     * @param string|array  $through
     * @param string|null  $localKey
     *
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function belongsToThrough($related, $through, $localKey = null)
    {
        $related = new $related;
        $models = [];
        foreach ((array)$through as $key => $model) {
            $object = new $model;
            if (!($object instanceof Model)) {
                throw new \InvalidArgumentException(
                    "Through model should be instance of \\Iluminate\\Database\\Eloquent\\Model."
                );
            }
            $models[] = $object;
        }

        if (empty($through)) {
            throw new \InvalidArgumentException("Provide one or more through model.");
        }

        $models[] = $this;

        return new Relation($related->newQuery(), $this, $models, $localKey);
    }
}

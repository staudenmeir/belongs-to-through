<?php

namespace Znck\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Relations\BelongsToThrough as Relation;

trait BelongsToThrough
{
    public function belongsToThrough($related, $through, $localKey = null)
    {
        $related = new $related();
        if (!($related instanceof Model)) {
            throw new \InvalidArgumentException('$related class should be instance of \\Iluminate\\Database\\Eloquent\\Model.');
        }
        $models = [];
        foreach ((array) $through as $key => $model) {
            $object = new $model();
            if (!($object instanceof Model)) {
                throw new \InvalidArgumentException('$through classes should be instance of \\Iluminate\\Database\\Eloquent\\Model.');
            }
            $models[] = $object;
        }

        if (empty($through)) {
            throw new \InvalidArgumentException('$through should contain one or more classes.');
        }

        $models[] = $this;

        return new Relation($related->newQuery(), $this, $models, $localKey);
    }
}

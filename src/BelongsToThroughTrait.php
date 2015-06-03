<?php namespace Znck\Eloquent\Relations;

use Breve\Eloquent\Relations\BelongsToThrough;

/**
 * This file belongs to server.
 *
 * Author: Rahul Kadyan, <hi@znck.me>
 * Find license in root directory of this project.
 */
trait BelongsToThroughTrait {
    public function belongsToThrough($related, $through, $firstKey = null, $localKey = null)
    {
        $related = new $related;
        $through = new $through;

        $firstKey = $firstKey ?: $related->getForeignKey();

        $localKey = $localKey ?: $through->getForeignKey();

        return new BelongsToThrough($related->newQuery(), $this, $through, $firstKey, $localKey);
    }
}
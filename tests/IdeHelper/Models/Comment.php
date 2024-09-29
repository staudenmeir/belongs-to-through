<?php

namespace Tests\IdeHelper\Models;

use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class Comment extends Model
{
    use BelongsToThroughTrait;

    public function country(): BelongsToThroughRelation
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class]);
    }
}

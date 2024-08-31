<?php

namespace Tests\IdeHelper\Models;

use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;

class Comment extends Model
{
    use BelongsToThrough;

    /**
     * @return \Znck\Eloquent\Relations\BelongsToThrough<Country, User|Post, $this>
     */
    public function country()
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class]);
    }
}

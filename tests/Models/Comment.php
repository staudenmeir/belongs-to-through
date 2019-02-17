<?php

namespace Tests\Models;

class Comment extends Model
{
    public function country()
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class]);
    }

    public function countryWithCustomForeignKeys()
    {
        return $this->belongsToThrough(
            Country::class,
            [[User::class, 'custom_user_id'], Post::class],
            null,
            '',
            [Post::class => 'custom_post_id']
        );
    }

    public function countryWithPrefix()
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class], null, 'custom_');
    }
}

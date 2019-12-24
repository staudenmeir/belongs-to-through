<?php

namespace Tests\Models;

use Znck\Eloquent\Traits\HasTableAlias;

class Comment extends Model
{
    use HasTableAlias;

    public function country()
    {
        return $this->belongsToThrough(Country::class, [User::class, Post::class])->withDefault();
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

    public function grandparent()
    {
        return $this->belongsToThrough(self::class, self::class.' as alias', null, '', [self::class => 'parent_id']);
    }

    public function user()
    {
        return $this->belongsToThrough(User::class, Post::class);
    }
}

<?php

namespace Staudenmeir\BelongsToThrough\Types;

use Staudenmeir\BelongsToThrough\Types\Models\Comment;
use Staudenmeir\BelongsToThrough\Types\Models\Country;
use Staudenmeir\BelongsToThrough\Types\Models\Post;
use Staudenmeir\BelongsToThrough\Types\Models\User;

use function PHPStan\Testing\assertType;

function test(Comment $comment): void
{
    assertType(
        'Znck\Eloquent\Relations\BelongsToThrough<Staudenmeir\BelongsToThrough\Types\Models\Country, Staudenmeir\BelongsToThrough\Types\Models\Comment>',
        $comment->belongsToThrough(Country::class, [User::class, Post::class])
    );
}

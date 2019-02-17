<?php

namespace Tests\Models;

class Post extends Model
{
    public function country()
    {
        return $this->belongsToThrough(Country::class, User::class);
    }
}

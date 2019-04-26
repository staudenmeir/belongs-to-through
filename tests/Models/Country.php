<?php

namespace Tests\Models;

class Country extends Model
{
    protected $withCount = ['users'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

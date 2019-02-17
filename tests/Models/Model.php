<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model as Base;
use Znck\Eloquent\Traits\BelongsToThrough;

abstract class Model extends Base
{
    use BelongsToThrough;

    public $incrementing = false;

    public $timestamps = false;
}

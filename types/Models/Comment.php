<?php

namespace Staudenmeir\BelongsToThrough\Types\Models;

use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;

class Comment extends Model
{
    use BelongsToThrough;
}

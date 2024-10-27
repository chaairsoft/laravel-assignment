<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    //public $incrementing = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

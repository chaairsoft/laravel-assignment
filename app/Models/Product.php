<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use  softDeletes;
    protected $guarded = [];
    public $incrementing = false;

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }
}

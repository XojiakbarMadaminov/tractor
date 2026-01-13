<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $table = 'locations';

    protected $guarded = [];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'is_from');
    }

    #[Scope]
    public function active($query)
    {
        return $query->where('is_active', true);
    }
}

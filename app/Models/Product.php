<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCurrentStoreScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasCurrentStoreScope;
    protected $table   = 'products';
    protected $guarded = [];

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class, ProductStock::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'is_from');
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->productStocks->sum('quantity');
    }
}

<?php

namespace App\Models;

use App\Models\Traits\HasCurrentStoreScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Store;
use App\Models\User;

class Sale extends Model
{
    use HasCurrentStoreScope;
    protected $table = 'sales';
    protected $guarded = [];
    protected $casts = [
        'total'       => 'float',
        'paid_amount' => 'float',
    ];

    /**
     * @return HasMany<SaleItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}

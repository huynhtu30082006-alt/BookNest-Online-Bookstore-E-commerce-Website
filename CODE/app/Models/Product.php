<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'name', 'author', 'description', 'guest_price', 'member_price', 'stock', 'image_url', 'status'])]
class Product extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    protected function casts(): array
    {
        return [
            'guest_price' => 'integer',
            'member_price' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isPurchasable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->stock > 0;
    }

    /**
     * Unit price for the given customer: member price for a logged-in user,
     * guest price otherwise. Single source of truth for cart, checkout and orders.
     */
    public function priceFor(?User $user): int
    {
        return $user ? $this->member_price : $this->guest_price;
    }
}

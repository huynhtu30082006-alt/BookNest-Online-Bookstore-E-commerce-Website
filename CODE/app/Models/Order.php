<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'coupon_id',
    'guest_name', 'guest_email', 'guest_phone', 'guest_address',
    'subtotal', 'discount', 'delivery_fee', 
    'status', 'payment_method', 'payment_status',
])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'Pending';
    public const STATUS_CONFIRMED = 'Confirmed';
    public const STATUS_SHIPPING = 'Shipping';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_SHIPPING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_UNPAID = 'Unpaid';
    public const PAYMENT_PAID = 'Paid';
    public const PAYMENT_REFUNDED = 'Refunded';

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'delivery_fee' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Derived attribute (ERD): subtotal - discount + delivery_fee. */
    public function getTotalPriceAttribute(): int
    {
        return max(0, $this->subtotal - $this->discount) + $this->delivery_fee;
    }

    /** Human-friendly order reference shown to customers, e.g. BN-000012. */
    public function getReferenceAttribute(): string
    {
        return 'BN-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return config('booknest.payment_methods.'.$this->payment_method, $this->payment_method);
    }

    /** Customers (members and guests) may cancel an order only while it is still Pending. */
    public function isCancellable(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isGuestOrder(): bool
    {
        return $this->user_id === null;
    }
}

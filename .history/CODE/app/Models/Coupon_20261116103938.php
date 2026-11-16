<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'discount_value', 'min_order_value', 'is_active'])]
class Coupon extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'discount_value' => 'integer',
            'min_order_value' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * One use per customer: a member is identified by account (or by the email on a guest order made
     * with their account email), a guest by the delivery email. Cancelled orders do not count.
     */
    public function hasBeenUsedBy(?User $user, ?string $email = null): bool
    {
        $email = $email ? mb_strtolower(trim($email)) : null;
        if (! $user && ! $email) {
            return false; // guest whose email is not known yet; checked again at checkout
        }

        return $this->orders()
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->where(function ($q) use ($user, $email) {
                if ($user) {
                    $q->orWhere('user_id', $user->id)
                      ->orWhereRaw('LOWER(guest_email) = ?', [mb_strtolower($user->email)]);
                }
                if ($email) {
                    $q->orWhereRaw('LOWER(guest_email) = ?', [$email]);
                }
            })
            ->exists();
    }
}

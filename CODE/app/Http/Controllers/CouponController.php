<?php

namespace App\Http\Controllers;

use App\Exceptions\CouponException;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private PricingService $pricing)
    {
    }

    public function apply(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50']], [
            'code.required' => 'Please enter a coupon code.',
        ]);

        try {
            $this->pricing->applyCoupon($data['code']);
        } catch (CouponException $e) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }

        $message = 'Coupon applied.';

        return $request->expectsJson()
            ? response()->json(['message' => $message] + $this->pricing->summary()->toArray())
            : back()->with('success', $message);
    }

    public function remove(Request $request): JsonResponse|RedirectResponse
    {
        $this->pricing->removeCoupon();

        return $request->expectsJson()
            ? response()->json(['message' => 'Coupon removed.'] + $this->pricing->summary()->toArray())
            : back()->with('success', 'Coupon removed.');
    }
}

{{-- Coupon box (members only). Works without JavaScript; with JavaScript it is applied via AJAX. --}}
<div class="mb-3" data-coupon-box>
    <label for="coupon-code" class="form-label fw-semibold">Discount code</label>

    @auth
        <form method="POST" action="{{ route('coupon.apply') }}" data-coupon-form
              class="input-group {{ $summary->coupon ? 'd-none' : '' }}">
            @csrf
            <input type="text" name="code" id="coupon-code" class="form-control" placeholder="Enter code" maxlength="50" autocomplete="off">
            <button class="btn btn-outline-bn" type="submit">Apply</button>
        </form>

        <div class="alert alert-success py-2 mb-0 d-flex justify-content-between align-items-center {{ $summary->coupon ? '' : 'd-none' }}" data-coupon-applied>
            <span>Code <strong data-summary="coupon_code">{{ $summary->coupon?->code }}</strong> applied</span>
            <form method="POST" action="{{ route('coupon.remove') }}" data-coupon-remove>
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger p-0" type="submit">Remove</button>
            </form>
        </div>

        <div class="small mt-1 text-danger d-none" data-coupon-error role="alert"></div>
        @if ($summary->couponNotice)
            <div class="small mt-1 text-warning-emphasis">{{ $summary->couponNotice }}</div>
        @endif
    @else
        <div class="alert alert-light border py-2 mb-0 small">
            Discount codes are for members.
            <a href="{{ route('login') }}">Log in</a> or <a href="{{ route('register') }}">register</a> to use one.
        </div>
    @endauth
</div>
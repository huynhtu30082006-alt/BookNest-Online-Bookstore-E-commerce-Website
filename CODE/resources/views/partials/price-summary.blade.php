<dl class="row mb-0 small price-breakdown">
    <dt class="col-7 fw-normal">Subtotal <span class="text-muted">({{ auth()->check() ? 'member' : 'guest' }} prices)</span></dt>
    <dd class="col-5 text-end" data-summary="subtotal">{{ \App\Support\Money::vnd($summary->subtotal) }}</dd>

    <dt class="col-7 fw-normal text-bn-accent {{ $summary->discount > 0 ? '' : 'd-none' }}" data-summary-row="discount">
        Discount <span data-summary="coupon_code_label">{{ $summary->coupon ? '('.$summary->coupon->code.')' : '' }}</span>
    </dt>
    <dd class="col-5 text-end text-bn-accent {{ $summary->discount > 0 ? '' : 'd-none' }}" data-summary-row="discount">
        − <span data-summary="discount">{{ \App\Support\Money::vnd($summary->discount) }}</span>
    </dd>

    <dt class="col-7 fw-normal">Delivery fee</dt>
    <dd class="col-5 text-end" data-summary="delivery_fee">{{ \App\Support\Money::vnd($summary->deliveryFee) }}</dd>
</dl>
<hr>
<div class="d-flex justify-content-between fs-5 fw-bold total-line">
    <span>Total</span>
    <span class="text-bn" data-summary="total">{{ \App\Support\Money::vnd($summary->total) }}</span>
</div>

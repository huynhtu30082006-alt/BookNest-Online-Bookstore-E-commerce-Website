@if ($product->isPurchasable())
    <form method="POST" action="{{ route('cart.add', $product) }}" data-add-to-cart class="d-flex gap-2">
        @csrf
        <input type="hidden" name="quantity" value="1">
        <button type="submit" class="btn btn-sm btn-outline-danger flex-grow-1" aria-label="Thêm {{ $product->name }} vào giỏ hàng">
            <span aria-hidden="true">＋</span> Thêm vào giỏ
        </button>
    </form>
@else
    <button class="btn btn-sm btn-outline-secondary w-100" disabled>Hết hàng</button>
@endif

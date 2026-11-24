@if ($product->isPurchasable())
    <form method="POST" action="{{ route('cart.add', $product) }}" data-add-to-cart class="d-flex gap-2">
        @csrf
        <input type="number" name="quantity" value="1" min="1" max="{{ min($product->stock, config('booknest.max_quantity_per_item')) }}"
               class="form-control form-control-sm qty-input" aria-label="Quantity">
        <button type="submit" class="btn btn-sm btn-bn flex-grow-1" aria-label="Add {{ $product->name }} to cart">
            <span aria-hidden="true">＋</span> Add to cart
        </button>
    </form>
@else
    <button class="btn btn-sm btn-outline-secondary w-100" disabled>Out of stock</button>
@endif

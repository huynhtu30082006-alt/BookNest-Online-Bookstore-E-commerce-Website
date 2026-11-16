{{-- Reusable "add to cart" button/form for any product card or detail page: @include('partials.add-to-cart', ['product' => $product]) --}}
@if ($product->isPurchasable())
    <form method="POST" action="{{ route('cart.add', $product) }}" data-add-to-cart class="d-flex gap-2">
        @csrf
        <input type="number" name="quantity" value="1" min="1" max="{{ min($product->stock, config('booknest.max_quantity_per_item')) }}"
               class="form-control form-control-sm qty-input" aria-label="Quantity">
        <button type="submit" class="btn btn-sm btn-bn flex-grow-1">Add to cart</button>
    </form>
@else
    <button class="btn btn-sm btn-secondary w-100" disabled>Out of stock</button>
@endif

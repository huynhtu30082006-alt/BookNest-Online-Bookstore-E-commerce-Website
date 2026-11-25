@extends('layouts.app')

@section('title', $product->name)

@section('content')
<nav aria-label="Điều hướng" class="small text-muted mb-3">
    <a href="{{ route('home') }}">Trang chủ</a>
    <span class="mx-2">/</span>
    <span>{{ $product->category?->name }}</span>
</nav>

<article class="product-detail">
    <div class="row g-4 g-lg-5">
        <div class="col-lg-5">
            <div class="product-detail-cover">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="Bìa sách {{ $product->name }}">
                @else
                    <div class="book-cover-placeholder">
                        <span class="cover-title">{{ $product->name }}</span>
                        <span class="cover-rule"></span>
                        <span class="cover-author">{{ $product->author }}</span>
                    </div>
                @endif
            </div>
            @if ($product->isPurchasable())
                <form method="POST" action="{{ route('cart.add', $product) }}" data-add-to-cart
                      data-checkout-url="{{ route('checkout.show') }}" class="product-actions">
                    @csrf
                    <input type="hidden" name="quantity" value="1" data-quantity-input
                           min="1" max="{{ min($product->stock, config('booknest.max_quantity_per_item')) }}">
                    <button type="submit" class="btn btn-outline-danger" aria-label="Thêm {{ $product->name }} vào giỏ hàng">
                        Thêm vào giỏ hàng
                    </button>
                    <button type="submit" name="buy_now" value="1" class="btn btn-bn" data-buy-now>Mua ngay</button>
                </form>
            @else
                <div class="product-actions">
                    <button type="button" class="btn btn-outline-secondary" disabled>Hết hàng</button>
                    <button type="button" class="btn btn-secondary" disabled>Mua ngay</button>
                </div>
            @endif
        </div>

        <div class="col-lg-7">
            <div class="product-detail-info">
                <div class="book-category">{{ $product->category?->name }}</div>
                <h1>{{ $product->name }}</h1>
                <p class="product-author">Tác giả: <strong>{{ $product->author }}</strong></p>
                <div class="stock-status {{ $product->stock > 0 ? 'is-available' : 'is-sold-out' }}">
                    <span class="stock-status-dot" aria-hidden="true"></span>
                    {{ $product->stock > 0 ? 'Còn hàng' : 'Hết hàng' }}
                </div>

                <div class="product-price-box">
                    <div class="member-price-line">
                        <span class="price-caption">Giá thành viên</span>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong>{{ \App\Support\Money::vnd($product->member_price) }}</strong>
                            @if ($product->guest_price > 0 && $product->member_price < $product->guest_price)
                                <span class="discount-badge">-{{ (int) floor((1 - $product->member_price / $product->guest_price) * 100) }}%</span>
                            @endif
                        </div>
                    </div>
                    <div class="guest-price-line mt-2">
                        <span>Giá khách</span>
                        <strong>{{ \App\Support\Money::vnd($product->guest_price) }}</strong>
                    </div>
                </div>

                @if ($product->isPurchasable())
                    <div class="quantity-picker">
                        <span class="form-label mb-0">Số lượng</span>
                        <div class="quantity-stepper" aria-label="Chọn số lượng">
                            <button type="button" aria-label="Giảm số lượng" data-quantity-step="-1">−</button>
                            <span data-quantity-display>1</span>
                            <button type="button" aria-label="Tăng số lượng" data-quantity-step="1">＋</button>
                        </div>
                    </div>
                @endif

                <section class="product-description" aria-labelledby="product-description-heading">
                    <h2 id="product-description-heading">Thông tin chi tiết</h2>
                    <dl class="row mb-3">
                        <dt class="col-sm-4">Tên sách</dt>
                        <dd class="col-sm-8">{{ $product->name }}</dd>
                        <dt class="col-sm-4">Tác giả</dt>
                        <dd class="col-sm-8">{{ $product->author }}</dd>
                        <dt class="col-sm-4">Danh mục</dt>
                        <dd class="col-sm-8">{{ $product->category?->name }}</dd>
                    </dl>
                    @if ($product->description)
                        <p class="product-description-text">{{ $product->description }}</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</article>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-quantity-step]');
        if (!button) { return; }

        var form = button.closest('.product-detail').querySelector('[data-add-to-cart]');
        var input = form.querySelector('[data-quantity-input]');
        var display = button.closest('.quantity-stepper').querySelector('[data-quantity-display]');
        var minimum = Number(input.min) || 1;
        var maximum = Number(input.max) || 99;
        input.value = Math.max(minimum, Math.min(maximum, Number(input.value) + Number(button.dataset.quantityStep)));
        display.textContent = input.value;
    });
</script>
@endpush

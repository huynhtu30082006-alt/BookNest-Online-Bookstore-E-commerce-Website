@extends('layouts.app')

@section('title', 'BookNest Bookstore')

@section('content')
<section class="shop-hero mb-4">
    <div class="shop-hero-content">
        <span class="eyebrow"><span aria-hidden="true">✦</span> Member-exclusive offers</span>
        <h1>Discover knowledge,<br class="d-none d-sm-block"> open new possibilities.</h1>
        <p>Discover great books, enjoy member pricing, and order with ease at BookNest.</p>
        <div class="hero-perks">
            <span><span aria-hidden="true">✓</span> Member-exclusive prices</span>
            <span><span aria-hidden="true">✓</span> Fast, secure ordering</span>
        </div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="hero-sun"></div>
        <div class="hero-book hero-book-back"></div>
        <div class="hero-book hero-book-front"><span>Great books<br>every day</span></div>
        <div class="hero-star hero-star-one">✦</div>
        <div class="hero-star hero-star-two">✧</div>
    </div>
</section>

<section class="book-search-panel mb-4" aria-label="Search and filter books">
    <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <div class="col-12 col-sm-6 col-lg-5">
            <label for="book-category" class="form-label">Book category</label>
            <select id="book-category" name="category" class="form-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-5">
            <label for="book-sort" class="form-label">Sort by</label>
            <select id="book-sort" name="sort" class="form-select">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                <option value="name_asc" @selected(request('sort') === 'name_asc')>Title: A–Z</option>
            </select>
        </div>
        <div class="col-12 col-lg-2">
            <button type="submit" class="btn btn-bn search-button w-100">Filter books</button>
        </div>
    </form>
</section>

<section aria-labelledby="book-shelf-heading">
    <div class="shelf-heading d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-3 mb-lg-4">
        <div>
            <span class="eyebrow text-bn">Discover BookNest</span>
            <h2 id="book-shelf-heading" class="h3 mb-0">Featured books</h2>
        </div>
        <p class="small text-muted mb-0">{{ $products->total() }} books</p>
    </div>

    @forelse ($products as $product)
        @if ($loop->first)
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-lg-4">
        @endif
        <div class="col">
            <article class="card book-card h-100">
                <a class="book-cover-frame" href="{{ route('products.show', $product) }}" aria-label="Xem {{ $product->name }}">
                    @if ($product->image_url)
                        <img class="book-cover-image" src="{{ $product->image_url }}" alt="Cover of {{ $product->name }}">
                    @else
                        <div class="book-cover-placeholder" aria-label="Book cover">
                            <span class="cover-title">{{ $product->name }}</span>
                            <span class="cover-rule"></span>
                            <span class="cover-author">{{ $product->author }}</span>
                        </div>
                    @endif
                </a>
                <div class="card-body d-flex flex-column">
                    <div class="book-category">{{ $product->category?->name }}</div>
                    <h3 class="book-title"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                    <p class="book-author mb-2">{{ $product->author }}</p>

                    <div class="book-prices mt-auto">
                        <div class="member-price-line">
                            <span class="price-caption">Member price</span>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong>{{ \App\Support\Money::vnd($product->member_price) }}</strong>
                                @if ($product->guest_price > 0 && $product->member_price < $product->guest_price)
                                    <span class="discount-badge">-{{ (int) floor((($product->guest_price - $product->member_price) * 100) / $product->guest_price) }}%</span>
                                @endif
                            </div>
                        </div>
                        <div class="guest-price-line">
                            <span>Guest price</span>
                            <strong>{{ \App\Support\Money::vnd($product->guest_price) }}</strong>
                        </div>
                    </div>
                    <div class="stock-status {{ $product->stock > 0 ? 'is-available' : 'is-sold-out' }}">
                        <span class="stock-status-dot" aria-hidden="true"></span>
                        {{ $product->stock > 0 ? 'In stock' : 'Out of stock' }}
                    </div>
                    <div class="mt-2">
                        @include('partials.add-to-cart', ['product' => $product])
                    </div>
                </div>
            </article>
        </div>
        @if ($loop->last)
            </div>
        @endif
    @empty
        <div class="empty-books text-center py-5 px-3">
            <div class="empty-books-icon" aria-hidden="true">📖</div>
            <h3 class="h5 mt-3">No matching books found</h3>
            <p class="text-muted mb-3">Try searching for a different title or category.</p>
            <a href="{{ route('home') }}" class="btn btn-outline-bn">View all books</a>
        </div>
    @endforelse
</section>

@if ($products->total() > 0)
    <div class="book-pagination mt-4 mt-lg-5">
        <p class="book-pagination-count small text-muted">
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} books
        </p>
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection

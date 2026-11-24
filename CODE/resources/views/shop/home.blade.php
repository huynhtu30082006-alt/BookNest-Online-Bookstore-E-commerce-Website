@extends('layouts.app')

@section('title', 'Find your next favorite book')

@section('content')
<section class="shop-hero mb-4 mb-lg-5">
    <div class="shop-hero-content">
        <span class="eyebrow"><span aria-hidden="true">✦</span> A little escape, one page at a time</span>
        <h1>Find your next<br class="d-none d-sm-block"> favorite book.</h1>
        <p>Make room for a new story. Explore a thoughtful collection picked for curious minds.</p>
        <div class="hero-perks">
            <span><span aria-hidden="true">✓</span> Member-only prices</span>
            <span><span aria-hidden="true">✓</span> Easy, secure checkout</span>
        </div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="hero-sun"></div>
        <div class="hero-book hero-book-back"></div>
        <div class="hero-book hero-book-front"><span>stories<br>to keep</span></div>
        <div class="hero-star hero-star-one">✦</div>
        <div class="hero-star hero-star-two">✧</div>
    </div>
</section>

<section class="book-search-panel mb-4 mb-lg-5" aria-label="Search and filter books">
    <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
        <div class="col-12 col-lg-5">
            <label for="book-search" class="form-label">What would you like to read?</label>
            <div class="search-input-wrap">
                <span aria-hidden="true">⌕</span>
                <input id="book-search" type="search" name="search" value="{{ request('search') }}"
                       class="form-control search-input" placeholder="Search by title or author...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <label for="book-category" class="form-label">Browse a shelf</label>
            <select id="book-category" name="category" class="form-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <label for="book-sort" class="form-label">Sort by</label>
            <select id="book-sort" name="sort" class="form-select">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                <option value="name_asc" @selected(request('sort') === 'name_asc')>Name: A to Z</option>
            </select>
        </div>
        <div class="col-12 col-lg-2">
            <button type="submit" class="btn btn-bn search-button w-100">
                <span aria-hidden="true">⌕</span> Find books
            </button>
        </div>
    </form>
</section>

<section aria-labelledby="book-shelf-heading">
    <div class="shelf-heading d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-3 mb-lg-4">
        <div>
            <span class="eyebrow text-bn">The BookNest collection</span>
            <h2 id="book-shelf-heading" class="h3 mb-0">Books worth getting lost in</h2>
        </div>
        <p class="small text-muted mb-0">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('book', $products->total()) }} to explore</p>
    </div>

    @forelse ($products as $product)
        @if ($loop->first)
            <div class="row row-cols-2 row-cols-lg-3 row-cols-xl-4 g-3 g-lg-4">
        @endif
        <div class="col">
            <article class="card book-card h-100">
                <div class="book-cover-frame">
                    @if ($product->image_url)
                        <img class="book-cover-image" src="{{ $product->image_url }}" alt="Cover of {{ $product->name }}">
                    @else
                        <div class="book-cover-placeholder" aria-label="Book cover placeholder">
                            <span class="cover-kicker">BOOKNEST EDITION</span>
                            <span class="cover-title">{{ $product->name }}</span>
                            <span class="cover-rule"></span>
                            <span class="cover-author">{{ $product->author }}</span>
                        </div>
                    @endif
                    <span class="stock-pill {{ $product->stock > 0 ? 'is-available' : 'is-sold-out' }}">
                        {{ $product->stock > 0 ? 'In stock' : 'Sold out' }}
                    </span>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="book-category">{{ $product->category?->name }}</div>
                    <h3 class="book-title">{{ $product->name }}</h3>
                    <p class="book-author">{{ $product->author }}</p>

                    <div class="book-prices mt-auto">
                        <div class="book-price-line">
                            <span>Guest price</span>
                            <strong>{{ \App\Support\Money::vnd($product->guest_price) }}</strong>
                        </div>
                        <div class="book-price-line member-price-line">
                            <span>Member price</span>
                            <strong>{{ \App\Support\Money::vnd($product->member_price) }}</strong>
                        </div>
                    </div>
                    <div class="mt-3">
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
            <h3 class="h5 mt-3">No books found on this shelf</h3>
            <p class="text-muted mb-3">Try another title or category to find your next read.</p>
            <a href="{{ route('home') }}" class="btn btn-outline-bn">See all books</a>
        </div>
    @endforelse
</section>

<div class="mt-4 mt-lg-5">
    {{ $products->links('pagination::bootstrap-5') }}
</div>
@endsection

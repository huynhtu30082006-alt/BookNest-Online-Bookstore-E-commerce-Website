{{--
    TEMPORARY placeholder (not part of the customer-flow scope).
    Only here so "Add to cart" can be tried before the product-browsing pages are merged.
    The UI owner replaces this view (and HomeController) with the real home page / product listing / detail page.
    Keep using: @include('partials.add-to-cart', ['product' => $product])
--}}
@extends('layouts.app')

@section('title', 'Books')

@section('content')
<div class="alert alert-warning small">Temporary product list for testing the cart – replaced by the product browsing pages.</div>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Book</th><th class="text-end">Guest price</th><th class="text-end">Member price</th><th>Stock</th><th style="width:220px"></th></tr></thead>
        <tbody>
        @forelse ($products as $product)
            <tr>
                <td>{{ $product->name }}<div class="small text-muted">{{ $product->author }}</div></td>
                <td class="text-end">{{ \App\Support\Money::vnd($product->guest_price) }}</td>
                <td class="text-end">{{ \App\Support\Money::vnd($product->member_price) }}</td>
                <td>{{ $product->stock }}</td>
                <td>@include('partials.add-to-cart', ['product' => $product])</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No books yet. Run <code>php artisan db:seed</code>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $products->links('pagination::bootstrap-5') }}
@endsection

@extends('layouts.app')
@section('title', 'Product catalog')
@section('content')
    <div class="page-heading"><div><h1>Product catalog</h1><p class="muted">Browse our available products.</p></div></div>
    <div class="product-grid">
        @forelse($products as $product)
            <article class="panel product-card">
                <span class="muted">{{ $product->category->name }}</span>
                <h2><a href="{{ route('catalog.show', $product) }}">{{ $product->name }}</a></h2>
                <p>{{ \Illuminate\Support\Str::limit($product->description, 120) }}</p>
                <div class="card-footer">
                    <strong>{{ number_format((float) $product->price, 2) }}</strong>
                    <span class="muted">{{ $product->stock > 0 ? $product->stock.' in stock' : 'Out of stock' }}</span>
                </div>
            </article>
        @empty
            <div class="panel empty">No products available yet.</div>
        @endforelse
    </div>
    {{ $products->links() }}
@endsection

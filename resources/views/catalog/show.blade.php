@extends('layouts.app')
@section('title', $product->name)
@section('content')
    <p><a href="{{ route('catalog.index') }}">Back to catalog</a></p>
    <article class="panel product-detail">
        <span class="muted">{{ $product->category->name }}</span>
        <h1>{{ $product->name }}</h1>
        <p class="price">{{ number_format((float) $product->price, 2) }}</p>
        <p>{{ $product->stock > 0 ? $product->stock.' in stock' : 'Out of stock' }}</p>
        <p class="description">{{ $product->description }}</p>
    </article>
@endsection

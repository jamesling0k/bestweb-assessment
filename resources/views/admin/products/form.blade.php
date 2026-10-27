@extends('layouts.app')
@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('content')
    <div class="page-heading">
        <h1>{{ $product->exists ? 'Edit product' : 'Add product' }}</h1>
        <a href="{{ route('admin.products.index') }}">Back to products</a>
    </div>
    <form class="panel form-stack product-form" method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif
        <div>
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="255" required>
        </div>
        <div>
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Choose a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @if($categories->isEmpty())
                <p class="muted">Run the database seeder to create categories.</p>
            @endif
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" maxlength="10000" required>{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="form-columns">
            <div>
                <label for="price">Price</label>
                <input id="price" name="price" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('price', $product->price) }}" required>
            </div>
            <div>
                <label for="stock">Stock</label>
                <input id="stock" name="stock" type="number" min="0" max="4294967295" step="1" value="{{ old('stock', $product->stock) }}" required>
            </div>
        </div>
        <div>
            <input type="hidden" name="enabled" value="0">
            <label class="checkbox-label" for="enabled">
                <input type="checkbox" id="enabled" name="enabled" value="1" @checked(old('enabled', $product->enabled))>
                Enabled (visible in the public catalog)
            </label>
        </div>
        <div class="row-actions">
            <button class="button" type="submit">Save product</button>
            <a href="{{ route('admin.products.index') }}">Cancel</a>
        </div>
    </form>
@endsection

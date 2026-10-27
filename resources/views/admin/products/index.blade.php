@extends('layouts.app')
@section('title', 'Manage products')
@section('content')
    <div class="page-heading">
        <div><h1>Products</h1><p class="muted">{{ $products->total() }} products matching your filters</p></div>
        <a class="button" href="{{ route('admin.products.create') }}">Add product</a>
    </div>
    <section class="panel">
        <form method="GET" action="{{ route('admin.products.index') }}" class="filter-bar">
            <div>
                <label for="enabled">Status</label>
                <select name="enabled" id="enabled">
                    <option value="">All statuses</option>
                    <option value="1" @selected(request('enabled') === '1')>Enabled</option>
                    <option value="0" @selected(request('enabled') === '0')>Disabled</option>
                </select>
            </div>
            <div>
                <label for="category_id">Category</label>
                <select name="category_id" id="category_id">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="button secondary" type="submit">Filter</button>
            <a href="{{ route('admin.products.index') }}">Reset</a>
            <a class="button secondary" href="{{ route('admin.products.export', request()->only('enabled', 'category_id')) }}">Export Excel</a>
        </form>
        <form id="bulk-delete" method="POST" action="{{ route('admin.products.bulk-destroy') }}" data-confirm="Delete the selected products?">
            @csrf
            @method('DELETE')
            <button class="button danger" type="submit">Delete selected</button>
            <span class="muted">Select up to 100 products on this page.</span>
        </form>
        <div class="table-scroll">
            <table>
                <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" aria-label="Select all products on this page"></th>
                    <th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $product->id }}" form="bulk-delete" aria-label="Select {{ $product->name }}"></td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td class="number">{{ number_format((float) $product->price, 2) }}</td>
                        <td class="number">{{ $product->stock }}</td>
                        <td><span class="badge {{ $product->enabled ? 'enabled' : '' }}">{{ $product->enabled ? 'Enabled' : 'Disabled' }}</span></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete this product?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="link-button text-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No products found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </section>
@endsection

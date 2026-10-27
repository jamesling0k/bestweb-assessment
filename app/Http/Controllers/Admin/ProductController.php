<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): View
    {
        $filters = $request->validated();

        return view('admin.products.index', [
            'products' => Product::with('category')->filter($filters)->orderByDesc('id')
                ->paginate($filters['per_page'] ?? 15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['enabled' => true, 'stock' => 0]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    public function bulkDestroy(BulkDeleteRequest $request): RedirectResponse
    {
        $count = Product::whereIn('id', $request->validated('ids'))->delete();

        return redirect()->route('admin.products.index')->with('status', $count.' products deleted.');
    }

    public function export(ProductIndexRequest $request): BinaryFileResponse
    {
        return Excel::download(new ProductsExport($request->validated()), 'products.xlsx');
    }
}

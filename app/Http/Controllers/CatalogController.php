<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        return view('catalog.index', [
            'products' => Product::with('category')->where('enabled', true)->orderByDesc('id')->paginate(12),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->enabled, 404);

        return view('catalog.show', ['product' => $product->load('category')]);
    }
}

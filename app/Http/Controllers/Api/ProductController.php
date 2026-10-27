<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        return ProductResource::collection(
            Product::with('category')->filter($filters)->orderByDesc('id')
                ->paginate($filters['per_page'] ?? 15)->withQueryString(),
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return (new ProductResource($product->load('category')))->response()->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    public function bulkDestroy(BulkDeleteRequest $request): JsonResponse
    {
        $count = Product::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Products deleted.', 'data' => ['deleted_count' => $count]]);
    }
}

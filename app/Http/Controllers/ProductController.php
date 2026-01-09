<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['categories', 'brand', 'images']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 15);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock_quantity' => 'integer|min:0',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*.image_url' => 'required|string',
            'images.*.is_primary' => 'boolean',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync categories
        if (!empty($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        // Create images
        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $index => $image) {
                $product->images()->create([
                    'image_url' => $image['image_url'],
                    'is_primary' => $image['is_primary'] ?? ($index === 0),
                    'sort_order' => $index,
                ]);
            }
        }

        // Sync attribute values
        if (!empty($validated['attribute_values'])) {
            $product->attributeValues()->sync($validated['attribute_values']);
        }

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product->load(['categories', 'brand', 'images', 'attributeValues.attribute']),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'product' => $product->load(['categories', 'brand', 'images', 'attributeValues.attribute']),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock_quantity' => 'integer|min:0',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*.image_url' => 'required|string',
            'images.*.is_primary' => 'boolean',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $product->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $product->slug,
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync categories
        if (isset($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        // Update images if provided
        if (isset($validated['images'])) {
            $product->images()->delete();
            foreach ($validated['images'] as $index => $image) {
                $product->images()->create([
                    'image_url' => $image['image_url'],
                    'is_primary' => $image['is_primary'] ?? ($index === 0),
                    'sort_order' => $index,
                ]);
            }
        }

        // Sync attribute values
        if (isset($validated['attribute_values'])) {
            $product->attributeValues()->sync($validated['attribute_values']);
        }

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh()->load(['categories', 'brand', 'images', 'attributeValues.attribute']),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }
}

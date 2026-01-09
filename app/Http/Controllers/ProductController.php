<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['categories', 'brand', 'images', 'variants'])
            ->withCount('variants');

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 25);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Basic Info
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'product_type' => 'nullable|in:stitched,unstitched',
            'fabric_type' => 'nullable|string|max:255',
            'care_instructions' => 'nullable|string',
            'season' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,published,pos_only,online_only',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_active' => 'boolean',
            
            // SEO
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:170',
            'meta_keywords' => 'nullable|string|max:255',
            
            // Pricing (base)
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            
            // Categories & Brand
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            
            // Variants
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.fabric_id' => 'nullable|exists:fabrics,id',
            'variants.*.fit_id' => 'nullable|exists:fits,id',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.retail_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.tax_class_id' => 'nullable|exists:tax_classes,id',
            'variants.*.stock_quantity' => 'integer|min:0',
            'variants.*.low_stock_threshold' => 'integer|min:0',
            'variants.*.is_online' => 'boolean',
            'variants.*.is_pos' => 'boolean',
            'variants.*.is_active' => 'boolean',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'nullable|string|url',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'product_type' => $validated['product_type'] ?? 'stitched',
            'fabric_type' => $validated['fabric_type'] ?? null,
            'care_instructions' => $validated['care_instructions'] ?? null,
            'season' => $validated['season'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'is_featured' => $validated['is_featured'] ?? false,
            'is_bestseller' => $validated['is_bestseller'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'price' => $validated['price'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
        ]);

        // Sync categories
        if (!empty($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        // Create variants with images
        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $index => $variantData) {
                $variant = $product->variants()->create([
                    'sku' => $variantData['sku'] ?: $this->generateSku($product, $index + 1),
                    'barcode' => $variantData['barcode'] ?? null,
                    'size_id' => $variantData['size_id'] ?? null,
                    'color_id' => $variantData['color_id'] ?? null,
                    'fabric_id' => $variantData['fabric_id'] ?? null,
                    'fit_id' => $variantData['fit_id'] ?? null,
                    'cost_price' => $variantData['cost_price'] ?? null,
                    'retail_price' => $variantData['retail_price'],
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'tax_class_id' => $variantData['tax_class_id'] ?? null,
                    'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                    'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 5,
                    'is_online' => $variantData['is_online'] ?? true,
                    'is_pos' => $variantData['is_pos'] ?? true,
                    'is_active' => $variantData['is_active'] ?? true,
                ]);
                
                // Create variant images
                if (!empty($variantData['images'])) {
                    foreach ($variantData['images'] as $imgIndex => $imageUrl) {
                        if ($imageUrl) {
                            $product->images()->create([
                                'variant_id' => $variant->id,
                                'image_url' => $imageUrl,
                                'is_primary' => $imgIndex === 0,
                                'sort_order' => $imgIndex,
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product->load(['categories', 'brand', 'variants']),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'product' => $product->load([
                'categories', 
                'brand', 
                'images', 
                'variants.size', 
                'variants.color', 
                'variants.fabric', 
                'variants.fit', 
                'variants.taxClass',
                'variants.images'
            ]),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            // Basic Info
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'product_type' => 'nullable|in:stitched,unstitched',
            'fabric_type' => 'nullable|string|max:255',
            'care_instructions' => 'nullable|string',
            'season' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,published,pos_only,online_only',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_active' => 'boolean',
            
            // SEO
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:170',
            'meta_keywords' => 'nullable|string|max:255',
            
            // Pricing
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            
            // Categories & Brand
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            
            // Variants
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.fabric_id' => 'nullable|exists:fabrics,id',
            'variants.*.fit_id' => 'nullable|exists:fits,id',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.retail_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.tax_class_id' => 'nullable|exists:tax_classes,id',
            'variants.*.stock_quantity' => 'integer|min:0',
            'variants.*.low_stock_threshold' => 'integer|min:0',
            'variants.*.is_online' => 'boolean',
            'variants.*.is_pos' => 'boolean',
            'variants.*.is_active' => 'boolean',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'nullable|string|url',
        ]);

        $product->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $product->slug,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'product_type' => $validated['product_type'] ?? $product->product_type,
            'fabric_type' => $validated['fabric_type'] ?? null,
            'care_instructions' => $validated['care_instructions'] ?? null,
            'season' => $validated['season'] ?? null,
            'status' => $validated['status'] ?? $product->status,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_bestseller' => $validated['is_bestseller'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'price' => $validated['price'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
        ]);

        // Sync categories
        if (isset($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        // Update variants
        if (isset($validated['variants'])) {
            $existingIds = [];
            
            foreach ($validated['variants'] as $index => $variantData) {
                if (!empty($variantData['id'])) {
                    // Update existing variant
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant && $variant->product_id === $product->id) {
                        $variant->update([
                            'sku' => $variantData['sku'] ?: $variant->sku,
                            'barcode' => $variantData['barcode'] ?? null,
                            'size_id' => $variantData['size_id'] ?? null,
                            'color_id' => $variantData['color_id'] ?? null,
                            'fabric_id' => $variantData['fabric_id'] ?? null,
                            'fit_id' => $variantData['fit_id'] ?? null,
                            'cost_price' => $variantData['cost_price'] ?? null,
                            'retail_price' => $variantData['retail_price'],
                            'sale_price' => $variantData['sale_price'] ?? null,
                            'tax_class_id' => $variantData['tax_class_id'] ?? null,
                            'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                            'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 5,
                            'is_online' => $variantData['is_online'] ?? true,
                            'is_pos' => $variantData['is_pos'] ?? true,
                            'is_active' => $variantData['is_active'] ?? true,
                        ]);
                        
                        // Update variant images
                        $this->syncVariantImages($product, $variant, $variantData['images'] ?? []);
                        $existingIds[] = $variant->id;
                    }
                } else {
                    // Create new variant
                    $newVariant = $product->variants()->create([
                        'sku' => $variantData['sku'] ?: $this->generateSku($product, $product->variants()->count() + 1),
                        'barcode' => $variantData['barcode'] ?? null,
                        'size_id' => $variantData['size_id'] ?? null,
                        'color_id' => $variantData['color_id'] ?? null,
                        'fabric_id' => $variantData['fabric_id'] ?? null,
                        'fit_id' => $variantData['fit_id'] ?? null,
                        'cost_price' => $variantData['cost_price'] ?? null,
                        'retail_price' => $variantData['retail_price'],
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'tax_class_id' => $variantData['tax_class_id'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 5,
                        'is_online' => $variantData['is_online'] ?? true,
                        'is_pos' => $variantData['is_pos'] ?? true,
                        'is_active' => $variantData['is_active'] ?? true,
                    ]);
                    
                    // Create variant images for new variant
                    $this->syncVariantImages($product, $newVariant, $variantData['images'] ?? []);
                    $existingIds[] = $newVariant->id;
                }
            }
            
            // Delete variants not in the update payload
            $product->variants()->whereNotIn('id', $existingIds)->delete();
        }

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh()->load(['categories', 'brand', 'variants']),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Generate SKU for variant
     */
    private function generateSku(Product $product, int $sequence): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product->name), 0, 3));
        $type = $product->product_type === 'stitched' ? 'STU' : 'UNS';
        return sprintf('%s-%s-%03d', $prefix, $type, $sequence);
    }

    /**
     * Sync variant images - delete old and create new
     */
    private function syncVariantImages(Product $product, ProductVariant $variant, array $imageUrls): void
    {
        // Delete existing images for this variant
        $product->images()->where('variant_id', $variant->id)->delete();
        
        // Create new images
        foreach ($imageUrls as $index => $imageUrl) {
            if ($imageUrl) {
                $product->images()->create([
                    'variant_id' => $variant->id,
                    'image_url' => $imageUrl,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}

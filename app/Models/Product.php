<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'product_type',
        'brand_id',
        'fabric_type',
        'care_instructions',
        'season',
        'price',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'is_active',
        'status',
        'is_featured',
        'is_bestseller',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'json_ld_schema',
        'canonical_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories')
            ->withTimestamps();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->withTimestamps();
    }

    /**
     * Scope for published products
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for online-available products
     */
    public function scopeOnline($query)
    {
        return $query->whereIn('status', ['published', 'online_only']);
    }

    /**
     * Scope for POS-available products
     */
    public function scopePos($query)
    {
        return $query->whereIn('status', ['published', 'pos_only']);
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the price range for a product with variants
     */
    public function getPriceRangeAttribute(): array
    {
        $prices = $this->variants()->pluck('retail_price');
        
        if ($prices->isEmpty()) {
            return ['min' => $this->price, 'max' => $this->price];
        }
        
        return ['min' => $prices->min(), 'max' => $prices->max()];
    }

    /**
     * Get total stock across all variants
     */
    public function getTotalStockAttribute(): int
    {
        return $this->variants()->sum('stock_quantity');
    }

    /**
     * Check if product is in stock
     */
    public function getInStockAttribute(): bool
    {
        return $this->total_stock > 0;
    }
}

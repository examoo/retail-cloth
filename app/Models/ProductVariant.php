<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'size_id',
        'color_id',
        'fabric_id',
        'fit_id',
        'cost_price',
        'retail_price',
        'sale_price',
        'tax_class_id',
        'weight',
        'is_online',
        'is_pos',
        'is_active',
        'stock_quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_online' => 'boolean',
        'is_pos' => 'boolean',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function fit(): BelongsTo
    {
        return $this->belongsTo(Fit::class);
    }

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function storeInventory(): HasMany
    {
        return $this->hasMany(StoreInventory::class, 'variant_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    /**
     * Get the effective price (sale_price if set, otherwise retail_price)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->retail_price;
    }

    /**
     * Check if variant is in stock
     */
    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if low stock
     */
    public function getLowStockAttribute(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Get variant display name
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        if ($this->size) $parts[] = $this->size->name;
        if ($this->color) $parts[] = $this->color->name;
        if ($this->fabric) $parts[] = $this->fabric->name;
        if ($this->fit) $parts[] = $this->fit->name;
        
        return implode(' / ', $parts) ?: 'Default';
    }
}

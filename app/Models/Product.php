<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'barcode', 'category_id', 'purchase_price',
        'selling_price', 'stock', 'minimum_stock', 'unit', 'image', 'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function stockLogs(): HasMany { return $this->hasMany(StockLog::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeLowStock($q) { return $q->whereColumn('stock', '<=', 'minimum_stock'); }

    public function isLowStock(): bool { return $this->stock <= $this->minimum_stock; }
}

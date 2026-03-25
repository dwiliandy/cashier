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
    public function stockBatches(): HasMany { return $this->hasMany(StockBatch::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeLowStock($q) { return $q->whereColumn('stock', '<=', 'minimum_stock'); }

    public function isLowStock(): bool { return $this->stock <= $this->minimum_stock; }

    /**
     * Rata-rata harga beli dari batch yang masih punya stok.
     * Fallback ke purchase_price jika tidak ada batch.
     */
    public function averagePurchasePrice(): float
    {
        $batches = $this->stockBatches()->hasStock()->get();
        if ($batches->isEmpty()) return (float) $this->purchase_price;

        $totalValue = $batches->sum(fn($b) => $b->remaining_quantity * $b->purchase_price);
        $totalQty = $batches->sum('remaining_quantity');

        return $totalQty > 0 ? round($totalValue / $totalQty, 2) : (float) $this->purchase_price;
    }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id', 'batch_number', 'quantity', 'remaining_quantity',
        'purchase_price', 'supplier', 'expiry_date', 'notes', 'user_id',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function generateBatchNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('batch_number', 'like', "BATCH-{$date}-%")->count();
        return sprintf('BATCH-%s-%04d', $date, $last + 1);
    }

    public function scopeHasStock($q) { return $q->where('remaining_quantity', '>', 0); }
}

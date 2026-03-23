<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'member_id', 'subtotal', 'discount', 'tax',
        'total', 'paid_amount', 'change_amount', 'payment_method', 'payment_status',
        'notes', 'local_id', 'synced_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2',
        'total' => 'decimal:2', 'paid_amount' => 'decimal:2', 'change_amount' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function items(): HasMany { return $this->hasMany(TransactionItem::class); }
    public function memberPoints(): HasMany { return $this->hasMany(MemberPoint::class); }

    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('invoice_number', 'like', "INV-{$date}-%")->count();
        return sprintf('INV-%s-%04d', $date, $last + 1);
    }
}

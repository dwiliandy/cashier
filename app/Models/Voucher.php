<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = ['member_id', 'code', 'value', 'points_cost', 'is_used', 'expires_at'];
    protected $casts = ['value' => 'decimal:2', 'points_cost' => 'decimal:2', 'is_used' => 'boolean', 'expires_at' => 'datetime'];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function isValid(): bool { return !$this->is_used && (!$this->expires_at || $this->expires_at->isFuture()); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPoint extends Model
{
    protected $fillable = ['member_id', 'transaction_id', 'points', 'type', 'notes'];
    protected $casts = ['points' => 'decimal:2'];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function transaction(): BelongsTo { return $this->belongsTo(Transaction::class); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = ['name', 'phone_number', 'email', 'points_balance', 'is_active'];
    protected $casts = ['points_balance' => 'decimal:2', 'is_active' => 'boolean'];

    public function points(): HasMany { return $this->hasMany(MemberPoint::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function vouchers(): HasMany { return $this->hasMany(Voucher::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}

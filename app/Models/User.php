<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isCashier(): bool { return $this->role === 'cashier'; }
    public function isOwner(): bool { return $this->role === 'owner'; }

    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function activityLogs(): HasMany { return $this->hasMany(ActivityLog::class); }
    public function stockLogs(): HasMany { return $this->hasMany(StockLog::class); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'description', 'properties', 'ip_address'];
    protected $casts = ['properties' => 'array'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function log(string $action, ?string $description = null, $model = null, ?array $properties = null): static
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}

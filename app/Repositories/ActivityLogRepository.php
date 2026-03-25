<?php
namespace App\Repositories;

use App\Models\ActivityLog;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function all(array $relations = []): Collection
    {
        return ActivityLog::with($relations)->latest()->get();
    }

    public function getFiltered(?string $from = null, ?string $to = null, ?string $action = null, ?int $userId = null): Collection
    {
        return ActivityLog::with('user')
            ->when($from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($action, fn($q, $v) => $q->where('action', $v))
            ->when($userId, fn($q, $v) => $q->where('user_id', $v))
            ->latest()
            ->get();
    }

    public function create(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }

    public function getDistinctActions(): array
    {
        return ActivityLog::distinct()->pluck('action')->toArray();
    }
}

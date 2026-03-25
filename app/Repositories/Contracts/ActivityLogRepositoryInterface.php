<?php
namespace App\Repositories\Contracts;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Collection;

interface ActivityLogRepositoryInterface
{
    public function all(array $relations = []): Collection;
    public function getFiltered(?string $from = null, ?string $to = null, ?string $action = null, ?int $userId = null): Collection;
    public function create(array $data): ActivityLog;
    public function getDistinctActions(): array;
}

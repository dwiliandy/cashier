<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ActivityLogService
{
    public function __construct(
        private ActivityLogRepositoryInterface $logRepo,
    ) {}

    public function getAll(): Collection
    {
        return $this->logRepo->all(['user']);
    }

    public function getFiltered(?string $from, ?string $to, ?string $action, ?int $userId): Collection
    {
        return $this->logRepo->getFiltered($from, $to, $action, $userId);
    }

    public function getDistinctActions(): array
    {
        return $this->logRepo->getDistinctActions();
    }

    public function getUsers(): Collection
    {
        return User::orderBy('name')->get();
    }
}

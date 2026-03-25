<?php

namespace App\Repositories\Contracts;

use App\Models\StockAdjustment;
use Illuminate\Support\Collection;

interface StockAdjustmentRepositoryInterface
{
    public function all(array $relations = []): Collection;
    public function create(array $data): StockAdjustment;
    public function getLatest(int $limit = 50): Collection;
    public function getFiltered(string $from = null, string $to = null, string $type = null, int $productId = null): Collection;
}

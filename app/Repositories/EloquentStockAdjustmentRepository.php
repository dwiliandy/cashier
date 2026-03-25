<?php

namespace App\Repositories;

use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentStockAdjustmentRepository implements StockAdjustmentRepositoryInterface
{
    public function all(array $relations = []): Collection
    {
        return StockAdjustment::with($relations)->latest()->get();
    }

    public function create(array $data): StockAdjustment
    {
        return StockAdjustment::create($data);
    }

    public function getLatest(int $limit = 50): Collection
    {
        return StockAdjustment::with(['product', 'user'])->latest()->limit($limit)->get();
    }

    public function getFiltered(string $from = null, string $to = null, string $type = null, int $productId = null): Collection
    {
        $query = StockAdjustment::with(['product', 'user']);

        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        if ($type) $query->where('type', $type);
        if ($productId) $query->where('product_id', $productId);

        return $query->latest()->get();
    }
}

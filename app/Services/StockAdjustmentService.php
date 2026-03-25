<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        private StockAdjustmentRepositoryInterface $adjustmentRepo,
        private StockService $stockService
    ) {}

    public function create(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            
            // Perform stock adjustment using existing StockService
            $this->stockService->adjustStock(
                $product, 
                $data['quantity'], 
                $data['type'], 
                'ADJ-' . time(), 
                $data['reason'] . ($data['notes'] ? ': ' . $data['notes'] : '')
            );

            // Record the specific adjustment
            $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
            return $this->adjustmentRepo->create($data);
        });
    }

    public function getHistory(string $from = null, string $to = null, string $type = null, int $productId = null): Collection
    {
        return $this->adjustmentRepo->getFiltered($from, $to, $type, $productId);
    }
}

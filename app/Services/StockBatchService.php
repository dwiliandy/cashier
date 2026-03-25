<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockBatch;
use App\Repositories\Contracts\StockBatchRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StockBatchService
{
    public function __construct(
        private StockBatchRepositoryInterface $batchRepo,
        private ProductRepositoryInterface $productRepo,
        private StockService $stockService,
    ) {}

    public function getAll(): Collection
    {
        return $this->batchRepo->all(['product', 'user']);
    }

    public function getActiveProducts(): Collection
    {
        return Product::active()->orderBy('name')->get();
    }

    public function create(int $productId, array $data): StockBatch
    {
        $product = Product::findOrFail($productId);
        return $this->stockService->addBatch($product, $data);
    }

    public function update(StockBatch $batch, array $data): StockBatch
    {
        $updated = $this->batchRepo->update($batch, $data);
        ActivityLog::log('batch_update', "Batch {$updated->batch_number} diperbarui", $updated);
        return $updated;
    }

    public function delete(StockBatch $batch): bool
    {
        $product = $batch->product;
        $remaining = $batch->remaining_quantity;

        if ($remaining > 0 && $product->stock >= $remaining) {
            $product->decrement('stock', $remaining);
        }

        ActivityLog::log('batch_delete', "Batch {$batch->batch_number} dihapus (sisa: {$remaining})", $product);
        return $this->batchRepo->delete($batch);
    }
}

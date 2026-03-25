<?php
namespace App\Repositories;

use App\Models\StockBatch;
use App\Models\Product;
use App\Repositories\Contracts\StockBatchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StockBatchRepository implements StockBatchRepositoryInterface
{
    public function all(array $relations = []): Collection
    {
        return StockBatch::with($relations)->latest()->get();
    }

    public function find(int $id): ?StockBatch
    {
        return StockBatch::find($id);
    }

    public function create(array $data): StockBatch
    {
        return StockBatch::create($data);
    }

    public function update(StockBatch $batch, array $data): StockBatch
    {
        $batch->update($data);
        return $batch->fresh();
    }

    public function delete(StockBatch $batch): bool
    {
        return $batch->delete();
    }

    public function getByProductFIFO(Product $product): Collection
    {
        return $product->stockBatches()->hasStock()->orderBy('created_at')->get();
    }
}

<?php
namespace App\Repositories\Contracts;

use App\Models\StockBatch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface StockBatchRepositoryInterface
{
    public function all(array $relations = []): Collection;
    public function find(int $id): ?StockBatch;
    public function create(array $data): StockBatch;
    public function update(StockBatch $batch, array $data): StockBatch;
    public function delete(StockBatch $batch): bool;
    public function getByProductFIFO(Product $product): Collection;
}

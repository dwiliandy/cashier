<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
  public function all(array $relations = []): Collection;
  public function find(int $id): ?Product;
  public function create(array $data): Product;
  public function update(Product $product, array $data): Product;
  public function delete(Product $product): bool;
  public function search(string $query, int $limit = 20): Collection;
  public function activeInStock(array $relations = []): Collection;
}

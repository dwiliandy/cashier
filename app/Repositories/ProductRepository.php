<?php
namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(array $relations = []): Collection
    {
        return Product::with($relations)->latest()->get();
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function search(string $query, int $limit = 20): Collection
    {
        return Product::active()
            ->where('stock', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', $query)
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->with('category')
            ->limit($limit)
            ->get();
    }

    public function activeInStock(array $relations = []): Collection
    {
        return Product::active()->where('stock', '>', 0)->with($relations)->get();
    }
}

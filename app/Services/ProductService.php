<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function getAll(): Collection
    {
        return $this->productRepo->all(['category']);
    }

    public function create(array $data): \App\Models\Product
    {
        $product = $this->productRepo->create($data);
        ActivityLog::log('product_create', "Produk '{$product->name}' ditambahkan", $product);
        return $product;
    }

    public function update(\App\Models\Product $product, array $data): \App\Models\Product
    {
        if (isset($data['image']) && $product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $updated = $this->productRepo->update($product, $data);
        ActivityLog::log('product_update', "Produk '{$updated->name}' diperbarui", $updated);
        return $updated;
    }

    public function delete(\App\Models\Product $product): bool
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        ActivityLog::log('product_delete', "Produk '{$product->name}' dihapus", $product);
        return $this->productRepo->delete($product);
    }

    public function search(?string $query): Collection
    {
        if (!$query) return $this->productRepo->activeInStock(['category']);
        return $this->productRepo->search($query);
    }

    public function getActiveInStock(): Collection
    {
        return $this->productRepo->activeInStock(['category']);
    }
}

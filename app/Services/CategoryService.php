<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function getAll(): Collection
    {
        return $this->categoryRepo->allWithProductCount();
    }

    public function getActive(): Collection
    {
        return $this->categoryRepo->active();
    }

    public function create(array $data): Category
    {
        $category = $this->categoryRepo->create($data);
        ActivityLog::log('category_create', "Kategori '{$category->name}' ditambahkan", $category);
        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        $updated = $this->categoryRepo->update($category, $data);
        ActivityLog::log('category_update', "Kategori '{$updated->name}' diperbarui", $updated);
        return $updated;
    }

    public function delete(Category $category): bool
    {
        ActivityLog::log('category_delete', "Kategori '{$category->name}' dihapus", $category);
        return $this->categoryRepo->delete($category);
    }
}

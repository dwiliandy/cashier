<?php
namespace App\Http\Controllers;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService,
    ) {}

    public function index()
    {
        $categories = $this->categoryService->getAll();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $this->categoryService->create($request->only('name', 'description'));
        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, \App\Models\Category $category)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $this->categoryService->update($category, $request->only('name', 'description'));
        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(\App\Models\Category $category)
    {
        $this->categoryService->delete($category);
        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}

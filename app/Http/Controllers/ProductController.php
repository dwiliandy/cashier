<?php
namespace App\Http\Controllers;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private CategoryService $categoryService,
    ) {}

    public function index()
    {
        $products = $this->productService->getAll();
        $categories = $this->categoryService->getActive();
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = $this->categoryService->getActive();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            $v['image'] = $request->file('image')->store('products', 'public');
        }
        $v['is_active'] = $request->has('is_active');

        $this->productService->create($v);
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(\App\Models\Product $product)
    {
        $categories = $this->categoryService->getActive();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, \App\Models\Product $product)
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            $v['image'] = $request->file('image')->store('products', 'public');
        }
        $v['is_active'] = $request->has('is_active');

        $this->productService->update($product, $v);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(\App\Models\Product $product)
    {
        $this->productService->delete($product);
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }

    // API for POS
    public function apiSearch(Request $request)
    {
        return response()->json($this->productService->search($request->q));
    }

    public function apiAll()
    {
        return response()->json($this->productService->getActiveInStock());
    }

    public function adjustStock(Request $request, \App\Models\Product $product, StockService $stockService)
    {
        $request->validate(['quantity' => 'required|integer|min:0', 'type' => 'required|in:in,out,adjustment', 'notes' => 'nullable|string']);
        $stockService->adjustStock($product, $request->quantity, $request->type, null, $request->notes);
        return back()->with('success', 'Stok berhasil diperbarui!');
    }
}

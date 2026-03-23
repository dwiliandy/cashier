<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->latest()->get();
        $categories = Category::active()->get();
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
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

        Product::create($v);
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
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
            if ($product->image) Storage::disk('public')->delete($product->image);
            $v['image'] = $request->file('image')->store('products', 'public');
        }
        $v['is_active'] = $request->has('is_active');

        $product->update($v);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }

    // API for POS
    public function apiSearch(Request $request)
    {
        $products = Product::active()
            ->where('stock', '>', 0)
            ->when($request->q, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('barcode', $s)->orWhere('sku', 'like', "%{$s}%"))
            ->with('category')
            ->limit(20)->get();
        return response()->json($products);
    }

    public function apiAll()
    {
        return response()->json(Product::active()->where('stock', '>', 0)->with('category')->get());
    }

    public function adjustStock(Request $request, Product $product, StockService $stockService)
    {
        $request->validate(['quantity' => 'required|integer|min:0', 'type' => 'required|in:in,out,adjustment', 'notes' => 'nullable|string']);
        $stockService->adjustStock($product, $request->quantity, $request->type, null, $request->notes);
        return back()->with('success', 'Stok berhasil diperbarui!');
    }
}

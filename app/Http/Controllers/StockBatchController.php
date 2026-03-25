<?php
namespace App\Http\Controllers;
use App\Services\StockBatchService;
use Illuminate\Http\Request;

class StockBatchController extends Controller
{
    public function __construct(
        private StockBatchService $batchService,
    ) {}

    public function index()
    {
        $batches = $this->batchService->getAll();
        $products = $this->batchService->getActiveProducts();
        return view('stock-batches.index', compact('batches', 'products'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $this->batchService->create($v['product_id'], $v);
        return back()->with('success', 'Batch stok berhasil ditambahkan!');
    }

    public function update(Request $request, \App\Models\StockBatch $stockBatch)
    {
        $v = $request->validate([
            'purchase_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $this->batchService->update($stockBatch, $v);
        return back()->with('success', 'Batch berhasil diperbarui!');
    }

    public function destroy(\App\Models\StockBatch $stockBatch)
    {
        $this->batchService->delete($stockBatch);
        return back()->with('success', 'Batch berhasil dihapus!');
    }
}

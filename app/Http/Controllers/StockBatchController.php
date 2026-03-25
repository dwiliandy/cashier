<?php
namespace App\Http\Controllers;
use App\Services\StockBatchService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), "r");
        
        // Skip header
        $header = fgetcsv($handle, 1000, ",");
        
        $success = 0;
        $errors = [];
        $row = 1;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if (count($data) < 3) continue;

                $sku = $data[0];
                $quantity = (int)$data[1];
                $purchasePrice = (float)$data[2];
                $supplier = $data[3] ?? null;
                $expiryDate = !empty($data[4]) ? $data[4] : null;
                $notes = $data[5] ?? null;

                $product = Product::where('sku', $sku)->first();
                if (!$product) {
                    $errors[] = "Baris {$row}: SKU {$sku} tidak ditemukan.";
                    continue;
                }

                $this->batchService->create($product->id, [
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'supplier' => $supplier,
                    'expiry_date' => $expiryDate,
                    'notes' => $notes,
                ]);
                $success++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage());
        }
        fclose($handle);

        $msg = "Berhasil mengimpor {$success} batch.";
        if (count($errors) > 0) {
            return back()->with('success', $msg)->with('import_errors', $errors);
        }
        return back()->with('success', $msg);
    }
}

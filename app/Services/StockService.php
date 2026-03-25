<?php
namespace App\Services;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\StockBatch;
use App\Models\ActivityLog;
use App\Repositories\Contracts\StockBatchRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockAlert;

class StockService
{
    public function __construct(
        private StockBatchRepositoryInterface $batchRepo,
    ) {}

    public function adjustStock(Product $product, int $quantity, string $type, ?string $reference = null, ?string $notes = null): StockLog
    {
        return DB::transaction(function () use ($product, $quantity, $type, $reference, $notes) {
            $stockBefore = $product->stock;

            if ($type === 'in') {
                $product->increment('stock', $quantity);
            } elseif ($type === 'out') {
                if ($product->stock < $quantity) {
                    throw new \Exception("Stok tidak mencukupi untuk {$product->name}");
                }
                $product->decrement('stock', $quantity);
                $this->decrementBatchesFIFO($product, $quantity);
            } else {
                $product->update(['stock' => $quantity]);
            }

            $product->refresh();

            $log = StockLog::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $type === 'adjustment' ? $quantity - $stockBefore : $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock,
                'reference' => $reference,
                'notes' => $notes,
                'user_id' => auth()->id(),
            ]);

            ActivityLog::log('stock_update', "Stok {$product->name}: {$stockBefore} → {$product->stock}", $product);

            if ($product->isLowStock()) {
                $this->sendLowStockAlert($product);
            }

            return $log;
        });
    }

    public function addBatch(Product $product, array $data): StockBatch
    {
        return DB::transaction(function () use ($product, $data) {
            $batch = $this->batchRepo->create([
                'product_id' => $product->id,
                'batch_number' => StockBatch::generateBatchNumber(),
                'quantity' => $data['quantity'],
                'remaining_quantity' => $data['quantity'],
                'purchase_price' => $data['purchase_price'],
                'supplier' => $data['supplier'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $stockBefore = $product->stock;
            $product->increment('stock', $data['quantity']);
            $product->refresh();

            StockLog::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $data['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock,
                'reference' => $batch->batch_number,
                'notes' => "Batch masuk: {$batch->batch_number}" . (!empty($data['supplier']) ? " dari {$data['supplier']}" : ''),
                'user_id' => auth()->id(),
            ]);

            ActivityLog::log('batch_create', "Batch {$batch->batch_number} ditambahkan untuk {$product->name} (qty: {$data['quantity']}, harga beli: {$data['purchase_price']})", $product);

            return $batch;
        });
    }

    private function decrementBatchesFIFO(Product $product, int $quantity): void
    {
        $batches = $this->batchRepo->getByProductFIFO($product);
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $deduct = min($batch->remaining_quantity, $remaining);
            $batch->decrement('remaining_quantity', $deduct);
            $remaining -= $deduct;
        }
    }

    public function getLowStockProducts()
    {
        return Product::active()->lowStock()->with('category')->get();
    }

    private function sendLowStockAlert(Product $product): void
    {
        $ownerEmail = config('app.owner_email');
        if (!$ownerEmail) return;

        try {
            Mail::to($ownerEmail)->queue(new LowStockAlert(collect([$product->load('category')])));
        } catch (\Exception $e) {
            Log::warning("Failed to send low stock alert: " . $e->getMessage());
        }
    }
}

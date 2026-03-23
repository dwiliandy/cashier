<?php
namespace App\Services;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class StockService
{
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

            return $log;
        });
    }

    public function getLowStockProducts()
    {
        return Product::active()->lowStock()->with('category')->get();
    }
}

<?php
namespace App\Services;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private StockService $stockService,
        private PointService $pointService,
    ) {}

    public function createTransaction(array $data, array $items): Transaction
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            foreach ($items as &$item) {
                $itemSubtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $item['subtotal'] = $itemSubtotal;
                $subtotal += $itemSubtotal;
            }

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;

            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'user_id' => auth()->id(),
                'member_id' => $data['member_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $data['paid_amount'],
                'change_amount' => max(0, $data['paid_amount'] - $total),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => $data['payment_status'] ?? 'paid',
                'notes' => $data['notes'] ?? null,
                'local_id' => $data['local_id'] ?? null,
                'synced_at' => isset($data['local_id']) ? now() : null,
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);

                // Decrement stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $this->stockService->adjustStock($product, $item['quantity'], 'out', $transaction->invoice_number, 'Penjualan');
                }
            }

            // Award points if member
            if ($transaction->member_id) {
                $member = Member::find($transaction->member_id);
                if ($member) {
                    $this->pointService->earnPoints($member, $transaction);
                }
            }

            ActivityLog::log('transaction_create', "Transaksi {$transaction->invoice_number} sebesar Rp " . number_format($total, 0, ',', '.'), $transaction);

            return $transaction->load('items', 'member', 'user');
        });
    }

    public function syncTransaction(array $data, array $items): Transaction
    {
        // Check if already synced
        if (!empty($data['local_id'])) {
            $existing = Transaction::where('local_id', $data['local_id'])->first();
            if ($existing) return $existing;
        }

        return $this->createTransaction($data, $items);
    }
}

<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function midtrans(Request $request, MidtransService $midtrans, StockService $stockService)
    {
        try {
            $notif = $midtrans->getNotification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;

            Log::info("Midtrans Webhook Received: Invoice {$orderId}, Status {$transaction}, Type {$type}");

            $tx = Transaction::with('items.product')->where('invoice_number', $orderId)->first();
            if (!$tx) {
                Log::warning("Transaction not found for order id: {$orderId}");
                return response()->json(['message' => 'Transaction not found']);
            }

            if ($transaction == 'capture' || $transaction == 'settlement') {
                if ($type == 'credit_card' && $fraud == 'challenge') {
                    $tx->update(['payment_status' => 'pending']);
                } else {
                    if ($tx->payment_status !== 'paid') {
                        $tx->update(['payment_status' => 'paid']);
                        // We already deducted stock at transaction creation.
                        // Wait, if it was pending and we deduct at creation, it's fine.
                        // We will also rely on webhook for reliability, but POS frontend will handle immediate state update.
                    }
                }
            } else if ($transaction == 'cancel' || $transaction == 'deny' || $transaction == 'expire') {
                if ($tx->payment_status !== 'failed') {
                    $tx->update(['payment_status' => 'failed']);
                    // Restore stock if transaction failed
                    foreach ($tx->items as $item) {
                        if ($item->product) {
                            $stockService->adjustStock($item->product, $item->quantity, 'in', $tx->invoice_number, 'Payment failed/expired: Restored stock');
                        }
                    }
                }
            } else if ($transaction == 'pending') {
                $tx->update(['payment_status' => 'pending']);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

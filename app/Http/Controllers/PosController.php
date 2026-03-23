<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Member;
use App\Models\Setting;
use App\Services\TransactionService;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::active()->where('stock', '>', 0)->with('category')->get();
        $categories = \App\Models\Category::active()->get();
        $storeName = Setting::get('store_name', 'Entice POS');
        return view('pos.index', compact('products', 'categories', 'storeName'));
    }

    public function processTransaction(Request $request, TransactionService $transactionService, MidtransService $midtransService)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,ewallet,qris',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'member_id' => 'nullable|exists:members,id',
            'notes' => 'nullable|string',
            'local_id' => 'nullable|string',
        ]);

        try {
            $isCash = $request->payment_method === 'cash';
            $txData = $request->only('paid_amount', 'payment_method', 'discount', 'tax', 'member_id', 'notes', 'local_id');
            $txData['payment_status'] = $isCash ? 'paid' : 'pending';

            $transaction = $transactionService->createTransaction($txData, $request->items);

            $snapToken = null;
            if (!$isCash) {
                $itemDetails = [];
                foreach ($request->items as $item) {
                    $itemDetails[] = ['id' => $item['product_id'], 'price' => (int) $item['price'], 'quantity' => (int) $item['quantity'], 'name' => $item['name']];
                }
                if ($transaction->discount > 0) {
                    $itemDetails[] = ['id' => 'DISCOUNT', 'price' => -(int) $transaction->discount, 'quantity' => 1, 'name' => 'Diskon'];
                }

                $customer = ['first_name' => 'Pelanggan', 'email' => 'guest@entice.local'];
                if ($transaction->member) {
                    $customer['first_name'] = $transaction->member->name;
                    $customer['phone'] = $transaction->member->phone_number;
                    if ($transaction->member->email) $customer['email'] = $transaction->member->email;
                }

                $snapToken = $midtransService->getSnapToken(
                    ['order_id' => $transaction->invoice_number, 'gross_amount' => (int) $transaction->total],
                    $itemDetails,
                    $customer
                );
            }

            return response()->json([
                'success' => true,
                'transaction' => $transaction->load('items', 'member', 'user'),
                'snap_token' => $snapToken,
                'message' => 'Transaksi berhasil!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function syncTransactions(Request $request, TransactionService $transactionService)
    {
        $results = [];
        foreach ($request->transactions as $txData) {
            try {
                $tx = $transactionService->syncTransaction($txData, $txData['items']);
                $results[] = ['local_id' => $txData['local_id'] ?? null, 'success' => true, 'id' => $tx->id];
            } catch (\Exception $e) {
                $results[] = ['local_id' => $txData['local_id'] ?? null, 'success' => false, 'error' => $e->getMessage()];
            }
        }
        return response()->json(['results' => $results]);
    }
}

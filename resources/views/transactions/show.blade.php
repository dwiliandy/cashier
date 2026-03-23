<x-layouts.app title="Detail Transaksi">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('transactions.index') }}" class="hover:text-indigo-600">Transaksi</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">{{ $transaction->invoice_number }}</span>
    </nav>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Item Transaksi</h3>
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200">
                    <th class="text-left py-2 text-xs font-semibold text-gray-500">Produk</th>
                    <th class="text-right py-2 text-xs font-semibold text-gray-500">Harga</th>
                    <th class="text-center py-2 text-xs font-semibold text-gray-500">Qty</th>
                    <th class="text-right py-2 text-xs font-semibold text-gray-500">Diskon</th>
                    <th class="text-right py-2 text-xs font-semibold text-gray-500">Subtotal</th>
                </tr></thead>
                <tbody>
                    @foreach($transaction->items as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-2.5 text-gray-900">{{ $item->product_name }}</td>
                            <td class="py-2.5 text-right text-gray-500">Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                            <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                            <td class="py-2.5 text-right text-red-500">{{ $item->discount > 0 ? 'Rp ' . number_format($item->discount, 0, ',', '.') : '-' }}</td>
                            <td class="py-2.5 text-right font-semibold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Ringkasan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                    @if($transaction->discount > 0)<div class="flex justify-between text-red-500"><span>Diskon</span><span>-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>@endif
                    @if($transaction->tax > 0)<div class="flex justify-between text-gray-500"><span>Pajak</span><span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>@endif
                    <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-200"><span>Total</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-gray-500"><span>Dibayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-gray-500"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Info</h3>
                <div class="space-y-2 text-sm text-gray-500">
                    <p><strong class="text-gray-700">Kasir:</strong> {{ $transaction->user->name ?? '-' }}</p>
                    <p><strong class="text-gray-700">Member:</strong> {{ $transaction->member->name ?? '-' }}</p>
                    <p><strong class="text-gray-700">Metode:</strong> <span class="uppercase">{{ $transaction->payment_method }}</span></p>
                    <p><strong class="text-gray-700">Tanggal:</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                    @if($transaction->memberPoints->count())
                        <p><strong class="text-gray-700">Poin Diperoleh:</strong> <span class="text-emerald-600 font-semibold">+{{ number_format($transaction->memberPoints->where('type', 'earn')->sum('points'), 0) }}</span></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

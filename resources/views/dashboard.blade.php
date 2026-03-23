<x-layouts.app title="Dashboard">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Penjualan Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['daily_sales'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['total_transactions_today'] }} transaksi</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Penjualan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['monthly_sales'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['total_transactions_month'] }} transaksi</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Rata-rata Transaksi</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['avg_transaction'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Total Member</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_members'] }}</p>
            @if($stats['low_stock_count'] > 0)
                <p class="text-xs text-red-500 mt-1 font-medium">⚠ {{ $stats['low_stock_count'] }} produk stok rendah</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sales Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Tren Penjualan (7 Hari Terakhir)</h3>
            <canvas id="salesChart" height="120"></canvas>
        </div>

        {{-- Top Products --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Produk Terlaris</h3>
            @if($topProducts->count() > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $i => $tp)
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $tp->product_name }}</p>
                                <p class="text-xs text-gray-400">{{ $tp->total_qty }} terjual</p>
                            </div>
                            <span class="text-xs font-semibold text-emerald-600">Rp {{ number_format($tp->total_revenue, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 text-center py-8">Belum ada data penjualan</p>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: @json($salesChart['labels']),
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: @json($salesChart['data']),
                    backgroundColor: 'rgba(99, 102, 241, 0.15)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
    @endpush
</x-layouts.app>

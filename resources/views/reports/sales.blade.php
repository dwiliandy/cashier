<x-layouts.app title="Laporan Penjualan">
    <h2 class="text-xl font-bold text-gray-900 mb-6">Laporan Penjualan</h2>
    <form class="flex flex-wrap gap-3 mb-6" method="GET">
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <button class="px-4 py-2.5 rounded-xl text-sm font-medium bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all">Filter</button>
    </form>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400 uppercase mb-1">Total Penjualan</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($transactions->sum('total'), 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">{{ $transactions->count() }} transaksi</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400 uppercase mb-1">Rata-rata per Transaksi</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($transactions->avg('total') ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Penjualan Per Produk</h3>
            <table id="sales-product-table" class="w-full text-sm stripe hover" style="width:100%">
                <thead><tr><th class="text-left">Produk</th><th class="text-center">Qty</th><th class="text-right">Revenue</th></tr></thead>
                <tbody>
                    @foreach($salesByProduct as $sp)
                        <tr><td class="text-gray-900">{{ $sp->product_name }}</td><td class="text-center">{{ $sp->total_qty }}</td><td class="text-right font-semibold text-gray-900">Rp {{ number_format($sp->total_revenue, 0, ',', '.') }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Daftar Transaksi</h3>
            <table id="sales-tx-table" class="w-full text-sm stripe hover" style="width:100%">
                <thead><tr><th class="text-left">Invoice</th><th class="text-left">Tanggal</th><th class="text-center">Metode</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td class="text-xs font-mono text-indigo-600">{{ $tx->invoice_number }}</td>
                            <td class="text-xs text-gray-400">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center"><span class="uppercase text-xs text-gray-500">{{ $tx->payment_method }}</span></td>
                            <td class="text-right font-semibold text-gray-900">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            var dtOpts = {
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Excel', exportOptions: { columns: ':visible' } },
                    { extend: 'pdfHtml5', text: '📄 PDF', exportOptions: { columns: ':visible' } },
                    { extend: 'print', text: '🖨 Cetak', exportOptions: { columns: ':visible' } }
                ],
                language: { search:'Cari:', info:'_START_-_END_ / _TOTAL_', infoEmpty:'Kosong', zeroRecords:'Tidak ditemukan', paginate:{previous:'‹',next:'›'} },
                pageLength: 25,
            };
            $('#sales-product-table').DataTable({...dtOpts, buttons: [{extend:'excelHtml5',text:'📊 Excel',title:'Penjualan Per Produk'},{extend:'pdfHtml5',text:'📄 PDF',title:'Penjualan Per Produk'},{extend:'print',text:'🖨 Cetak',title:'Penjualan Per Produk'}], order:[[2,'desc']]});
            $('#sales-tx-table').DataTable({...dtOpts, buttons: [{extend:'excelHtml5',text:'📊 Excel',title:'Daftar Transaksi'},{extend:'pdfHtml5',text:'📄 PDF',title:'Daftar Transaksi'},{extend:'print',text:'🖨 Cetak',title:'Daftar Transaksi'}], order:[[1,'desc']]});
        });
    </script>
    @endpush
</x-layouts.app>

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
                <tfoot><tr>
                    <th>Produk</th>
                    <th class="text-center">Qty</th>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="number" class="dt-range-min" placeholder="Min">
                            <input type="number" class="dt-range-max" placeholder="Max">
                        </div>
                    </th>
                </tr></tfoot>
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
                <tfoot><tr>
                    <th>Invoice</th>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="date" class="dt-date-min">
                            <input type="date" class="dt-date-max">
                        </div>
                    </th>
                    <th>Metode</th>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="number" class="dt-range-min" placeholder="Min">
                            <input type="number" class="dt-range-max" placeholder="Max">
                        </div>
                    </th>
                </tr></tfoot>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            function setupColumnSearch(tableId) {
                $(tableId + ' tfoot th').each(function() {
                    var title = $(this).text();
                    if (title) $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
                });
            }
            setupColumnSearch('#sales-product-table');
            setupColumnSearch('#sales-tx-table');

            var dtOpts = {
                dom: 'Bfrtip',
                language: { search:'Cari:', info:'_START_-_END_ / _TOTAL_', infoEmpty:'Kosong', zeroRecords:'Tidak ditemukan', paginate:{previous:'‹',next:'›'} },
                pageLength: 25,
                scrollX: true
            };

            // Custom Range Filters for Sales
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var tableId = settings.nTable.id;
                
                if (tableId === 'sales-product-table') {
                    var min = parseInt($('#sales-product-table tfoot .dt-range-min').val(), 10);
                    var max = parseInt($('#sales-product-table tfoot .dt-range-max').val(), 10);
                    var val = parseFloat(data[2].replace(/[^\d]/g, '')) || 0;
                    if (!isNaN(min) && val < min) return false;
                    if (!isNaN(max) && val > max) return false;
                }

                if (tableId === 'sales-tx-table') {
                    // Date range (1)
                    var dMin = $('#sales-tx-table tfoot .dt-date-min').val();
                    var dMax = $('#sales-tx-table tfoot .dt-date-max').val();
                    var parts = data[1].split(' ')[0].split('/');
                    if (parts.length === 3) {
                        var dVal = parts[2] + '-' + parts[1] + '-' + parts[0];
                        if (dMin && dVal < dMin) return false;
                        if (dMax && dVal > dMax) return false;
                    }
                    // Total range (3)
                    var tMin = parseInt($('#sales-tx-table tfoot .dt-range-min').val(), 10);
                    var tMax = parseInt($('#sales-tx-table tfoot .dt-range-max').val(), 10);
                    var tVal = parseFloat(data[3].replace(/[^\d]/g, '')) || 0;
                    if (!isNaN(tMin) && tVal < tMin) return false;
                    if (!isNaN(tMax) && tVal > tMax) return false;
                }

                return true;
            });

            var t1 = $('#sales-product-table').DataTable({...dtOpts, buttons: [{extend:'excelHtml5',text:'📊 Excel',title:'Penjualan Per Produk'},{extend:'pdfHtml5',text:'📄 PDF',title:'Penjualan Per Produk'},{extend:'print',text:'🖨 Cetak',title:'Penjualan Per Produk'}], order:[[2,'desc']]});
            var t2 = $('#sales-tx-table').DataTable({...dtOpts, buttons: [{extend:'excelHtml5',text:'📊 Excel',title:'Daftar Transaksi'},{extend:'pdfHtml5',text:'📄 PDF',title:'Daftar Transaksi'},{extend:'print',text:'🖨 Cetak',title:'Daftar Transaksi'}], order:[[1,'desc']]});

            [t1, t2].forEach(function(table) {
                table.columns().every(function() {
                    var that = this;
                    $('input', this.footer()).on('keyup change clear', function() {
                        if (that.search() !== this.value) that.search(this.value).draw();
                    });
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>

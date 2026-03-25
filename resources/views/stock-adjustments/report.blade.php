<x-layouts.app title="Laporan Penyesuaian Stok">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Laporan Penyesuaian Stok</h2>
            <p class="text-sm text-gray-500">Summary aktivitas penyesuaian stok produk</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
        <form class="flex flex-wrap gap-4 items-end" method="GET">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Produk</label>
                <select name="product_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none min-w-[200px] select2">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-6 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-all">Filter</button>
            <a href="{{ route('stock-adjustments.report') }}" class="px-6 py-2 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <table id="report-adjustments-table" class="w-full text-sm stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th class="text-left">Tanggal</th>
                    <th class="text-left">Produk</th>
                    <th class="text-center">Tipe</th>
                    <th class="text-right">Total Qty</th>
                    <th class="text-left">Alasan</th>
                    <th class="text-left">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustments as $adj)
                    <tr>
                        <td>{{ $adj->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-medium">{{ $adj->product->name }}</td>
                        <td class="text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium 
                                @if($adj->type === 'in') bg-emerald-50 text-emerald-600
                                @elseif($adj->type === 'out') bg-red-50 text-red-600
                                @else bg-blue-50 text-blue-600
                                @endif">
                                {{ ucfirst($adj->type) }}
                            </span>
                        </td>
                        <td class="text-right font-semibold">{{ $adj->quantity }}</td>
                        <td>{{ $adj->reason }}</td>
                        <td class="text-gray-500 text-xs">{{ $adj->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <th>
                        <select class="dt-filter-select">
                            <option value="">Semua</option>
                            <option value="In">In</option>
                            <option value="Out">Out</option>
                            <option value="Adjustment">Adjustment</option>
                        </select>
                    </th>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="number" class="dt-range-min" placeholder="Min">
                            <input type="number" class="dt-range-max" placeholder="Max">
                        </div>
                    </th>
                    <th>Alasan</th>
                    <th>Keterangan</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#report-adjustments-table tfoot th').each(function(i) {
                var title = $(this).text();
                if (i === 0 || i === 1 || i === 4 || i === 5) {
                    $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
                }
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'report-adjustments-table') return true;
                
                var min = parseInt($('#report-adjustments-table tfoot .dt-range-min').val(), 10);
                var max = parseInt($('#report-adjustments-table tfoot .dt-range-max').val(), 10);
                var val = parseFloat(data[3]) || 0;

                if (!isNaN(min) && val < min) return false;
                if (!isNaN(max) && val > max) return false;
                return true;
            });

            var table = $('#report-adjustments-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'Laporan Penyesuaian Stok' },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'Laporan Penyesuaian Stok' },
                    { extend: 'print', text: '🖨 Cetak', title: 'Laporan Penyesuaian Stok' }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan', paginate: { first: '«', last: '»', previous: '‹', next: '›' } },
                pageLength: 50,
                order: [[0, 'desc']],
                scrollX: true
            });

            table.columns().every(function() {
                var that = this;
                $('input.dt-column-search', this.footer()).on('keyup change clear', function() {
                    if (that.search() !== this.value) that.search(this.value).draw();
                });
                $('select.dt-filter-select', this.footer()).on('change', function() {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    that.search(val ? '^' + val + '$' : '', true, false).draw();
                });
            });

            $('.dt-range-min, .dt-range-max').on('keyup change clear', function() {
                table.draw();
            });
        });
    </script>
    @endpush
</x-layouts.app>

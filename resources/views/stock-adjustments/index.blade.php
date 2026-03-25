<x-layouts.app title="Penyesuaian Stok">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Penyesuaian Stok</h2>
            <p class="text-sm text-gray-500">Sesuaikan jumlah stok produk secara manual</p>
        </div>
        <a href="{{ route('stock-adjustments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-200 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Penyesuaian
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <table id="adjustments-table" class="w-full text-sm stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th class="text-left">Waktu</th>
                    <th class="text-left">Produk</th>
                    <th class="text-center">Tipe</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-left">Alasan</th>
                    <th class="text-left">User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustments as $adj)
                    <tr>
                        <td class="text-xs text-gray-400 whitespace-nowrap">{{ $adj->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="font-medium text-gray-900">{{ $adj->product->name }}</td>
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
                        <td class="text-gray-600">{{ $adj->reason }}</td>
                        <td class="text-xs text-gray-400">{{ $adj->user->name }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="date" class="dt-date-min">
                            <input type="date" class="dt-date-max">
                        </div>
                    </th>
                    <th>Produk</th>
                    <th>
                        <select class="dt-filter-select">
                            <option value="">Semua</option>
                            <option value="In">In</option>
                            <option value="Out">Out</option>
                            <option value="Adjustment">Adjustment</option>
                        </select>
                    </th>
                    <th>Jumlah</th>
                    <th>Alasan</th>
                    <th>User</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#adjustments-table tfoot th').each(function(i) {
                var title = $(this).text();
                if (i === 1 || i === 4 || i === 5) {
                    $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
                }
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'adjustments-table') return true;
                
                var dMin = $('#adjustments-table tfoot .dt-date-min').val();
                var dMax = $('#adjustments-table tfoot .dt-date-max').val();
                var dValStr = data[0].split(' ')[0];
                var parts = dValStr.split('/');
                if (parts.length === 3) {
                    var dVal = parts[2] + '-' + parts[1] + '-' + parts[0];
                    if (dMin && dVal < dMin) return false;
                    if (dMax && dVal > dMax) return false;
                }
                return true;
            });

            var table = $('#adjustments-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'History Penyesuaian Stok' },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'History Penyesuaian Stok' }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan', paginate: { first: '«', last: '»', previous: '‹', next: '›' } },
                pageLength: 25,
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

            $('.dt-date-min, .dt-date-max').on('keyup change clear', function() {
                table.draw();
            });
        });
    </script>
    @endpush
</x-layouts.app>

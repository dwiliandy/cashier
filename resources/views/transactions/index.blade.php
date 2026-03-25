<x-layouts.app title="Riwayat Transaksi">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Riwayat Transaksi</h2>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <table id="transactions-table" class="w-full text-sm stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th class="text-left">Invoice</th>
                    <th class="text-left">Tanggal</th>
                    <th class="text-left">Kasir</th>
                    <th class="text-left">Member</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Diskon</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Metode</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td class="font-mono text-xs text-indigo-600 font-medium">{{ $tx->invoice_number }}</td>
                        <td class="text-gray-500 text-xs">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-gray-700">{{ $tx->user->name ?? '-' }}</td>
                        <td class="text-gray-500">{{ $tx->member->name ?? '-' }}</td>
                        <td class="text-right text-gray-500">Rp {{ number_format($tx->subtotal, 0, ',', '.') }}</td>
                        <td class="text-right text-red-500">{{ $tx->discount > 0 ? 'Rp '.number_format($tx->discount, 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-semibold text-gray-900">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                        <td class="text-center"><span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-medium uppercase">{{ $tx->payment_method }}</span></td>
                        <td class="text-center">
                            <a href="{{ route('transactions.show', $tx) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all inline-block" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Invoice</th>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="date" class="dt-date-min">
                            <input type="date" class="dt-date-max">
                        </div>
                    </th>
                    <th>Kasir</th>
                    <th>Member</th>
                    <th>Subtotal</th>
                    <th>Diskon</th>
                    <th>
                        <div class="dt-range-wrap">
                            <input type="number" class="dt-range-min" placeholder="Min">
                            <input type="number" class="dt-range-max" placeholder="Max">
                        </div>
                    </th>
                    <th>
                        <select class="dt-filter-select">
                            <option value="">Semua</option>
                            <option value="Tunai">Tunai</option>
                            <option value="Midtrans">Midtrans</option>
                        </select>
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#transactions-table tfoot th').each(function(i) {
                var title = $(this).text();
                if (i === 0 || i === 2 || i === 3) {
                    $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
                }
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'transactions-table') return true;
                
                var result = true;

                // Date Range (1)
                var dMin = $('#transactions-table tfoot .dt-date-min').val();
                var dMax = $('#transactions-table tfoot .dt-date-max').val();
                var dValStr = data[1].split(' ')[0];
                var parts = dValStr.split('/');
                if (parts.length === 3) {
                    var dVal = parts[2] + '-' + parts[1] + '-' + parts[0];
                    if (dMin && dVal < dMin) result = false;
                    if (dMax && dVal > dMax) result = false;
                }

                // Total Range (6)
                var tMin = parseInt($('#transactions-table tfoot .dt-range-min').val(), 10);
                var tMax = parseInt($('#transactions-table tfoot .dt-range-max').val(), 10);
                var tVal = parseFloat(data[6].replace(/[^\d]/g, '')) || 0;
                if (!isNaN(tMin) && tVal < tMin) result = false;
                if (!isNaN(tMax) && tVal > tMax) result = false;

                return result;
            });

            var table = $('#transactions-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'Riwayat Transaksi', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'Riwayat Transaksi', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'print', text: '🖨 Cetak', title: 'Riwayat Transaksi', exportOptions: { columns: [0,1,2,3,4,5,6,7] } }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ transaksi', infoEmpty: 'Tidak ada data', zeroRecords: 'Transaksi tidak ditemukan', paginate: { first: '«', last: '»', previous: '‹', next: '›' } },
                pageLength: 25,
                order: [[1, 'desc']],
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

            $('.dt-range-min, .dt-range-max, .dt-date-min, .dt-date-max').on('keyup change clear', function() {
                table.draw();
            });
        });
    </script>
    @endpush
</x-layouts.app>

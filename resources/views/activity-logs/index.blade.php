<x-layouts.app title="Log Aktivitas">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Log Aktivitas</h2>
            <p class="text-sm text-gray-500">Semua aktivitas sistem tercatat di sini</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
        <form class="flex flex-wrap gap-3 items-end" method="GET">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Aksi</label>
                <select name="action" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                <select name="user_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 rounded-xl text-sm font-medium bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all">Filter</button>
            <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-50 text-gray-600 hover:bg-gray-100 transition-all">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <table id="logs-table" class="w-full text-sm stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th class="text-left">Waktu</th>
                    <th class="text-left">User</th>
                    <th class="text-left">Aksi</th>
                    <th class="text-left">Deskripsi</th>
                    <th class="text-left">Model</th>
                    <th class="text-left">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="text-gray-700">{{ $log->user->name ?? 'System' }}</td>
                        <td>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                @if(str_contains($log->action, 'create')) bg-emerald-50 text-emerald-600
                                @elseif(str_contains($log->action, 'update')) bg-blue-50 text-blue-600
                                @elseif(str_contains($log->action, 'delete')) bg-red-50 text-red-600
                                @else bg-gray-100 text-gray-600
                                @endif
                            ">{{ $log->action }}</span>
                        </td>
                        <td class="text-gray-600 max-w-xs truncate">{{ $log->description ?? '-' }}</td>
                        <td class="text-xs text-gray-400">
                            @if($log->model_type)
                                {{ class_basename($log->model_type) }}#{{ $log->model_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '-' }}</td>
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
                    <th>User</th>
                    <th>
                        <select class="dt-filter-select">
                            <option value="">Semua</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}">{{ $act }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>Deskripsi</th>
                    <th>Model</th>
                    <th>IP Address</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#logs-table tfoot th').each(function(i) {
                var title = $(this).text();
                if ([1,3,4,5].includes(i)) {
                    $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
                }
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'logs-table') return true;
                
                var dMin = $('#logs-table tfoot .dt-date-min').val();
                var dMax = $('#logs-table tfoot .dt-date-max').val();
                var dValStr = data[0].split(' ')[0];
                var parts = dValStr.split('/');
                if (parts.length === 3) {
                    var dVal = parts[2] + '-' + parts[1] + '-' + parts[0];
                    if (dMin && dVal < dMin) return false;
                    if (dMax && dVal > dMax) return false;
                }
                return true;
            });

            var table = $('#logs-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'Log Aktivitas', exportOptions: { columns: [0,1,2,3,4,5] } },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'Log Aktivitas', exportOptions: { columns: [0,1,2,3,4,5] } },
                    { extend: 'print', text: '🖨 Cetak', title: 'Log Aktivitas', exportOptions: { columns: [0,1,2,3,4,5] } }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ log', infoEmpty: 'Tidak ada data', zeroRecords: 'Log tidak ditemukan', paginate: { first: '«', last: '»', previous: '‹', next: '›' } },
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

            $('.dt-date-min, .dt-date-max').on('keyup change clear', function() {
                table.draw();
            });
        });
    </script>
    @endpush
</x-layouts.app>

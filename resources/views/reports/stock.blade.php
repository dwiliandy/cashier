<x-layouts.app title="Laporan Stok">
    <h2 class="text-xl font-bold text-gray-900 mb-6">Laporan Stok</h2>
    @if($lowStock->count())
    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4">
        <h3 class="text-sm font-semibold text-red-700 mb-2">⚠ {{ $lowStock->count() }} Produk Stok Rendah</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($lowStock as $p)
                <span class="bg-white border border-red-200 px-3 py-1 rounded-lg text-xs text-red-600">{{ $p->name }} ({{ $p->stock }})</span>
            @endforeach
        </div>
    </div>
    @endif
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <table id="stock-table" class="w-full text-sm stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th class="text-left">Produk</th>
                    <th class="text-left">Kategori</th>
                    <th class="text-left">SKU</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Min. Stok</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                    <tr class="{{ $p->isLowStock() ? 'bg-red-50/50' : '' }}">
                        <td class="font-medium text-gray-900">{{ $p->name }}</td>
                        <td class="text-gray-500">{{ $p->category?->name ?? '-' }}</td>
                        <td class="font-mono text-xs text-gray-400">{{ $p->sku }}</td>
                        <td class="text-center font-semibold" data-order="{{ $p->stock }}">{{ $p->stock }}</td>
                        <td class="text-center text-gray-400">{{ $p->minimum_stock }}</td>
                        <td class="text-center text-gray-400">{{ $p->unit }}</td>
                        <td class="text-center">
                            @if($p->stock <= 0) <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-medium">Habis</span>
                            @elseif($p->isLowStock()) <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs font-medium">Rendah</span>
                            @else <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-medium">Aman</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#stock-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'Laporan Stok', exportOptions: { columns: [0,1,2,3,4,5,6] } },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'Laporan Stok', exportOptions: { columns: [0,1,2,3,4,5,6] } },
                    { extend: 'print', text: '🖨 Cetak', title: 'Laporan Stok', exportOptions: { columns: [0,1,2,3,4,5,6] } }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ produk', infoEmpty: 'Tidak ada data', zeroRecords: 'Tidak ditemukan', paginate: { previous: '‹', next: '›' } },
                pageLength: 50,
                order: [[3, 'asc']],
            });
        });
    </script>
    @endpush
</x-layouts.app>

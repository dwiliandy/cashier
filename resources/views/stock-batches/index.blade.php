<x-layouts.app title="Stok Batch">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Manajemen Stok Batch</h2>
            <p class="text-sm text-gray-500">Kelola penambahan stok per batch dengan harga beli</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Form Tambah Batch --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Tambah Batch Baru</h3>
            <form action="{{ route('stock-batches.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                    <select name="product_id" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Pilih produk...</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stok: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                    <input type="number" name="quantity" min="1" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli (per unit)</label>
                    <input type="number" name="purchase_price" min="0" step="0.01" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('purchase_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <input type="text" name="supplier" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kedaluwarsa</label>
                    <input type="date" name="expiry_date" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                </div>
                <button class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-200 transition-all">
                    Tambah Batch
                </button>
            </form>
        </div>

        {{-- Tabel Batch --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <table id="batches-table" class="w-full text-sm stripe hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-left">No. Batch</th>
                            <th class="text-left">Produk</th>
                            <th class="text-right">Qty Awal</th>
                            <th class="text-right">Sisa</th>
                            <th class="text-right">Harga Beli</th>
                            <th class="text-left">Supplier</th>
                            <th class="text-center">Kedaluwarsa</th>
                            <th class="text-left">Dibuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $batch)
                            <tr>
                                <td class="font-mono text-xs text-indigo-600 font-medium">{{ $batch->batch_number }}</td>
                                <td class="font-medium text-gray-900">{{ $batch->product->name }}</td>
                                <td class="text-right text-gray-500">{{ $batch->quantity }}</td>
                                <td class="text-right">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $batch->remaining_quantity <= 0 ? 'bg-red-50 text-red-600' : ($batch->remaining_quantity <= $batch->quantity * 0.2 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                                        {{ $batch->remaining_quantity }}
                                    </span>
                                </td>
                                <td class="text-right text-gray-900 font-semibold">Rp {{ number_format($batch->purchase_price, 0, ',', '.') }}</td>
                                <td class="text-gray-500">{{ $batch->supplier ?? '-' }}</td>
                                <td class="text-center text-xs text-gray-400">{{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-xs text-gray-400">{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <form action="{{ route('stock-batches.destroy', $batch) }}" method="POST" onsubmit="return confirm('Hapus batch ini? Stok produk akan dikurangi sesuai sisa batch.')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>No. Batch</th>
                            <th>Produk</th>
                            <th>Qty Awal</th>
                            <th>Sisa</th>
                            <th>Harga Beli</th>
                            <th>Supplier</th>
                            <th>Kedaluwarsa</th>
                            <th>Dibuat</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#batches-table tfoot th').each(function() {
                var title = $(this).text();
                if (title) $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
            });
            var table = $('#batches-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'Stok Batch', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'Stok Batch', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'print', text: '🖨 Cetak', title: 'Stok Batch', exportOptions: { columns: [0,1,2,3,4,5,6,7] } }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ batch', infoEmpty: 'Tidak ada data', zeroRecords: 'Batch tidak ditemukan', paginate: { first: '«', last: '»', previous: '‹', next: '›' } },
                pageLength: 25,
                order: [[7, 'desc']],
            });
            table.columns().every(function() {
                var that = this;
                $('input', this.footer()).on('keyup change clear', function() {
                    if (that.search() !== this.value) that.search(this.value).draw();
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>

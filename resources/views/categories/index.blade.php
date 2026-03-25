<x-layouts.app title="Kategori">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Kategori Produk</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Tambah Kategori</h3>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <input type="text" name="description" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                <button class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">Simpan</button>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <table id="categories-table" class="w-full text-sm stripe hover" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-left">Nama</th>
                        <th class="text-left">Deskripsi</th>
                        <th class="text-center">Jumlah Produk</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $cat->name }}</td>
                            <td class="text-gray-500">{{ $cat->description ?? '-' }}</td>
                            <td class="text-center"><span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full text-xs font-medium">{{ $cat->products_count }}</span></td>
                            <td class="text-center">
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#categories-table tfoot th').each(function() {
                var title = $(this).text();
                if (title) $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
            });
            var table = $('#categories-table').DataTable({
                language: { search:'Cari:', info:'_TOTAL_ kategori', infoEmpty:'Kosong', zeroRecords:'Tidak ditemukan', paginate:{previous:'‹',next:'›'} },
                pageLength: 25,
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

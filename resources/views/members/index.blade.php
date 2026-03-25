<x-layouts.app title="Member">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Daftar Member</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Tambah Member</h3>
            <form action="{{ route('members.store') }}" method="POST" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">@error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="phone_number" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">@error('phone_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email (opsional)</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                <button class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">Simpan</button>
            </form>
        </div>
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <table id="members-table" class="w-full text-sm stripe hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-left">Nama</th>
                            <th class="text-left">Telepon</th>
                            <th class="text-left">Email</th>
                            <th class="text-right">Poin</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                            <tr>
                                <td class="font-medium text-gray-900">{{ $m->name }}</td>
                                <td class="text-gray-500">{{ $m->phone_number }}</td>
                                <td class="text-gray-400">{{ $m->email ?? '-' }}</td>
                                <td class="text-right"><span class="bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full text-xs font-semibold">{{ number_format($m->points_balance, 0) }} poin</span></td>
                                <td class="text-center">
                                    <a href="{{ route('members.show', $m) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all inline-block" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Poin</th>
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
            $('#members-table tfoot th').each(function() {
                var title = $(this).text();
                if (title) $(this).html('<input type="text" class="dt-column-search" placeholder="Cari ' + title + '..." />');
            });
            var table = $('#members-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Export Excel', title: 'Daftar Member', exportOptions: { columns: [0,1,2,3] } },
                    { extend: 'pdfHtml5', text: '📄 Export PDF', title: 'Daftar Member', exportOptions: { columns: [0,1,2,3] } },
                    { extend: 'print', text: '🖨 Cetak', title: 'Daftar Member', exportOptions: { columns: [0,1,2,3] } }
                ],
                language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ member', infoEmpty: 'Tidak ada data', zeroRecords: 'Member tidak ditemukan', paginate: { first: '«', last: '»', previous: '‹', next: '›' } },
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

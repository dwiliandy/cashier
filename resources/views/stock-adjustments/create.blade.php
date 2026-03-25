<x-layouts.app title="Tambah Penyesuaian Stok">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Tambah Penyesuaian Stok</h2>
            <p class="text-sm text-gray-500">Input penyesuaian stok baru untuk produk tertentu</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form action="{{ route('stock-adjustments.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                    <select name="product_id" required class="w-full select2">
                        <option value="">Pilih produk...</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stok Saat Ini: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Penyesuaian</label>
                        <select name="type" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="in">Penambahan (+)</option>
                            <option value="out">Pengurangan (-)</option>
                            <option value="adjustment">Set Stok (Absolute)</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                        <input type="number" name="quantity" min="1" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                    <select name="reason" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Pilih alasan...</option>
                        <option value="Stock Opname">Stock Opname (Pengecekan Rutin)</option>
                        <option value="Produk Rusak">Produk Rusak</option>
                        <option value="Produk Kedaluwarsa">Produk Kedaluwarsa</option>
                        <option value="Salah Input">Salah Input Sebelumnya</option>
                        <option value="Retur Supplier">Retur ke Supplier</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none" placeholder="Detail tambahan..."></textarea>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <a href="{{ route('stock-adjustments.index') }}" class="flex-1 py-3 rounded-xl text-center text-sm font-semibold text-gray-600 bg-gray-50 border border-gray-200 hover:bg-gray-100 transition-all">Batal</a>
                    <button type="submit" class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-200 transition-all">Simpan Penyesuaian</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

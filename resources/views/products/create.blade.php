<x-layouts.app title="Tambah Produk">
    <div class="max-w-2xl">
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('products.index') }}" class="hover:text-indigo-600">Produk</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('barcode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="category_id" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">— Tanpa Kategori —</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <input type="text" name="unit" value="{{ old('unit', 'pcs') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
                        <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}" required min="0" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                        <input type="number" name="selling_price" value="{{ old('selling_price') }}" required min="0" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                        <input type="number" name="stock" value="{{ old('stock', 0) }}" required min="0" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stok</label>
                        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 5) }}" required min="0" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
                    <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Gambar Produk</label>
                        <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100"></div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-200 transition-all">Simpan Produk</button>
                    <a href="{{ route('products.index') }}" class="px-6 py-3 rounded-xl text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir — Entice POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
    <style>
        body{font-family:'Inter',sans-serif;}
        .product-card:active{transform:scale(0.97);}
        @media print {
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print { position: absolute; top: 0; left: 0; width: 80mm; font-size: 12px; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 h-screen overflow-hidden">
    <div class="flex h-screen">
        {{-- LEFT: Product Grid --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Header --}}
            <div class="bg-white border-b border-gray-200 px-5 py-3 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <a href="{{ in_array(auth()->user()->role, ['admin','owner']) ? route('dashboard') : '#' }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">E</div>
                        <span class="font-bold text-gray-900">Kasir</span>
                    </a>
                    <span class="text-xs text-gray-400">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <div id="sync-status" class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div> Online
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">@csrf
                        <button class="p-2 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Search + Category filter --}}
            <div class="px-5 py-3 bg-white border-b border-gray-200 flex gap-3 shrink-0">
                <div class="flex-1 relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="search-input" placeholder="Cari produk atau scan barcode..."
                           class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" autofocus>
                </div>
                <div class="flex gap-1.5 overflow-x-auto" id="category-filters">
                    <button type="button" data-cat="all" class="cat-btn active shrink-0 px-3 py-2 rounded-xl text-xs font-medium bg-indigo-600 text-white transition-all">Semua</button>
                    @foreach($categories as $cat)
                        <button type="button" data-cat="{{ $cat->id }}" class="cat-btn shrink-0 px-3 py-2 rounded-xl text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="flex-1 overflow-y-auto p-5">
                <div class="grid grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3" id="product-grid">
                    {{-- Populated by JS --}}
                </div>
            </div>
        </div>

        {{-- RIGHT: Cart --}}
        <div class="w-[480px] bg-gray-50 border-l border-gray-200 flex flex-col shrink-0">
            {{-- Member select --}}
            <div class="px-3 py-2 border-b border-gray-200 bg-white shrink-0">
                <div class="relative">
                    <input type="text" id="member-search" placeholder="Cari member (nama/telp)..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-indigo-500 outline-none">
                    <div id="member-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-40 overflow-y-auto"></div>
                </div>
                <div id="selected-member" class="hidden mt-1.5 flex items-center justify-between bg-indigo-50 rounded-lg px-3 py-1.5">
                    <div>
                        <span class="text-sm font-medium text-indigo-700" id="member-name"></span>
                        <span class="text-xs text-indigo-400 ml-2" id="member-points"></span>
                    </div>
                    <button onclick="clearMember()" class="text-indigo-400 hover:text-red-500 text-xs">×</button>
                </div>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto px-3 py-2" id="cart-container">
                <div id="cart-empty" class="text-center py-16">
                    <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <p class="text-sm text-gray-400">Keranjang kosong</p>
                    <p class="text-xs text-gray-300 mt-1">Klik produk untuk menambahkan</p>
                </div>
                <div id="cart-items" class="space-y-1.5"></div>
            </div>

            {{-- Discount & Totals --}}
            <div class="px-3 py-2 border-t border-gray-200 bg-white shrink-0">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gray-500">Diskon (Rp)</label>
                    <input type="number" id="tx-discount" value="0" min="0" class="w-32 border border-gray-300 rounded-lg px-2 py-1 text-right text-sm focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>
                <div class="space-y-1 text-sm bg-gray-50 rounded-lg p-2 border border-gray-100">
                    <div class="flex justify-between text-gray-500 text-xs"><span>Subtotal</span><span id="t-subtotal" class="font-medium text-gray-700">Rp 0</span></div>
                    <div class="flex justify-between text-red-500 text-xs hidden" id="row-discount"><span>Diskon</span><span id="t-discount">-Rp 0</span></div>
                    <div class="flex justify-between text-gray-900 font-bold pt-1 mt-1 border-t border-gray-200 text-base"><span>Total</span><span id="t-total" class="text-indigo-700">Rp 0</span></div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="px-3 py-2 border-t border-gray-200 bg-white shrink-0">
                <div class="grid grid-cols-4 gap-1.5 mb-2">
                    <button type="button" data-method="cash" class="pay-btn active flex flex-col items-center gap-0.5 p-1.5 rounded-lg border-2 border-indigo-500 bg-indigo-50 text-indigo-700 text-[11px] font-bold transition-all shadow-sm">💵<span>Cash</span></button>
                    <button type="button" data-method="transfer" class="pay-btn flex flex-col items-center gap-0.5 p-1.5 rounded-lg border-2 border-gray-200 bg-gray-50 text-gray-600 text-[11px] font-bold transition-all hover:border-gray-300 hover:bg-gray-100">🏦<span>Trf</span></button>
                    <button type="button" data-method="ewallet" class="pay-btn flex flex-col items-center gap-0.5 p-1.5 rounded-lg border-2 border-gray-200 bg-gray-50 text-gray-600 text-[11px] font-bold transition-all hover:border-gray-300 hover:bg-gray-100">📱<span>Q-Pay</span></button>
                    <button type="button" data-method="qris" class="pay-btn flex flex-col items-center gap-0.5 p-1.5 rounded-lg border-2 border-gray-200 bg-gray-50 text-gray-600 text-[11px] font-bold transition-all hover:border-gray-300 hover:bg-gray-100">📷<span>QRIS</span></button>
                </div>
                <div class="flex gap-2 mb-2 items-center relative" id="cash-input-container">
                    <span class="absolute left-3 text-gray-500 font-bold">Rp</span>
                    <input type="number" id="paid-amount" min="0" class="flex-1 w-full border-2 border-gray-300 rounded-lg pl-9 pr-3 py-2 text-base font-bold text-gray-900 focus:border-emerald-500 focus:ring-0 outline-none" placeholder="Jumlah Bayar">
                    <div class="text-right w-1/3 shrink-0" id="change-row" style="display:none">
                        <p class="text-[10px] text-gray-500 font-medium uppercase leading-tight">Kembali</p>
                        <p id="t-change" class="font-bold text-emerald-600 leading-tight">Rp 0</p>
                    </div>
                </div>
                <button id="btn-pay" class="w-full py-3 rounded-lg text-sm font-extrabold tracking-wide text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed uppercase" disabled>
                    🛒 Bayar Transaksi
                </button>
            </div>
        </div>
    </div>

    {{-- Receipt modal --}}
    <div id="receipt-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6" id="receipt-content"></div>
            <div class="px-6 pb-6 flex gap-2">
                <button onclick="printReceipt()" class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">🖨 Cetak Struk</button>
                <button onclick="sendWhatsApp()" id="btn-wa" class="hidden flex-1 py-3 rounded-xl text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-all">📱 WhatsApp</button>
                <button onclick="closeReceipt()" class="px-4 py-3 rounded-xl text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">Tutup</button>
            </div>
        </div>
    </div>
    <div id="receipt-print" class="hidden"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});

        // State
        let products = @json($products);
        let cart = [];
        let selectedMember = null;
        let paymentMethod = 'cash';
        let lastTransaction = null;
        const storeName = @json($storeName);

        // Format currency
        function rp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

        // Render products
        function renderProducts(filter = '', catId = 'all') {
            let filtered = products.filter(p => {
                const matchSearch = !filter || p.name.toLowerCase().includes(filter.toLowerCase()) || (p.barcode && p.barcode === filter) || p.sku.toLowerCase().includes(filter.toLowerCase());
                const matchCat = catId === 'all' || p.category_id == catId;
                return matchSearch && matchCat;
            });

            let html = '';
            filtered.forEach(p => {
                const low = p.stock <= p.minimum_stock;
                html += `<div class="product-card cursor-pointer bg-white rounded-xl border border-gray-200 p-3 hover:shadow-md hover:border-indigo-200 transition-all" onclick="addToCart(${p.id})">
                    <div class="aspect-square rounded-lg bg-gray-50 mb-2 flex items-center justify-center overflow-hidden">
                        ${p.image ? `<img src="/storage/${p.image}" class="w-full h-full object-cover">` : `<svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`}
                    </div>
                    <p class="text-xs font-medium text-gray-900 truncate">${p.name}</p>
                    <p class="text-xs font-bold text-indigo-600 mt-0.5">${rp(p.selling_price)}</p>
                    <p class="text-[10px] mt-0.5 ${low ? 'text-red-500 font-medium' : 'text-gray-400'}">Stok: ${p.stock}</p>
                </div>`;
            });
            if (!html) html = '<div class="col-span-full text-center py-10 text-gray-400 text-sm">Produk tidak ditemukan</div>';
            $('#product-grid').html(html);
        }

        // Add to cart
        function addToCart(productId) {
            const p = products.find(x => x.id === productId);
            if (!p || p.stock <= 0) return;
            const existing = cart.find(x => x.product_id === productId);
            if (existing) {
                if (existing.quantity >= p.stock) return alert('Stok tidak mencukupi!');
                existing.quantity++;
            } else {
                cart.push({ product_id: p.id, name: p.name, price: parseFloat(p.selling_price), quantity: 1, discount: 0, stock: p.stock });
            }
            renderCart();
        }

        // Render cart
        function renderCart() {
            if (cart.length === 0) {
                $('#cart-empty').show(); $('#cart-items').html('');
                updateTotals(); return;
            }
            $('#cart-empty').hide();
            let html = '';
            cart.forEach((item, i) => {
                const subtotal = (item.price * item.quantity) - item.discount;
                html += `<div class="bg-white border text-gray-900 border-gray-200 rounded-xl p-2.5 flex items-center justify-between gap-3 shadow-sm hover:border-indigo-300 transition-all">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate leading-tight">${item.name}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">${rp(item.price)}</p>
                    </div>
                    <div class="flex items-center gap-1.5 bg-gray-50 rounded-lg p-1 border border-gray-200 shrink-0">
                        <button onclick="updateQty(${i},-1)" class="w-7 h-7 rounded bg-white hover:bg-gray-200 flex items-center justify-center font-bold text-lg leading-none shadow-sm text-gray-600 transition-all">−</button>
                        <span class="w-6 text-center text-sm font-extrabold">${item.quantity}</span>
                        <button onclick="updateQty(${i},1)" class="w-7 h-7 rounded bg-white hover:bg-gray-200 flex items-center justify-center font-bold text-lg leading-none shadow-sm text-gray-600 transition-all">+</button>
                    </div>
                    <div class="text-right w-20 shrink-0">
                        <p class="text-sm font-bold text-indigo-600 leading-tight">${rp(subtotal)}</p>
                    </div>
                    <button onclick="removeFromCart(${i})" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shrink-0 shadow-sm border border-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>`;
            });
            $('#cart-items').html(html);
            updateTotals();
        }

        function updateQty(index, delta) {
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) cart.splice(index, 1);
            else if (cart[index].quantity > cart[index].stock) { cart[index].quantity = cart[index].stock; alert('Stok tidak mencukupi!'); }
            renderCart();
        }

        function removeFromCart(index) { cart.splice(index, 1); renderCart(); }

        function updateTotals() {
            let subtotal = cart.reduce((sum, i) => sum + (i.price * i.quantity) - i.discount, 0);
            let discount = parseFloat($('#tx-discount').val()) || 0;
            let total = Math.max(0, subtotal - discount);

            $('#t-subtotal').text(rp(subtotal));
            if (discount > 0) { $('#row-discount').show(); $('#t-discount').text('-' + rp(discount)); } else { $('#row-discount').hide(); }
            $('#t-total').text(rp(total));

            if (paymentMethod !== 'cash') {
                $('#paid-amount').val(total);
            }
            
            let paid = parseFloat($('#paid-amount').val()) || 0;
            
            if (paymentMethod === 'cash') {
                if (paid > 0 && paid >= total) { $('#change-row').show(); $('#t-change').text(rp(paid - total)); } else { $('#change-row').hide(); }
                $('#btn-pay').prop('disabled', cart.length === 0 || paid < total);
            } else {
                $('#change-row').hide();
                $('#btn-pay').prop('disabled', cart.length === 0 || total === 0);
            }
        }

        // Payment method
        $('.pay-btn').click(function() {
            paymentMethod = $(this).data('method');
            $('.pay-btn').removeClass('active border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm').addClass('border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100');
            $(this).addClass('active border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm').removeClass('border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100');
            
            if (paymentMethod !== 'cash') {
                $('#cash-input-container').hide();
            } else {
                $('#cash-input-container').show();
            }
            updateTotals();
        });

        // Member search
        let memberTimeout;
        $('#member-search').on('input', function() {
            clearTimeout(memberTimeout);
            const q = $(this).val();
            if (q.length < 2) { $('#member-dropdown').hide(); return; }
            memberTimeout = setTimeout(() => {
                $.get('/api/members/search', {q}, function(data) {
                    if (data.length === 0) { $('#member-dropdown').html('<div class="p-3 text-xs text-gray-400">Tidak ditemukan</div>').show(); return; }
                    let html = '';
                    data.forEach(m => { html += `<div class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm" onclick="selectMember(${m.id},'${m.name}','${m.phone_number}',${m.points_balance})">${m.name} <span class="text-gray-400">${m.phone_number}</span></div>`; });
                    $('#member-dropdown').html(html).show();
                });
            }, 300);
        });

        function selectMember(id, name, phone, points) {
            selectedMember = {id, name, phone, points};
            $('#member-name').text(name);
            $('#member-points').text(points + ' poin');
            $('#selected-member').show().removeClass('hidden');
            $('#member-search').val('');
            $('#member-dropdown').hide();
        }
        function clearMember() { selectedMember = null; $('#selected-member').hide().addClass('hidden'); }

        // Search & filter
        $('#search-input').on('input', function() { renderProducts($(this).val(), $('.cat-btn.active').data('cat')); });
        $(document).on('click', '.cat-btn', function() {
            $('.cat-btn').removeClass('active bg-indigo-600 text-white').addClass('bg-gray-100 text-gray-600');
            $(this).addClass('active bg-indigo-600 text-white').removeClass('bg-gray-100 text-gray-600');
            renderProducts($('#search-input').val(), $(this).data('cat'));
        });
        $('#tx-discount, #paid-amount').on('input', updateTotals);

        // Process payment
        $('#btn-pay').click(function() {
            if (cart.length === 0) return;
            const subtotal = cart.reduce((s, i) => s + (i.price * i.quantity) - i.discount, 0);
            const discount = parseFloat($('#tx-discount').val()) || 0;
            const total = Math.max(0, subtotal - discount);
            const paid = parseFloat($('#paid-amount').val()) || 0;
            if (paid < total) return alert('Jumlah bayar kurang!');

            const data = {
                items: cart.map(i => ({product_id: i.product_id, name: i.name, price: i.price, quantity: i.quantity, discount: i.discount})),
                paid_amount: paid,
                payment_method: paymentMethod,
                discount: discount,
                tax: 0,
                member_id: selectedMember?.id || null,
                local_id: 'local_' + Date.now(),
            };

            $(this).prop('disabled', true).text('Memproses...');

            if (!navigator.onLine) {
                saveOffline(data);
                showReceipt(buildOfflineReceipt(data));
                resetCart();
                return;
            }

            $.post('/pos/transaction', data)
                .done(function(res) {
                    if (res.success) {
                        lastTransaction = res.transaction;
                        // Update local stock
                        cart.forEach(item => {
                            let p = products.find(x => x.id === item.product_id);
                            if (p) p.stock -= item.quantity;
                        });

                        if (res.snap_token) {
                            window.snap.pay(res.snap_token, {
                                onSuccess: function(result) {
                                    res.transaction.payment_status = 'paid';
                                    showReceipt(buildReceipt(res.transaction));
                                    resetCart();
                                },
                                onPending: function(result) {
                                    alert('Pembayaran tertunda. Selesaikan via Midtrans.');
                                    resetCart();
                                },
                                onError: function(result) {
                                    alert('Pembayaran gagal.');
                                    resetCart();
                                },
                                onClose: function() {
                                    alert('Pop-up ditutup. Transaksi tersimpan (Pending) dan bisa dilanjutkan nanti oleh pelanggan.');
                                    resetCart();
                                }
                            });
                        } else {
                            showReceipt(buildReceipt(res.transaction));
                            resetCart();
                        }
                    } else { alert(res.message || 'Gagal memproses transaksi'); }
                })
                .fail(function(xhr) {
                    if (!navigator.onLine) { saveOffline(data); showReceipt(buildOfflineReceipt(data)); resetCart(); }
                    else alert(xhr.responseJSON?.message || 'Terjadi kesalahan');
                })
                .always(function() { $('#btn-pay').prop('disabled', false).text('BAYAR'); });
        });

        function resetCart() {
            cart = []; selectedMember = null;
            clearMember();
            $('#tx-discount').val(0); $('#paid-amount').val('');
            renderCart(); renderProducts();
            $('#btn-pay').prop('disabled', true).text('BAYAR');
        }

        // Receipt
        function buildReceipt(tx) {
            let itemsHtml = '';
            tx.items.forEach(i => {
                itemsHtml += `<div class="flex justify-between py-1 text-xs"><div class="flex-1">${i.product_name} x${i.quantity}</div><div>${rp(i.subtotal)}</div></div>`;
            });
            const pointsEarned = tx.member_points?.filter(p => p.type === 'earn').reduce((s,p) => s + parseFloat(p.points), 0) || 0;
            return `<div class="text-center mb-4"><h2 class="font-bold text-lg">${storeName}</h2><p class="text-xs text-gray-400">${tx.invoice_number}</p><p class="text-xs text-gray-400">${new Date(tx.created_at).toLocaleString('id-ID')}</p><p class="text-xs text-gray-400">Kasir: ${tx.user?.name || '-'}</p></div>
                <div class="border-t border-dashed border-gray-300 py-2">${itemsHtml}</div>
                <div class="border-t border-dashed border-gray-300 py-2 space-y-1 text-xs">
                    <div class="flex justify-between"><span>Subtotal</span><span>${rp(tx.subtotal)}</span></div>
                    ${tx.discount > 0 ? `<div class="flex justify-between text-red-500"><span>Diskon</span><span>-${rp(tx.discount)}</span></div>` : ''}
                    <div class="flex justify-between font-bold text-sm"><span>Total</span><span>${rp(tx.total)}</span></div>
                    <div class="flex justify-between"><span>Bayar (${tx.payment_method.toUpperCase()})</span><span>${rp(tx.paid_amount)}</span></div>
                    <div class="flex justify-between"><span>Kembalian</span><span>${rp(tx.change_amount)}</span></div>
                </div>
                ${tx.member ? `<div class="border-t border-dashed border-gray-300 pt-2 text-xs text-center"><p>Member: ${tx.member.name}</p>${pointsEarned > 0 ? `<p class="text-emerald-600 font-semibold">+${pointsEarned} poin</p>` : ''}</div>` : ''}
                <div class="text-center mt-4 text-xs text-gray-400"><p>Terima kasih atas kunjungan Anda!</p></div>`;
        }

        function buildOfflineReceipt(data) {
            let itemsHtml = '';
            data.items.forEach(i => { itemsHtml += `<div class="flex justify-between py-1 text-xs"><div>${i.name} x${i.quantity}</div><div>${rp(i.price * i.quantity)}</div></div>`; });
            const total = data.items.reduce((s,i) => s + i.price * i.quantity, 0) - (data.discount || 0);
            return `<div class="text-center mb-4"><h2 class="font-bold text-lg">${storeName}</h2><p class="text-xs text-amber-500 font-medium">⚡ OFFLINE MODE</p><p class="text-xs text-gray-400">${new Date().toLocaleString('id-ID')}</p></div>
                <div class="border-t border-dashed border-gray-300 py-2">${itemsHtml}</div>
                <div class="border-t border-dashed border-gray-300 py-2 text-xs"><div class="flex justify-between font-bold text-sm"><span>Total</span><span>${rp(total)}</span></div></div>
                <div class="text-center mt-4 text-xs text-amber-600">Transaksi akan disinkronkan saat online</div>`;
        }

        function showReceipt(html) {
            $('#receipt-content').html(html);
            $('#receipt-modal').removeClass('hidden');
            if (selectedMember?.phone) { $('#btn-wa').show().removeClass('hidden'); } else { $('#btn-wa').hide(); }
        }
        function closeReceipt() { $('#receipt-modal').addClass('hidden'); }
        function printReceipt() {
            $('#receipt-print').html($('#receipt-content').html()).show();
            window.print();
            $('#receipt-print').hide();
        }

        // WhatsApp
        function sendWhatsApp() {
            if (!lastTransaction || !selectedMember?.phone) return;
            const tx = lastTransaction;
            const msg = `*${storeName}*\nNo: ${tx.invoice_number}\nTotal: ${rp(tx.total)}\nTanggal: ${new Date(tx.created_at).toLocaleString('id-ID')}\n\nTerima kasih atas kunjungan Anda! 🙏`;
            const phone = selectedMember.phone.replace(/^0/, '62');
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
        }

        // Offline sync
        function saveOffline(data) {
            let pending = JSON.parse(localStorage.getItem('pendingTransactions') || '[]');
            pending.push(data);
            localStorage.setItem('pendingTransactions', JSON.stringify(pending));
            updateSyncStatus();
        }

        function syncPending() {
            if (!navigator.onLine) return;
            let pending = JSON.parse(localStorage.getItem('pendingTransactions') || '[]');
            if (pending.length === 0) return;

            $('#sync-status').html('<div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div> Sinkronisasi...')
                .removeClass('bg-emerald-50 text-emerald-600 bg-red-50 text-red-600').addClass('bg-amber-50 text-amber-600');

            $.post('/pos/sync', {transactions: pending})
                .done(function(res) {
                    let remaining = [];
                    res.results.forEach((r, i) => { if (!r.success) remaining.push(pending[i]); });
                    localStorage.setItem('pendingTransactions', JSON.stringify(remaining));
                    updateSyncStatus();
                })
                .fail(function() { updateSyncStatus(); });
        }

        function updateSyncStatus() {
            let pending = JSON.parse(localStorage.getItem('pendingTransactions') || '[]');
            const el = $('#sync-status');
            if (!navigator.onLine) {
                el.html('<div class="w-2 h-2 rounded-full bg-red-500"></div> Offline' + (pending.length ? ` (${pending.length})` : ''))
                    .removeClass('bg-emerald-50 text-emerald-600 bg-amber-50 text-amber-600').addClass('bg-red-50 text-red-600');
            } else if (pending.length > 0) {
                el.html(`<div class="w-2 h-2 rounded-full bg-amber-500"></div> ${pending.length} belum sync`)
                    .removeClass('bg-emerald-50 text-emerald-600 bg-red-50 text-red-600').addClass('bg-amber-50 text-amber-600');
            } else {
                el.html('<div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div> Online')
                    .removeClass('bg-red-50 text-red-600 bg-amber-50 text-amber-600').addClass('bg-emerald-50 text-emerald-600');
            }
        }

        window.addEventListener('online', () => { updateSyncStatus(); syncPending(); });
        window.addEventListener('offline', updateSyncStatus);

        // Init
        renderProducts();
        updateSyncStatus();
        setInterval(syncPending, 30000);
    </script>
</body>
</html>

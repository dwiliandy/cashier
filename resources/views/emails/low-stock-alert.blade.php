<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { color: #dc2626; font-size: 20px; margin: 0; }
        .header p { color: #6b7280; font-size: 14px; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f9fafb; padding: 10px 12px; text-align: left; font-size: 12px; color: #6b7280; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #374151; }
        .badge-low { background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-out { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .footer { text-align: center; margin-top: 24px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>⚠ Peringatan Stok Rendah</h1>
                <p>Berikut produk yang stoknya hampir habis atau sudah habis:</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th style="text-align:center">Stok</th>
                        <th style="text-align:center">Min. Stok</th>
                        <th style="text-align:center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                            <td style="text-align:center"><strong>{{ $product->stock }}</strong></td>
                            <td style="text-align:center">{{ $product->minimum_stock }}</td>
                            <td style="text-align:center">
                                @if($product->stock <= 0)
                                    <span class="badge-out">Habis</span>
                                @else
                                    <span class="badge-low">Rendah</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="footer">
                <p>Email otomatis dari Entice POS — {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</body>
</html>

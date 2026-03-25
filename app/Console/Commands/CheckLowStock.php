<?php
namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Services\StockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check-low';
    protected $description = 'Cek produk dengan stok rendah dan kirim email notifikasi ke owner';

    public function __construct(private StockService $stockService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $lowStockProducts = $this->stockService->getLowStockProducts();

        if ($lowStockProducts->isEmpty()) {
            $this->info('✅ Tidak ada produk dengan stok rendah.');
            return Command::SUCCESS;
        }

        $this->warn("⚠ Ditemukan {$lowStockProducts->count()} produk dengan stok rendah:");
        foreach ($lowStockProducts as $product) {
            $this->line("  - {$product->name}: {$product->stock} (min: {$product->minimum_stock})");
        }

        $ownerEmail = config('app.owner_email');
        if (!$ownerEmail) {
            $this->error('❌ OWNER_EMAIL belum dikonfigurasi di .env');
            return Command::FAILURE;
        }

        Mail::to($ownerEmail)->send(new LowStockAlert($lowStockProducts));
        $this->info("📧 Email notifikasi dikirim ke {$ownerEmail}");

        return Command::SUCCESS;
    }
}

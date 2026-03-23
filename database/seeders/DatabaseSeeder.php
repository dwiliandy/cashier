<?php
namespace Database\Seeders;
use App\Models\Category;
use App\Models\Member;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::create(['name' => 'Admin', 'email' => 'admin@entice.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        User::create(['name' => 'Kasir 1', 'email' => 'kasir@entice.com', 'password' => bcrypt('password'), 'role' => 'cashier']);
        User::create(['name' => 'Owner', 'email' => 'owner@entice.com', 'password' => bcrypt('password'), 'role' => 'owner']);

        // Categories
        $food = Category::create(['name' => 'Makanan', 'description' => 'Produk makanan']);
        $drink = Category::create(['name' => 'Minuman', 'description' => 'Produk minuman']);
        $snack = Category::create(['name' => 'Snack', 'description' => 'Camilan dan snack']);
        $daily = Category::create(['name' => 'Kebutuhan Harian', 'description' => 'Produk kebutuhan sehari-hari']);

        // Products
        $items = [
            ['name'=>'Nasi Goreng','sku'=>'MKN-001','barcode'=>'8991234567890','category_id'=>$food->id,'purchase_price'=>12000,'selling_price'=>18000,'stock'=>100,'unit'=>'porsi'],
            ['name'=>'Mie Goreng','sku'=>'MKN-002','barcode'=>'8991234567891','category_id'=>$food->id,'purchase_price'=>10000,'selling_price'=>15000,'stock'=>80,'unit'=>'porsi'],
            ['name'=>'Ayam Goreng','sku'=>'MKN-003','barcode'=>'8991234567892','category_id'=>$food->id,'purchase_price'=>15000,'selling_price'=>25000,'stock'=>50,'unit'=>'porsi'],
            ['name'=>'Es Teh Manis','sku'=>'MNM-001','barcode'=>'8991234567893','category_id'=>$drink->id,'purchase_price'=>2000,'selling_price'=>5000,'stock'=>200,'unit'=>'gelas'],
            ['name'=>'Kopi Hitam','sku'=>'MNM-002','barcode'=>'8991234567894','category_id'=>$drink->id,'purchase_price'=>3000,'selling_price'=>8000,'stock'=>150,'unit'=>'gelas'],
            ['name'=>'Jus Jeruk','sku'=>'MNM-003','barcode'=>'8991234567895','category_id'=>$drink->id,'purchase_price'=>5000,'selling_price'=>12000,'stock'=>60,'unit'=>'gelas'],
            ['name'=>'Air Mineral','sku'=>'MNM-004','barcode'=>'8991234567896','category_id'=>$drink->id,'purchase_price'=>1500,'selling_price'=>4000,'stock'=>300,'unit'=>'botol'],
            ['name'=>'Keripik Singkong','sku'=>'SNK-001','barcode'=>'8991234567897','category_id'=>$snack->id,'purchase_price'=>5000,'selling_price'=>10000,'stock'=>75,'unit'=>'bungkus'],
            ['name'=>'Kacang Goreng','sku'=>'SNK-002','barcode'=>'8991234567898','category_id'=>$snack->id,'purchase_price'=>4000,'selling_price'=>8000,'stock'=>90,'unit'=>'bungkus'],
            ['name'=>'Roti Bakar','sku'=>'SNK-003','barcode'=>'8991234567899','category_id'=>$snack->id,'purchase_price'=>6000,'selling_price'=>12000,'stock'=>40,'unit'=>'porsi'],
            ['name'=>'Sabun Mandi','sku'=>'HRN-001','barcode'=>'8991234567900','category_id'=>$daily->id,'purchase_price'=>4000,'selling_price'=>8000,'stock'=>120,'unit'=>'pcs'],
            ['name'=>'Tisu','sku'=>'HRN-002','barcode'=>'8991234567901','category_id'=>$daily->id,'purchase_price'=>3000,'selling_price'=>6000,'stock'=>3,'minimum_stock'=>10,'unit'=>'pack'],
            ['name'=>'Sikat Gigi','sku'=>'HRN-003','barcode'=>'8991234567902','category_id'=>$daily->id,'purchase_price'=>5000,'selling_price'=>10000,'stock'=>45,'unit'=>'pcs'],
        ];
        foreach ($items as $item) { Product::create($item); }

        // Members
        Member::create(['name'=>'Budi Santoso','phone_number'=>'081234567890','email'=>'budi@email.com','points_balance'=>50]);
        Member::create(['name'=>'Siti Aminah','phone_number'=>'081234567891','email'=>'siti@email.com','points_balance'=>120]);
        Member::create(['name'=>'Ahmad Hidayat','phone_number'=>'081234567892','points_balance'=>0]);

        // Settings
        Setting::set('store_name', 'Entice POS');
        Setting::set('points_per_rupiah', '0.01');
        Setting::set('tax_percentage', '0');
        Setting::set('store_address', 'Jl. Contoh No. 123');
        Setting::set('store_phone', '021-1234567');
    }
}

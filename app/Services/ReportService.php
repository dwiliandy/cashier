<?php
namespace App\Services;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDailySales(?string $date = null): float
    {
        $date = $date ?? today()->toDateString();
        return Transaction::where('payment_status', 'paid')->whereDate('created_at', $date)->sum('total');
    }

    public function getMonthlySales(?string $month = null): float
    {
        $month = $month ?? now()->format('Y-m');
        return Transaction::where('payment_status', 'paid')
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])->sum('total');
    }

    public function getSalesReport(string $from, string $to)
    {
        return Transaction::with('user', 'member')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()->get();
    }

    public function getSalesByProduct(string $from, string $to)
    {
        return DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.payment_status', 'paid')
            ->whereBetween('transactions.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select('transaction_items.product_name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'))
            ->groupBy('transaction_items.product_name')
            ->orderByDesc('total_qty')
            ->get();
    }

    public function getTopProducts(int $limit = 10)
    {
        return DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.payment_status', 'paid')
            ->select('transaction_items.product_name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'))
            ->groupBy('transaction_items.product_name')
            ->orderByDesc('total_qty')
            ->limit($limit)->get();
    }

    public function getDashboardStats(): array
    {
        return [
            'daily_sales' => $this->getDailySales(),
            'monthly_sales' => $this->getMonthlySales(),
            'total_transactions_today' => Transaction::where('payment_status', 'paid')->whereDate('created_at', today())->count(),
            'total_transactions_month' => Transaction::where('payment_status', 'paid')->whereMonth('created_at', now()->month)->count(),
            'avg_transaction' => Transaction::where('payment_status', 'paid')->whereMonth('created_at', now()->month)->avg('total') ?? 0,
            'total_products' => Product::count(),
            'low_stock_count' => Product::active()->lowStock()->count(),
            'total_members' => Member::count(),
        ];
    }

    public function getSalesChart(int $days = 7): array
    {
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            $data[] = (float) Transaction::where('payment_status', 'paid')->whereDate('created_at', $date)->sum('total');
        }
        return compact('labels', 'data');
    }
}

<?php
namespace App\Http\Controllers;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function sales(Request $request)
    {
        $from = $request->from ?? today()->startOfMonth()->toDateString();
        $to = $request->to ?? today()->toDateString();
        $transactions = $this->reportService->getSalesReport($from, $to);
        $salesByProduct = $this->reportService->getSalesByProduct($from, $to);
        return view('reports.sales', compact('transactions', 'salesByProduct', 'from', 'to'));
    }

    public function stock()
    {
        $products = \App\Models\Product::with('category')->orderBy('stock')->get();
        $lowStock = $products->filter(fn($p) => $p->isLowStock());
        $categories = \App\Models\Category::active()->get();
        return view('reports.stock', compact('products', 'lowStock', 'categories'));
    }
}

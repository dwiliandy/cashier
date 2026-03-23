<?php
namespace App\Http\Controllers;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        $stats = $this->reportService->getDashboardStats();
        $salesChart = $this->reportService->getSalesChart(7);
        $topProducts = $this->reportService->getTopProducts(5);
        return view('dashboard', compact('stats', 'salesChart', 'topProducts'));
    }
}

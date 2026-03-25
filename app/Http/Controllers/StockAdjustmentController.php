<?php

namespace App\Http\Controllers;

use App\Services\StockAdjustmentService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private StockAdjustmentService $adjustmentService,
        private ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $adjustments = $this->adjustmentService->getHistory(
            $request->from,
            $request->to,
            $request->type,
            $request->product_id
        );
        $products = $this->productService->getAll();
        
        return view('stock-adjustments.index', compact('adjustments', 'products'));
    }

    public function create()
    {
        $products = $this->productService->getAll();
        return view('stock-adjustments.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $this->adjustmentService->create($data);

        return redirect()->route('stock-adjustments.index')->with('success', 'Stok berhasil disesuaikan!');
    }

    public function report(Request $request)
    {
        $from = $request->from ?? today()->startOfMonth()->toDateString();
        $to = $request->to ?? today()->toDateString();
        
        $adjustments = $this->adjustmentService->getHistory($from, $to, $request->type, $request->product_id);
        $products = $this->productService->getAll();

        return view('stock-adjustments.report', compact('adjustments', 'products', 'from', 'to'));
    }
}

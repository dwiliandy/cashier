<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StockBatchController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard (admin, owner)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('role:admin,owner');

    // Products management (admin, owner)
    Route::middleware('role:admin,owner')->group(function () {
        Route::resource('products', ProductController::class)->except('show');
        Route::post('products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');
        Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
        Route::resource('stock-batches', StockBatchController::class)->except(['show', 'create', 'edit']);
    });

    // Members management (admin, owner, cashier)
    Route::resource('members', MemberController::class)->except(['create', 'edit']);

    // Transactions (admin, owner)
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Reports (admin, owner)
    Route::middleware('role:admin,owner')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
    });

    // Activity Logs (owner only)
    Route::middleware('role:owner')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // POS (cashier, admin)
    Route::middleware('role:cashier,admin')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/transaction', [PosController::class, 'processTransaction'])->name('pos.transaction');
        Route::post('/pos/sync', [PosController::class, 'syncTransactions'])->name('pos.sync');
    });

    // API endpoints (for AJAX)
    Route::prefix('api')->group(function () {
        Route::get('/products/search', [ProductController::class, 'apiSearch'])->name('api.products.search');
        Route::get('/products/all', [ProductController::class, 'apiAll'])->name('api.products.all');
        Route::get('/members/search', [MemberController::class, 'apiSearch'])->name('api.members.search');
        Route::get('/members/all', [MemberController::class, 'apiAll'])->name('api.members.all');
        
        // Midtrans Webhook
        Route::post('/midtrans/webhook', [\App\Http\Controllers\WebhookController::class, 'midtrans'])->name('api.midtrans.webhook');
    });
});

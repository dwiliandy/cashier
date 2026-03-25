<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\ProductRepositoryInterface::class,
            \App\Repositories\ProductRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\CategoryRepositoryInterface::class,
            \App\Repositories\CategoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\MemberRepositoryInterface::class,
            \App\Repositories\MemberRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\StockBatchRepositoryInterface::class,
            \App\Repositories\StockBatchRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ActivityLogRepositoryInterface::class,
            \App\Repositories\ActivityLogRepository::class
        );
    }
}

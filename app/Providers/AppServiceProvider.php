<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        // Paginator::useBootstrap();

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // DB::listen(function ($query) {
        //     File::append(
        //         storage_path('/logs/query' . date('-Y-m-d') . '.log'),
        //         '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL
        //     );
        // });

    }
}

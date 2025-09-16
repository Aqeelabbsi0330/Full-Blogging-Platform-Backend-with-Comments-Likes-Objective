<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Disable foreign key checks before migrations
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

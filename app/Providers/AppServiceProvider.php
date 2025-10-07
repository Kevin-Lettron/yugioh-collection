<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\View\Components\AppLayout;

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
    // Enregistre le composant app-layout
    \Illuminate\Support\Facades\Blade::component('app-layout', \App\View\Components\AppLayout::class);

    // ✅ Forcer l'utilisation de la pagination Tailwind (compatible Breeze)
    Paginator::useTailwind();
}
}

<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        //
        // if(\config('app.env') === 'local'){
        //     URL::forceScheme('https');
        //     request()->server->set('HTTPS', request()->header('X-Forwarded-Proto', 'https') == 'https' ? 'on' : 'off');
        // }
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => '<meta name="mobile-web-app-capable" content="yes">'
        );
    }
}

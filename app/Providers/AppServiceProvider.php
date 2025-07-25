<?php

namespace App\Providers;

use App\Interfaces\SMSProviderInterface;
use App\Services\OrangeSMSService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        
        $this->configureRoutes();
    }
    
    protected function configureRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
<?php

namespace App\Providers;

use App\Services\CloudPaymentsService;
use App\Services\GitService;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServices();
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(LC_ALL, 'ru_RU.utf8');
        date_default_timezone_set(config('app.timezone'));
        Carbon::setLocale(config('app.locale'));
    }

    private function registerServices()
    {
        $this->app->singleton(CloudPaymentsService::class);
        $this->app->singleton(GitService::class);
    }
}

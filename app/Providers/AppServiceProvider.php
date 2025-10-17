<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use App\Models\WorkOrder;
use App\Policies\WorkOrderPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\CalculationService::class);
        $this->app->singleton(\App\Services\HiddenFieldService::class);
        $this->app->singleton(\App\Services\ApprovalService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //force https
        // URL::forceHttps(); // Commented out for local development
        
        //Paginator
        //Paginator::useBootstrapFive();
        Paginator::defaultView('layouts.pagination');

        // Register policies
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}

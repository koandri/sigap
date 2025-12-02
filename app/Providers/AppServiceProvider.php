<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use App\Models\WorkOrder;
use App\Models\DocumentInstance;
use App\Models\DocumentBorrow;
use App\Policies\WorkOrderPolicy;
use App\Policies\DocumentInstancePolicy;
use App\Policies\DocumentBorrowPolicy;
use Illuminate\Support\Facades\Event;


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
        if (env('APP_FORCE_HTTPS')) {
            URL::forceHttps();
        }
        
        //Paginator
        //Paginator::useBootstrapFive();
        Paginator::defaultView('layouts.pagination');

        // Register policies
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
        Gate::policy(DocumentInstance::class, DocumentInstancePolicy::class);
        Gate::policy(DocumentBorrow::class, DocumentBorrowPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('keycloak', \SocialiteProviders\Keycloak\Provider::class);
        });
    }
}

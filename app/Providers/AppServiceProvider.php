<?php

namespace App\Providers;

use App\Events\IdeaApproved;
use App\Events\IdeaCreated;
use App\Listeners\IntegrationNotificationListener;
use Illuminate\Support\Facades\Event;
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
        // Register integration notification listeners
        Event::listen(IdeaCreated::class, [IntegrationNotificationListener::class, 'handleIdeaCreated']);
        Event::listen(IdeaApproved::class, [IntegrationNotificationListener::class, 'handleIdeaApproved']);
    }
}

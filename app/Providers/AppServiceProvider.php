<?php

namespace Knowfox\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Knowfox\Models\Concept;
use Knowfox\Observers\ConceptObserver;
use Knowfox\ViewComposers\ImpactMapComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Concept::observe(ConceptObserver::class);
        View::composer('concept.show-impact-map', ImpactMapComposer::class);

        // Because mpociot/versionable does not specify it
        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/mpociot/versionable/src/migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
    }
}

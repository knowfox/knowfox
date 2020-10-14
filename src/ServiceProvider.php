<?php

namespace Knowfox;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Routing\Middleware\SubstituteBindings;

//use Laravel\Passport\Passport;

use Knowfox\Models\Concept;
use Knowfox\Observers\ConceptObserver;
use Knowfox\Policies\ConceptPolicy;
use Knowfox\Models\Item;
use Knowfox\Observers\ItemObserver;

use Knowfox\ViewComposers\AlphaIndexComposer;
use Knowfox\ViewComposers\ImpactMapComposer;

class ServiceProvider extends IlluminateServiceProvider
{
    protected $namespace = '\Knowfox\Http\Controllers';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/knowfox.php', 'knowfox'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Concept::observe(ConceptObserver::class);
        Item::observe(ItemObserver::class);
        View::composer('knowfox::concept.show-impact-map', ImpactMapComposer::class);
        View::composer('knowfox::partials.alpha-nav', AlphaIndexComposer::class);

        Gate::policy(Concept::class, ConceptPolicy::class);

        // Because mpociot/versionable does not specify it
        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/mpociot/versionable/src/migrations');

        //Route::model('concept', Concept::class);

        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/web.php');

        //Passport::routes();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'knowfox');

        $this->publishes([
            __DIR__ . '/../config/knowfox.php' => config_path('knowfox.php'),
        ]);
    }
}

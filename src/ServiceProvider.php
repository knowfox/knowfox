<?php

namespace Knowfox;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Barryvdh\Cors\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Livewire\Livewire;

use Knowfox\Core\Models\Concept;
use Knowfox\Core\Policies\ConceptPolicy;

use Knowfox\Frontend\ViewComposers\AlphaIndexComposer;
use Knowfox\Frontend\ViewComposers\ImpactMapComposer;
use Knowfox\Frontend\Livewire\Children;

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
        View::composer('concept.show-impact-map', ImpactMapComposer::class);
        View::composer('partials.alpha-nav', AlphaIndexComposer::class);

        Livewire::component('children', Children::class);

        //Route::model('concept', Concept::class);

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'knowfox');

        $this->publishes([
            __DIR__ . '/../config/knowfox.php' => config_path('knowfox.php'),
        ]);
    }
}

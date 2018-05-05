<?php

namespace Knowfox\Drupal7;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Knowfox\Drupal7\Commands\ImportDrupal7;

class ServiceProvider extends IlluminateServiceProvider
{
    protected function mergeConfigRecursiveFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);
        $this->app['config']->set($key, array_merge_recursive(require $path, $config));
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportDrupal7::class,
            ]);
        }
    }

    public function register()
    {
    }
}
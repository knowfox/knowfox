<?php

namespace Knowfox\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Knowfox\Models\Concept;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfiguration();
    }

    private function mergeConfiguration()
    {
        $config = Concept::whereIsRoot()
            ->where('title', 'Configuration')
            ->first();

        if ($config) {
            foreach (config('knowfox') as $name => $value) {
                if (!empty($config->config->{$name})) {
                    Config::set('knowfox.' . $name,
                        array_merge_recursive($config->config->{$name}, $value)
                    );
                }
            }
        }
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

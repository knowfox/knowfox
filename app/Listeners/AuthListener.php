<?php

namespace Knowfox\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Knowfox\Models\Concept;
use Illuminate\Support\Facades\Config;

class AuthListener
{
    private function mergeConfiguration($user_id)
    {
        $config = Concept::whereIsRoot()
            ->where('title', 'Configuration')
            ->where('owner_id', $user_id)
            ->first();

        if (!$config) {
            return;
        }

        foreach (config('knowfox') as $name => $value) {
            if (!empty($config->config->{$name})) {
                Config::set('knowfox.' . $name,
                    array_merge_recursive($config->config->{$name}, $value)
                );
            }
        }
    }

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Authenticated  $event
     * @return void
     */
    public function handle(Authenticated $event)
    {
        $this->mergeConfiguration($event->user->id);
    }
}

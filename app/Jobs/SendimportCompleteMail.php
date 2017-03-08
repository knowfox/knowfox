<?php

namespace Knowfox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

use Knowfox\User;
use Symfony\Component\Yaml\Yaml;

class SendimportCompleteMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $notebook_name;
    protected $info;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $notebook_name, $info)
    {
        $this->user = $user;
        $this->notebook_name = $notebook_name;
        $this->info = $info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        $notebook_name = $this->notebook_name;
        $info = $this->info;

        Mail::send('email.import-complete', [
            'user' => $user,
            'notebook_name' => $notebook_name,
            'info' => Yaml::dump($info),
        ], function ($m) use ($user) {
            $m->from('hello@post.knowfox.com', 'Knowfox');
            $m->to($user->email)->subject('Import for ' . $user->name . ' complete');
        });
    }
}

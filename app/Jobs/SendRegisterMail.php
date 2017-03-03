<?php

namespace Knowfox\Jobs;

use Knowfox\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendRegisterMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;

        Mail::send('email.getting-started', [
            'user' => $user,
        ], function ($m) use ($user) {
            $m->from('hello@post.knowfox.com', 'Knowfox');
            $m->to($user->email, $user->name)->subject('Hello ' . $user->name . ', get started with Knowfox!');
        });
    }
}

<?php

namespace Knowfox\Jobs;

use Knowfox\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendLoginMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $user;
    private $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        $url = $this->url;

        Mail::send('auth.emails.email-login', [
            'user' => $user,
            'url' => $url,
        ], function ($m) use ($user) {
            $m->from('hello@post.knowfox.com', 'Knowfox');
            $m->to($user->email)->subject('Hello ' . $user->name . ', here your login link to Knowfox!');
        });
    }
}

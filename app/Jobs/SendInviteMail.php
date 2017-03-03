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
    public function __construct($owner, $user, $concept, $url)
    {
        $this->owner = $user;
        $this->user = $user;
        $this->concept = $concept;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $owner = $this->owner;
        $user = $this->user;
        $concept = $this->concept;
        $url = $this->url;

        Mail::send('auth.emails.email-login', [
            'owner' => $owner,
            'user' => $user,
            'concept' => $concept,
            'url' => $url,
        ], function ($m) use ($owner, $user) {
            $m->from('hello@post.knowfox.com', $owner->name);
            $m->to($user->email)->subject('I have shared a document with you!');
        });
    }
}

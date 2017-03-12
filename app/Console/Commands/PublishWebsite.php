<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;
use Knowfox\User;
use Knowfox\Jobs\PublishWebsite as PublishJob;

class PublishWebsite extends Command
{
    const OWNER_ID = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:publish {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a static website from a Knowfox subtree';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $domain_name = $this->argument('domain');

        $user = User::find(self::OWNER_ID);

        dispatch(new PublishJob($user, $domain_name));
        $this->info("Publishing of website {$domain_name} for {$user->email} initiated");
    }
}

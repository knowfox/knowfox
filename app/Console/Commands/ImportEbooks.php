<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;
use Knowfox\Jobs\ImportEbooks as ImportJob;

use Knowfox\User;

class ImportEbooks extends Command
{
    const OWNER_ID = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebooks:import {sqlitedb}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ebooks from an SQlite database';

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
        $sqlitedb = $this->argument('sqlitedb');

        $user = User::find(self::OWNER_ID);

        dispatch(new ImportJob($user, $sqlitedb));
        $this->info("Import of ebooks from {$sqlitedb} for {$user->email} initiated");
    }
}

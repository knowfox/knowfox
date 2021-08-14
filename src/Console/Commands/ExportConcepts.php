<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;

class ExportConcepts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concept:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all concepts as markdown files to a target directory';

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
     * @return int
     */
    public function handle()
    {
        $dir = $this->arguments(0);
        $this->info("Exporting to ${dir}...");
        $this->info("Done.");
        return 0;
    }
}

<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;
use Knowfox\Models\Concept;
use Mpociot\Versionable\Version;

class RestoreParents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:parents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'After a hickup, restore the parent id of concepts that lost it';

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
        $this->info("Restoring parents...");

	$concepts = Concept::where('parent_id', NULL)->where('owner_id', 1)->where('updated_at', '>=', '2017-06-30');
        $this->info(' - ' . $concepts->count() . ' concepts with empty parent');

	foreach ($concepts->get() as $concept) {
	    $this->info("Restoring {$concept->id} {$concept->title}");
	    $version = Version::where('versionable_id', $concept->id)->first();
            if (!$version) {
		$this->error(" - No version");
		continue;
	    }
	    try {
	        $parent_id = $version->getModel()->parent_id;
            }
	    catch (\Exception $e) {
		$this->error(" - model not found");
		continue;
	    }
	    if (!$parent_id) {
		$this->error(" - No parent_id");
		continue;
	    }
            $this->info(' - restoring parent_id ' . $parent_id);
	    $concept->parent_id = $parent_id;
	    $concept->save();
	}

    }
}

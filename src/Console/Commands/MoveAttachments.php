<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class MoveAttachments extends Command
{
    private $start_at = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move attachments to more structured directories';

    protected function move($dir)
    {
        //$this->info(' d ' . $dir);
        foreach (scandir($dir) as $entry) {
	    if ($entry[0] == '.') {
	        continue;
            }
	    $path = $dir . '/' . $entry;
	    if (is_dir($path)) {
	        $this->move($path);
	    }
	    else {
                $new_dir = '';
		$flat = str_replace('/', '', substr($dir, $this->start_at));
                for ($i = 0; $i < strlen($flat); $i += 2) {
                    $new_dir .= '/' . substr($flat, $i, 2);
                } 

	        $this->info(' - ' . $path . ' -> ' . $new_dir);
                Storage::drive('upload')->putFileAs($new_dir,
		    new File($path), $entry);
            }
	}
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $root = storage_path('uploads-UNUSED');
	$this->start_at = strlen(storage_path('uploads-UNUSED'));
	$this->info('Moving ' . $root);
	$this->move($root);
        return 0;
    }
}

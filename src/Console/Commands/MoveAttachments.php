<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Knowfox\Services\PictureService;
use Knowfox\Models\TransferAsset;

class MoveAttachments extends Command
{
    private $start_at = 0;
    private $picture;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:attachments {--collect}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move attachments to more structured directories';

    public function __construct(PictureService $picture)
    {
        parent::__construct();
        $this->picture = $picture;
    }

    protected function move($dir)
    {
        $me = $this;
        TransferAsset::where('tranfered', false)->chunkById(200, function($assets) use ($dir, $me) {
            $me->info(' ### BATCH');
            foreach ($assets as $asset) {
		$info = pathinfo($asset->path);
                $flat = str_replace('/', '', substr($info['dirname'], $me->start_at));
                $new_dir = $me->picture->dirs($flat);

                $me->info(' - ' . $asset->path . " -> {$new_dir}/{$info['basename']}");
                try {
                    Storage::drive('upload')->putFileAs($new_dir,
                        new File($asset->path), $info['basename']);
		}
		catch (ErrorException $e) {
		    $me->error(' - Error: ' . $e->getMessage());
                    continue;
                }
		$asset->tranfered = true;
		$asset->save();
	    }
	});
    }

    public function collect($dir)
    {
        foreach (scandir($dir) as $entry) {
            if ($entry[0] == '.') {
                continue;
            }
            $path = $dir . '/' . $entry;
            if (is_dir($path)) {
                $this->collect($path);
            }
            else {
                $this->info(' - ' . $path);
		TransferAsset::create([
		    'path' => $path,
		]);
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
        $root = storage_path('uploads');
	$this->start_at = strlen($root);

        $collect = $this->option('collect');
	if ($collect) {
	    $this->info('Collecting ' . $root);
            TransferAsset::truncate();
	    $this->collect($root);
	    $this->info('Done.');
        }
	else {
	    $this->info('Moving ' . $root);
	    $this->move($root);
	    $this->info('Done.');
	}

        return 0;
    }
}

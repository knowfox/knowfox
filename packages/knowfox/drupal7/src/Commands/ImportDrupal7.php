<?php

namespace Knowfox\Drupal7\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use Knowfox\Entangle\Models\ImportedEvent;
use Knowfox\Entangle\Models\ImportedUser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImportDrupal7 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drupal7:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Drupal7 nodes from a MySQL database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->info("Importing from database " . env('DB_D7_DATABASE') . '...');
        $this->info('Done.');
    }
}

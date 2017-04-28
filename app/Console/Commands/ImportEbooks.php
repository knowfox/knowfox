<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Config;
use Knowfox\Models\ImportedEbook;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Yaml\Yaml;

class ImportEbooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebooks:import {token} {sqlitedb}';

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
    public function handle() {
        $token = $this->argument('token');
        $sqlitedb = $this->argument('sqlitedb');

        $this->info("Starting import of ebooks from {$sqlitedb} with token {$token} initiated");

        Config::set('database.connections.sqlite.database', $sqlitedb);

        $client = new GuzzleClient();

        // The CSRF validation is linked to the Laravel session
        $jar = new \GuzzleHttp\Cookie\CookieJar();

        $ebooks = ImportedEbook::all();
        foreach ($ebooks as $ebook) {
            $title = empty($ebook->title) ? $ebook->filename : $ebook->title;

            $this->info('Importing ' . $title . ' ...');

            try {
                $res = $client->request('GET', 'https://knowfox.dev/book', [
                    'query' => [
                        'token' => $token,

                        'title' => $title,
                        'author' => $ebook->author,
                        'year' => $ebook->year,
                    ],
                    'cookies' => $jar,
                ]);
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->error("... not found: " . $e->getResponse()->getBody());
                break;
            }

            if ($res->getHeaderLine('content-type') != 'application/json') {
                $this->error("... cannot read UUID: " . $res->getBody());
                break;
            }
            $response = Yaml::parse($res->getBody(), YAML::PARSE_OBJECT_FOR_MAP);
            $uuid = $response->uuid;
            $csrf_token = $response->csrf_token;
            $count = $response->count;
            $this->info(" - UUID: " . $uuid);
            $this->info(" - Count: " . $count);

            try {
                $res = $client->request('POST', 'https://knowfox.dev/book', [
                    'form_params' => [
                        'token' => $token,
                        'uuid' => $uuid,

                        'title' => $ebook->title,
                        'author' => $ebook->author,
                        'publisher' => $ebook->publisher,
                        'year' => $ebook->year,
                        'filename' => $ebook->filename,
                        'path' => $ebook->path,
                        'type' => $ebook->type,
                        'format' => $ebook->format,
                    ],
                    // 'debug' => true,
                    'cookies' => $jar,
                    'headers' => [
                        'X-CSRF-TOKEN' => $csrf_token,
                    ]
                ]);
            }
            catch (\GuzzleHttp\Exception\ServerException $e) {
                $this->error("Failed: " . $e->getResponse()->getBody());
                break;
            }

            if ($res->getHeaderLine('content-type') != 'application/json') {
                $this->error("... cannot read book data: " . $res->getBody());
                break;
            }
            $response = Yaml::parse($res->getBody(), YAML::PARSE_OBJECT_FOR_MAP);

            $this->info(' - Resulting URL: ' . $response->url);
            $this->info(' - Status: ' . $response->status);
        }
    }
}

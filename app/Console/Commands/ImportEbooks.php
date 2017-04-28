<?php

namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Config;
use Knowfox\Models\ImportedEbook;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

class ImportEbooks extends Command
{
    const EBOOK_META = '~/Applications/calibre.app/Contents/MacOS/ebook-meta';
    const EBOOK_DIR = '/Users/olav/SpaceMonkey/eBooks/';

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

    private function extractCover($path)
    {
        $path = self::EBOOK_DIR . str_replace("'", "\\'", $path);
        if (!is_file($path)) {
            $this->warn(' - Cover is not a file: ' . $path);
            return null;
        }
        $tempnam = tempnam(env('TMP_DIR'), 'cover');
        $this->comment(" - extracting cover...");
        $cmd = self::EBOOK_META . " '" . $path . "' --get-cover=" . $tempnam;
        $this->info(" - $cmd");
        shell_exec($cmd);

        if (filesize($tempnam) == 0) {
            return null;
        }

        if (!is_file($tempnam)) {
            $this->warn(' - Could not extract cover');
            return null;
        }

        return $tempnam;
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
                $res = $client->request('GET', 'https://knowfox.de/book', [
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

            $cover_path = $this->extractCover($ebook->path . '/' . $ebook->filename);
            if ($cover_path) {
                $f = new File($cover_path);
                $ext = $f->guessExtension();

                $cover_filename = 'cover.' . $ext;
            }
            else {
                $cover_filename = null;
            }

            try {
                $res = $client->request('POST', 'https://knowfox.de/book', [
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

                        'cover' => $cover_filename,
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

            if ($cover_path) {

                $this->info(' - Uploading cover...');
                try {
                    $res = $client->request('POST', 'https://knowfox.de/upload/' . $response->value->uuid, [
                        'multipart' => [[
                            'name' => 'file',
                            'contents' => fopen($cover_path, 'r'),
                            'filename' => 'cover.' . $ext,
                        ]],
                        //'debug' => true,
                        'cookies' => $jar,
                        'headers' => [
                            'X-CSRF-TOKEN' => $csrf_token,
                        ]
                    ]);
                }
                catch (\Exception $e) {
                    $this->error("Failed (cover): " . $e->getMessage());
                }

                unlink($cover_path);
            }
        }
    }
}

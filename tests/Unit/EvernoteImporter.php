<?php

namespace Tests\Unit;

use Knowfox\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Knowfox\Jobs\ImportEvernote;

class EvernoteImporter extends TestCase
{
    const MARKUP = '<div>Donnerstag: <a href="evernote:///view/84898/s1/d80cda1d-7346-4594-aa7e-e7253a468cb8/d80cda1d-7346-4594-aa7e-e7253a468cb8/" style="color:#69aa35;">Chefkoch 172. Tag (Do 2014-11-06)</a></div>';
    const REPLACEMENT = "<div>Donnerstag: <a href=\"/uuid/d80cda1d-7346-4594-aa7e-e7253a468cb8\">Chefkoch 172. Tag (Do 2014-11-06)</a>\n</div>";

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testReplaceLinks()
    {
        $importer = new ImportEvernote(new User(), 'dummy-notebook');

        $this->assertEquals(self::REPLACEMENT, $importer->replaceLinks(self::MARKUP));
    }
}

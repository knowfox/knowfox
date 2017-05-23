<?php

namespace Tests\Feature\Entangle;

use Knowfox\Entangle\Models\Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Knowfox\User;
use Knowfox\Models\Concept;
use Knowfox\Entangle\Services\ImportService;

class ImportTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp() {
        parent::setUp();

        $user = factory(User::class)->create();
        $this->actingAs($user);
    }

    private function findTimelines($owner_id, $slug = null)
    {
        $root = Concept::whereIsRoot()
            ->where('title', 'Timelines')
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $timelines = Concept::where('parent_id', $root->id);
        if ($slug) {
            return $timelines->where('slug', $slug)->get();
        }
        else {
            return $timelines->get();
        }
    }

    private function saveTimelines()
    {
        $user = factory(User::class)->make();

        $importer = new ImportService();
        $user = $importer->saveUser([
            'name' => $user->name,
            'email' => $user->email,

            'timelines' => [
                ['name' => 'tl-1', 'title' => 'Timeline #1', 'timelines' => '1,2,3'],
                ['name' => 'tl-2', 'title' => 'Timeline #2', 'timelines' => ''],
            ]
        ]);

        return $user->id;
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testImportUser()
    {
        $user_id = $this->saveTimelines();
        $timelines = $this->findTimelines($user_id);

        $this->assertEquals(2, $timelines->count());
    }

    public function testImportEvent()
    {
        $user_id = $this->saveTimelines();
        $timeline_slug = 'tl-1';

        $importer = new ImportService();
        $event = $importer->saveEvent([
            'owner_id' => $user_id,
            'timeline_slug' => $timeline_slug,
            'title' => 'Event #1',
            'public' => true,
            'description' => 'Body of event #1',
            'created' => '2017-05-01',
            'date_from' => '1963-07-19',
            'date_to' => null,
            'duration' => 1,
            'duration_unit' => 'd',
            'anniversary' => 'Olavs %d. Geburtstag',
            'source_id' => 'https://olav.net',
            'replicated' => false,
        ]);

        $timeline = $this->findTimelines($user_id, $timeline_slug)->first();

        $retrieved_event = Event::where('parent_id', $timeline->id)->firstOrFail();

        $this->assertEquals($event->id, $retrieved_event->id);
        $this->assertEquals($event->event()->first()->date_from, $retrieved_event->event()->first()->date_from);
    }
}

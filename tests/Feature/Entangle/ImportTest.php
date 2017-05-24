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

        $this->user = factory(User::class)->create();
        $this->actingAs($this->user);
    }

    private function findTimelines($person, $title = null)
    {
        $root = Concept::whereIsRoot()
            ->where('title', 'Timelines')
            ->where('owner_id', $this->user->id)
            ->firstOrFail();

        $timelines = Concept::where('parent_id', $root->id);
        if ($title) {
            $full_title = $person->title . ': ' . $title;
            return $timelines->where('title', $full_title)->get();
        }
        else {
            return $timelines->get();
        }
    }

    private function saveTimelines()
    {
        $user = factory(User::class)->make();

        $importer = new ImportService();
        $person = $importer->savePerson([
            'name' => $user->name,
            'email' => $user->email,

            'timelines' => [
                ['name' => 'tl-1', 'title' => 'Timeline #1', 'timelines' => '1,2,3'],
                ['name' => 'tl-2', 'title' => 'Timeline #2', 'timelines' => ''],
            ]
        ]);

        return $person;
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testImportUser()
    {
        $person = $this->saveTimelines();
        $timelines = $this->findTimelines($person);

        $this->assertEquals(2, $timelines->count());
    }

    public function testImportEvent()
    {
        $person = $this->saveTimelines();
        $timeline_title = 'Timeline #1';

        $importer = new ImportService();
        $event = $importer->saveEvent([
            'person_id' => $person->id,
            'timeline_title' => $timeline_title,
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

        $timeline = $this->findTimelines($person, $timeline_title)->first();

        $retrieved_event = Event::where('parent_id', $timeline->id)->firstOrFail();

        $this->assertEquals($event->id, $retrieved_event->id);
        $this->assertEquals($event->event()->first()->date_from, $retrieved_event->event()->first()->date_from);
    }
}

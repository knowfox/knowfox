<?php

namespace Tests\Feature;

use Knowfox\Models\Concept;
use Knowfox\Services\OutlineService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Knowfox\User;

class OutlinerTest extends TestCase
{
    use DatabaseMigrations;

    const DATA = ['body' => [
        [
            'id' => 2, 'text' => 'node',
            '@outlines' => [
                [
                    'id' => 3, 'text' => 'node.A',
                    '@outlines' => [
                        [ 'id' => 4, 'text' => 'node.A.A' ],
                        [ 'id' => 5, 'text' => 'node.A.B' ],
                    ],
                ],
                [
                    'id' => 6, 'text' => 'node.B',
                    '@outlines' => [
                        [ 'id' => 7, 'text' => 'node.B.A' ],
                        [ 'id' => 8, 'text' => 'node.B.B' ],
                    ],
                ],
            ]
        ]
    ]];

    protected $root;

    private function createKids($root, $level)
    {
        for ($i = 0; $i < 2; $i++) {
            $kid = new Concept();
            $kid->title = $root->title . '.' .chr(ord('A') + $i);
            $kid->parent_id = $root->id;
            $kid->save();

            if ($level > 0) {
                $this->createKids($kid, $level - 1);
            }
        }
    }


    public function setUp()
    {
        parent::setUp();

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $toplevel = new Concept();
        $toplevel->title = 'top';
        $toplevel->save();

        $this->root = new Concept();
        $this->root->title = 'node';
        $this->root->parent_id = $toplevel->id;
        $this->root->save();

        $this->createKids($this->root, 1);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testOpml()
    {
        $response = $this->get('/opml/' . $this->root->id);

        $response->assertStatus(200);

        $expected = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<opml version="2.0">
    <head>
        <title>node</title>
        <dateCreated>2017-05-09 18:17:03</dateCreated>
        <dateModified>2017-05-09 18:17:03</dateModified>
        <ownerName>Esperanza Yost</ownerName>
        <ownerEmail>okreiger@example.com</ownerEmail>
        <docs>http://dev.opml.org/spec2.html</docs>
    </head>
    <body>
        <outline id="2" text="node">
            <outline id="3" text="node.A">
                <outline id="4" text="node.A.A">
                </outline>
                <outline id="5" text="node.A.B">
                </outline>
            </outline>
            <outline id="6" text="node.B">
                <outline id="7" text="node.B.A">
                </outline>
                <outline id="8" text="node.B.B">
                </outline>
            </outline>
        </outline>
    </body>
</opml>
EOF;

        $this->assertXmlStringEqualsXmlString(
            preg_replace(['#<head>.*</head>#s', '#^\s*#m'], '', $expected),
            preg_replace(['#<head>.*</head>#s', '#^\s*#m'], '', $response->getContent())
        );
    }

    private function checkSingleRoot()
    {
        $toplevel = Concept::whereIsRoot()->get();
        $this->assertEquals(1, $toplevel->count());
        $this->assertEquals($this->root->parent_id, $toplevel[0]->id);
    }

    public function checkWith($before_count, $after_count, $changes_count, $data, $result)
    {
        /** @var OutlineService $outline */
        $outline = app(OutlineService::class);
        $this->assertEquals($before_count, Concept::count());

        $count = $outline->update($this->root, $data);

        $this->assertEquals($changes_count, $count);
        $this->assertEquals($after_count, Concept::count());

        $this->checkSingleRoot();

        $flat = Concept::whereDescendantOrSelf($this->root->id)
            ->get()
            ->toFlatTree()
            ->pluck('title', 'id')
            ->toArray();
        $this->assertEquals($result, $flat);
    }

    public function testDeleteNone()
    {
        $this->checkWith(8, 8, 0, self::DATA, [
            2 => "node",
            3 => "node.A",
            4 => "node.A.A",
            5 => "node.A.B",
            6 => "node.B",
            7 => "node.B.A",
            8 => "node.B.B",
        ]);
    }

    public function testMoveNode()
    {
        $data = self::DATA;
        $data['body'][0]['@outlines'][1]['@outlines'][] = [ 'id' => 4, 'title' => 'node.A.A' ];
        unset($data['body'][0]['@outlines'][0]['@outlines'][0]);

        $this->checkWith(8, 8, 6, $data, [
            2 => "node",
            3 => "node.A",
            5 => "node.A.B",
            6 => "node.B",
            7 => "node.B.A",
            8 => "node.B.B",
            4 => "node.A.A",
        ]);
    }

    public function testAddNode()
    {
        $data = self::DATA;
        $data['body'][0]['@outlines'][1]['@outlines'][] = [ 'title' => 'node.B.C' ];

        $this->checkWith(8, 9, 4, $data, [
            2 => "node",
            3 => "node.A",
            4 => "node.A.A",
            5 => "node.A.B",
            6 => "node.B",
            7 => "node.B.A",
            8 => "node.B.B",
            9 => "node.B.C",
        ]);
    }

    public function testDeleteInnerNode()
    {
        $data = self::DATA;
        unset($data['body'][0]['@outlines'][0]);

        $this->checkWith(8, 5, 5, $data, [
            2 => "node",
            6 => "node.B",
            7 => "node.B.A",
            8 => "node.B.B",
        ]);
    }

    public function testDeleteLeafNode()
    {
        $data = self::DATA;
        unset($data['body'][0]['@outlines'][0]['@outlines'][0]);

        $this->checkWith(8, 7, 7, $data, [
            2 => "node",
            3 => "node.A",
            5 => "node.A.B",
            6 => "node.B",
            7 => "node.B.A",
            8 => "node.B.B",
        ]);
    }

    public function testDeleteAll()
    {
        /** @var OutlineService $outline */
        $outline = app(OutlineService::class);
        $this->assertEquals(8, Concept::count());
        $count = $outline->update($this->root, ['body' => []]);
        $this->assertEquals(1, $count);
        $this->assertEquals(1, Concept::count());
    }
}

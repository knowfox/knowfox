<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Knowfox\User;
use Symfony\Component\DomCrawler\Crawler;

class JournalTest extends TestCase
{
    public function testToday()
    {
        $user = factory(User::class)->create();
        $today = date('Y-m-d');

        $response = $this->actingAs($user)
            ->get('/journal');

        $response->assertStatus(302);

        $location = $response->headers->get('Location');
        $content = $this->actingAs($user)
            ->get($location)
            ->getContent();

        $crawler = new Crawler($content);

        $h1 = trim($crawler->filter('h1')->text());
        $this->assertStringStartsWith($today, $h1);
    }
}

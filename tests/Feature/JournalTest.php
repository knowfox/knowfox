<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Knowfox\User;
use Symfony\Component\DomCrawler\Crawler;

use Carbon\Carbon;

class JournalTest extends TestCase
{
    private function followRedirectAt($url)
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->get($url);

        $response->assertStatus(302);

        $location = $response->headers->get('Location');
        $content = $this->actingAs($user)
            ->get($location)
            ->getContent();

        return new Crawler($content);
    }

    public function testToday()
    {
        $crawler = $this->followRedirectAt('/journal');

        $h1 = trim($crawler->filter('h1')->text());

        $date = Carbon::today()->format('Y-m-d');
        $this->assertStringStartsWith($date, $h1);
    }

    public function testDate()
    {
        $date = '2017-03-31';
        $crawler = $this->followRedirectAt('/journal/' . $date);

        $h1 = trim($crawler->filter('h1')->text());
        $this->assertStringStartsWith($date, $h1);
    }
}

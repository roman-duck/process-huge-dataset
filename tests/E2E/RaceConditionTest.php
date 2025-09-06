<?php

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpClient\HttpClient;

class RaceConditionTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $cache = new RedisAdapter(RedisAdapter::createConnection('redis://redis:6379'));
        $cache->clear();
    }

    public function testRaceCondition(): void
    {
        $client =  HttpClient::create();

        $responses = [
            $client->request('GET', 'http://nginx/process-huge-dataset'),
            $client->request('GET', 'http://nginx/process-huge-dataset'),
            $client->request('GET', 'http://nginx/process-huge-dataset'),
        ];

        $statuses = [];
        foreach ($responses as $response) {
            $statuses[] = $response->getStatusCode();
        }

        sort($statuses);

        $this->assertSame([200, 202, 202], $statuses);
    }
}

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
        $cache = new RedisAdapter(RedisAdapter::createConnection($_ENV['REDIS_DSN']));
        $dataset = $cache->getItem('dataset');
        $dataset->expiresAfter(-1);
        $cache->save($dataset);
    }

    public function testRaceCondition(): void
    {
        $client =  HttpClient::create();

        $responses = [
            $client->request('GET', 'http://localhost:8080/process-huge-dataset'),
            $client->request('GET', 'http://localhost:8080/process-huge-dataset'),
        ];

        $statuses = [];
        foreach ($responses as $response) {
            $statuses[] = $response->getStatusCode();
        }

        sort($statuses);

        $this->assertSame([200, 202], $statuses);
    }
}

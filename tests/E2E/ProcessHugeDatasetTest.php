<?php

namespace App\Tests\E2E;

use App\Service\DataService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProcessHugeDatasetTest extends WebTestCase
{
    private \Redis $redis;

    private HttpClientInterface $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->redis = new \Redis();
        $this->redis->connect('redis');
        $this->redis->flushAll();

        $this->client = HttpClient::create();
    }

    public function testRaceCondition(): void
    {
        $responses = [
            $this->client->request('GET', 'http://nginx/process-huge-dataset'),
            $this->client->request('GET', 'http://nginx/process-huge-dataset'),
            $this->client->request('GET', 'http://nginx/process-huge-dataset'),
        ];

        $statuses = [];
        foreach ($responses as $response) {
            $statuses[] = $response->getStatusCode();
        }
        sort($statuses);

        $this->assertSame([200, 202, 202], $statuses);
    }

    public function testStaleCache(): void
    {
        $dataset = [
            'payload' => DataService::fetchData(),
            'expires_at' => time() - 1,
        ];

        $this->redis->set('dataset', json_encode($dataset));

        $responses = [
            $this->client->request('GET', 'http://nginx/process-huge-dataset'),
            $this->client->request('GET', 'http://nginx/process-huge-dataset'),
        ];

        $cacheStatuses = [];
        foreach ($responses as $response) {
            $headers = $response->getHeaders();
            if (array_key_exists('x-cache-status', $headers)) {
                $cacheStatuses[] = $headers['x-cache-status'][0];
            }
        }

        $this->assertCount(1, $cacheStatuses);
        $this->assertEquals('STALE', $cacheStatuses[0]);
    }
}

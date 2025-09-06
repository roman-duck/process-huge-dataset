<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ProcessDatasetTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Clear the Redis dataset cache before each test
        $cache = new RedisAdapter(RedisAdapter::createConnection($_ENV['REDIS_DSN']));
        $dataset = $cache->getItem('dataset');
        $dataset->expiresAfter(-1);
        $cache->save($dataset);
    }

    public function testProcessDatasetSuccess(): void
    {
        $client = static::createClient();
        // The first request should process the dataset and cache it
        $client->request('GET', '/process-huge-dataset');
        $this->assertResponseIsSuccessful();

        // The second request should hit the cache
        $client->request('GET', '/process-huge-dataset');
        $this->assertResponseIsSuccessful();
    }

}

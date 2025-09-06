<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProcessDatasetTest extends WebTestCase
{
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

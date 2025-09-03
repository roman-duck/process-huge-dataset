<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProcessTest extends WebTestCase
{
    public function testProcessSuccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/process-huge-dataset');

        $this->assertResponseIsSuccessful();
    }
}

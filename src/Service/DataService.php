<?php

namespace App\Service;

use App\Model\Data;

class DataService
{
    const TTL = 60; // 1 minute

    private readonly \Redis $redis;
    public function __construct(string $dsn)
    {
        $this->redis = new \Redis();
        $items = parse_url($dsn);
        $this->redis->connect($items['host'], $items['port'] ?? 6379);
    }

    public function get(string $key): ?array
    {
        return $this->redis->get($key) ? json_decode($this->redis->get($key), true) : null;
    }

    public function fetch(string $key): array
    {
        $dataset = [
            'payload' => DataService::fetchData(),
            'expires_at' => time() + self::TTL,
        ];
        $this->redis->set($key, json_encode($dataset));
        return $dataset;
    }

    public static function fetchData(): array
    {
        sleep(10);

        $maxItems = 5;

        $data = [];
        for($i = 0; $i < $maxItems; $i++) {
            $data[] = new Data(
                date('c'),
                rand(1, 9999),
            );
        }
        return $data;
    }
}

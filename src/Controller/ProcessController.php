<?php

namespace App\Controller;

use App\Model\Data;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

final class ProcessController extends AbstractController
{
    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly CacheInterface $cache
    )
    {
    }

    #[Route('/process-huge-dataset', name: 'app_process', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Process a huge dataset',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Data::class))
        )
    )]
    public function process(): JsonResponse
    {
        $lock = $this->lockFactory->createLock('data_fetch_lock');

        $dataset = $this->cache->getItem('dataset');

        $headers = [];

        if (!$lock->acquire()) {
            if (!$dataset->isHit()) {
                return $this->json([],Response::HTTP_ACCEPTED);
            }

            $headers = ['X-Cache-Status' => 'STALE'];
        }

        if ($dataset->isHit()) {
            return $this->json($dataset->get(), Response::HTTP_OK, $headers);
        }

        try {
            $data = $this->fetchData();

            $dataset->set($data);
            $dataset->expiresAfter(60);
            $this->cache->save($dataset);

            return $this->json($data);
        } finally {
            $lock->release();
        }
    }

    private function fetchData(): array
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

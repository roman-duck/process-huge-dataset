<?php

namespace App\Controller;

use App\Model\Data;
use App\Service\DataService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Attribute\Route;

final class ProcessController extends AbstractController
{
    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly DataService $dataService,
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

        $dataset = $this->dataService->get('dataset');

        if (!$lock->acquire()) {
            if (!$dataset) {
                return $this->json([], Response::HTTP_ACCEPTED);
            }

            if ($dataset['expires_at'] < time()) {
                return $this->json($dataset['payload'], Response::HTTP_OK, ['X-Cache-Status' => 'STALE']);
            }
        }

        if (!$dataset || $dataset['expires_at'] < time()) {
            $dataset = $this->dataService->fetch('dataset');
        }

        $lock->release();

        return $this->json($dataset['payload']);
    }
}

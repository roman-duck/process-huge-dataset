<?php

namespace App\Controller;

use App\Model\Data;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

final class ProcessController extends AbstractController
{
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
        return $this->json($this->fetchData());
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

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ProcessController extends AbstractController
{
    #[Route('/process-huge-dataset', name: 'app_process')]
    public function index(): JsonResponse
    {
        return $this->json($this->fetchData());
    }

    private function fetchData(): array
    {
        sleep(10);

        $maxItems = 5;

        $data = [];
        for($i = 0; $i < $maxItems; $i++) {
            $data[] = [
                'date' => date('c'),
                'value' => rand(1, 9999),
            ];
        }
        return $data;
    }
}

<?php

namespace App\Model;

use OpenApi\Attributes as OA;


class Data
{
    #[OA\Property(description: 'Date in ISO 8601 format')]
    public string $date;
    #[OA\Property(description: 'Value')]
    public string $value;

    public function __construct(string $date, string $value)
    {
        $this->date = $date;
        $this->value = $value;
    }
}

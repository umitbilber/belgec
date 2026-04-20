<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariYaslandirmaServiceInterface
{
    public function getReport(?string $tip = null, int $cariId = 0): array;
}
<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariYaslandirmaRepositoryInterface
{
    public function getOpenInvoices(?string $tip = null, int $cariId = 0): array;
}
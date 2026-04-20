<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariPrintServiceInterface
{
    public function getPrintData(int $cariId): array;
    public function getBakiyeDurumu(float $bakiye): array;
    public function formatIslemTipi(string $tip): string;
}
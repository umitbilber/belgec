<?php

declare(strict_types=1);

namespace App\Interfaces;

interface StokHareketServiceInterface
{
    public function getReport(array $input): array;
}
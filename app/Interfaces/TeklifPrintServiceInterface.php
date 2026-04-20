<?php

declare(strict_types=1);

namespace App\Interfaces;

interface TeklifPrintServiceInterface
{
    public function getPrintData(int $teklifId): array;
}
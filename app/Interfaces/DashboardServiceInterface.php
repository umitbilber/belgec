<?php

declare(strict_types=1);

namespace App\Interfaces;

interface DashboardServiceInterface
{
    public function getDailySummary(?string $date = null): array;
    public function getModuleDefinitions(): array;
    public function getModuleLabels(): array;
}
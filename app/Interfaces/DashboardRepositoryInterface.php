<?php

declare(strict_types=1);

namespace App\Interfaces;

interface DashboardRepositoryInterface
{
    public function getDailyTahsilat(string $date): float;
    public function getDailyTediye(string $date): float;
    public function getDailyAlis(string $date): float;
    public function getDailySatis(string $date): float;
}
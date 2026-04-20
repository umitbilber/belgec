<?php

declare(strict_types=1);

namespace App\Interfaces;

interface MutabakatRepositoryInterface
{
    public function findCariById(int $id): ?array;
}
<?php

declare(strict_types=1);

namespace App\Interfaces;

interface StokHareketRepositoryInterface
{
    public function getFiltered(array $filters): array;
}
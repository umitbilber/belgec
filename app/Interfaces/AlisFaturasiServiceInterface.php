<?php

declare(strict_types=1);

namespace App\Interfaces;

interface AlisFaturasiServiceInterface extends BaseFaturaServiceInterface
{
    public function getAll(): array;
    public function getFiltered(array $filters): array;
}
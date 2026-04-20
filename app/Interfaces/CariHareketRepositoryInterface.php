<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariHareketRepositoryInterface
{
    public function getFiltered(array $filters): array;
    public function findById(int $id): ?array;
    public function updateMovement(int $id, array $data): void;
    public function deleteMovement(int $id): void;
}
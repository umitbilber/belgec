<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariServiceInterface
{
    public function getAll(): array;
    public function getById(int $id): ?array;
    public function getMovements(int $cariId): array;
    public function create(array $input): void;
    public function update(int $id, array $input): void;
    public function delete(int $id): void;
    public function recordMovement(array $input): void;
}
<?php

declare(strict_types=1);

namespace App\Interfaces;

interface BaseFaturaServiceInterface
{
    public function getAll(): array;
    public function getById(int $id): ?array;
    public function getItems(int $faturaId): array;
    public function create(array $input): void;
    public function delete(int $faturaId): void;
    public function update(int $faturaId, array $input): void;
}
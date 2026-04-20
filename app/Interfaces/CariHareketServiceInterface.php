<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariHareketServiceInterface
{
    public function getReport(array $input): array;
    public function getById(int $id): ?array;
    public function update(int $id, array $input): void;
    public function delete(int $id): void;
}
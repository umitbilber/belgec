<?php

declare(strict_types=1);

namespace App\Interfaces;

interface StokRepositoryInterface
{
    public function all(): array;
    public function findById(int $id): ?array;
    public function hasLinkedRecords(int $stokId): bool;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
    public function deleteMovementsByStokId(int $stokId): void;
    public function insertMovement(array $data): void;
    public function getMovementsByStokId(int $stokId): array;
    public function getFiyatGecmisi(int $stokId): array;
public function findByKodOrAd(string $stokKodu, string $urunAdi): ?array;
}
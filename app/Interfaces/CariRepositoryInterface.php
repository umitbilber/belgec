<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariRepositoryInterface
{
    public function all(): array;
    public function findById(int $id): ?array;
    public function hasLinkedRecords(int $cariId): bool;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
    public function deleteMovementsByCariId(int $cariId): void;
    public function insertMovement(array $data): void;
    public function decreaseBalance(int $cariId, float $amount): void;
    public function increaseBalance(int $cariId, float $amount): void;
    public function setBalance(int $cariId, float $amount): void;
    public function getMovementsByCariId(int $cariId): array;
    public function getOpenInvoicesForCariAndType(int $cariId, string $tip): array;
    public function increaseInvoicePaidAmount(int $faturaId, float $amount): void;
    public function clampInvoicePaidAmounts(): void;
    public function resetAllInvoicePaidAmounts(): void;
    public function getAllPaymentMovements(): array;
}
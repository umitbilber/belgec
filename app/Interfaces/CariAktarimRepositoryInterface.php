<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariAktarimRepositoryInterface
{
    public function findCariById(int $cariId): ?array;
    public function getInvoiceIdsByCariId(int $cariId): array;
    public function deleteInvoiceItemsByInvoiceIds(array $invoiceIds): void;
    public function deleteInvoicesByCariId(int $cariId): void;
    public function deleteMovementsByCariId(int $cariId): void;
    public function deleteStockMovementsByCariId(int $cariId): void;
    public function setCariBalance(int $cariId, float $balance): void;
    public function insertInvoice(int $cariId, string $tip, string $faturaNo, string $tarih, float $genelToplam): int;
    public function insertInvoiceMovement(int $cariId, string $tip, float $tutar, string $faturaNo, string $tarih): void;
    public function insertPaymentMovement(int $cariId, string $tip, float $tutar, string $aciklama, string $tarih): void;
    public function increaseBalance(int $cariId, float $amount): void;
    public function decreaseBalance(int $cariId, float $amount): void;
    public function resetAllInvoicePaidAmounts(): void;
    public function getAllPaymentMovements(): array;
    public function getOpenInvoicesForCariAndType(int $cariId, string $tip): array;
    public function increaseInvoicePaidAmount(int $faturaId, float $amount): void;
    public function clampInvoicePaidAmounts(): void;
}
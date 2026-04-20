<?php

declare(strict_types=1);

namespace App\Interfaces;

interface BaseFaturaRepositoryInterface
{
    public function all(): array;
public function getFiltered(array $filters): array;
    public function findInvoiceById(int $id): ?array;
    public function findInvoiceWithCariName(int $id): ?array;
    public function getInvoiceItems(int $faturaId): array;
    public function getInvoiceItemsRaw(int $faturaId): array;
    public function findCariIdByName(string $name): int|false;
    public function findCariById(int $id): ?array;
    public function createCari(string $name): int;
    public function findStockId(string $urunAdi, string $stokKodu): int|false;
    public function createStock(string $urunAdi, string $stokKodu): int;
    public function createInvoice(string $faturaNo, int $cariId, string $tarih, ?string $vadeTarihi = null): int;
    public function updateInvoiceHeader(int $faturaId, string $faturaNo, int $cariId, string $tarih, ?string $vadeTarihi = null): void;
    public function updateInvoiceTotal(int $faturaId, float $genelToplam): void;
    public function insertInvoiceItem(int $faturaId, int $stokId, float $miktar, float $birimFiyat, int $kdvOrani): void;
    public function deleteInvoiceItems(int $faturaId): void;
    public function deleteInvoice(int $faturaId): void;
    public function increaseStock(int $stokId, float $miktar): void;
    public function decreaseStock(int $stokId, float $miktar): void;
    public function insertStockMovement(int $stokId, int $cariId, string $faturaNo, float $miktar, float $birimFiyat): void;
    public function deleteStockMovementsByInvoiceNo(string $faturaNo): void;
    public function deleteStockMovementByStockIdAndInvoiceNo(int $stokId, string $faturaNo): void;
    public function increaseCariBalance(int $cariId, float $amount): void;
    public function decreaseCariBalance(int $cariId, float $amount): void;
    public function insertCariMovement(int $cariId, float $tutar, string $aciklama): void;
    public function deleteCariMovementLikeInvoiceNo(int $cariId, string $faturaNo): void;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
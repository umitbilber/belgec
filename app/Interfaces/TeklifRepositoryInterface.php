<?php

declare(strict_types=1);

namespace App\Interfaces;

interface TeklifRepositoryInterface
{
    public function all(): array;
    public function findById(int $id): ?array;
    public function getItems(int $teklifId): array;
    public function findCariIdByName(string $name): int|false;
    public function createCari(string $name): int;
    public function createOffer(string $teklifNo, int $cariId, string $tarih, string $paraBirimi, string $teklifNotlari = ''): int;
    public function updateOfferHeader(int $id, string $teklifNo, int $cariId, string $tarih, string $paraBirimi, string $teklifNotlari = ''): void;
    public function updateOfferTotal(int $id, float $genelToplam): void;
    public function insertItem(int $teklifId, string $urunAdi, string $marka, float $miktar, float $birimFiyat, float $satirToplam, string $termin, string $paraBirimi): void;
    public function deleteItems(int $teklifId): void;
    public function deleteOffer(int $id): void;
}
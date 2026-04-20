<?php

declare(strict_types=1);

namespace App\Interfaces;

interface EdmServiceInterface
{
    public function testConnection(): array;
    public function previewRecentInvoices(string $direction, int $days = 7): array;
    public function getInvoicesByRange(string $direction, string $baslangic, string $bitis): array;
    public function getFaturaIcerik(string $uuid, string $direction): string;
    public function getFaturaKalemleri(string $uuid, string $direction): array;
    public function getKontor(): array;
}
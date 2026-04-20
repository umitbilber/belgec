<?php

declare(strict_types=1);

namespace App\Interfaces;

interface CariAktarimServiceInterface
{
    public function storeUploadedStatement(int $cariId, ?array $dosya): array;
    public function buildPreviewFromStoredFile(array $uploaded): array;
    public function addPreviewToQueue(array $preview): void;
    public function removePreviewFromQueue(string $queueId): void;
    public function executeAllImportsFromQueue(): array;
    public function executeImportFromSession(int $requestedCariId): array;
}
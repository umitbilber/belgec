<?php

declare(strict_types=1);

namespace App\Interfaces;

interface MutabakatServiceInterface
{
    public function getCariById(int $id): ?array;
    public function buildMutabakatMail(int $cariId, string $ozelMetin = ''): array;
    public function sendMutabakatMail(int $cariId, string $ozelMetin = ''): void;
    public function sendReplyNotification(int $cariId, string $cevap, string $aciklama = ''): void;
}
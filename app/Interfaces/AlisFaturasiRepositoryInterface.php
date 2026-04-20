<?php

declare(strict_types=1);

namespace App\Interfaces;

interface AlisFaturasiRepositoryInterface extends BaseFaturaRepositoryInterface
{
    public function deleteCariMovementForPurchase(int $cariId, string $aciklama): void;
}
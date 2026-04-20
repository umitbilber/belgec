<?php

declare(strict_types=1);

namespace App\Interfaces;

interface SatisFaturasiRepositoryInterface extends BaseFaturaRepositoryInterface
{
    public function deleteCariMovementForSale(int $cariId, string $aciklama): void;
}
<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\SatisFaturasiRepositoryInterface;

class SatisFaturasiRepository extends BaseFaturaRepository implements SatisFaturasiRepositoryInterface
{
    public function deleteCariMovementForSale(int $cariId, string $aciklama): void
    {
        $this->deleteCariMovementLikeInvoiceNo($cariId, $aciklama);
    }

    protected function getInvoiceType(): string
    {
        return 'satis';
    }

    protected function getStockMovementDescription(): string
    {
        return 'Satış Faturası';
    }

    protected function normalizeStockMovementAmount(float $miktar): float
    {
        return -$miktar;
    }
}
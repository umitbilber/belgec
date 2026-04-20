<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\AlisFaturasiRepositoryInterface;

class AlisFaturasiRepository extends BaseFaturaRepository implements AlisFaturasiRepositoryInterface
{
    public function deleteCariMovementForPurchase(int $cariId, string $aciklama): void
    {
        $this->deleteCariMovementLikeInvoiceNo($cariId, $aciklama);
    }

    protected function getInvoiceType(): string
    {
        return 'alis';
    }

    protected function getStockMovementDescription(): string
    {
        return 'Alış Faturası';
    }
}
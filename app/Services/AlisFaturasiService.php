<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AlisFaturasiRepositoryInterface;
use App\Interfaces\AlisFaturasiServiceInterface;

class AlisFaturasiService extends BaseFaturaService implements AlisFaturasiServiceInterface
{
    public function __construct(AlisFaturasiRepositoryInterface $repository)
{
    $this->repository = $repository;
}

public function getFiltered(array $filters): array
{
    $faturalar = $this->repository->getFiltered($filters);

    foreach ($faturalar as &$fatura) {
        $fatura['kalemler'] = $this->repository->getInvoiceItems((int) $fatura['id']);
    }
    unset($fatura);

    return $faturalar;
}

    protected function applyStockEffect(int $stokId, float $miktar): void
    {
        $this->repository->increaseStock($stokId, $miktar);
    }

    protected function revertStockEffect(int $stokId, float $miktar): void
    {
        $this->repository->decreaseStock($stokId, $miktar);
    }

    protected function applyCariEffect(int $cariId, float $tutar): void
    {
        $this->repository->decreaseCariBalance($cariId, $tutar);
    }

    protected function revertCariEffect(int $cariId, float $tutar): void
    {
        $this->repository->increaseCariBalance($cariId, $tutar);
    }

    protected function deleteCariMovementForInvoice(int $cariId, string $faturaNo): void
    {
        $this->repository->deleteCariMovementForPurchase($cariId, $faturaNo);
    }

    protected function getNotFoundMessage(): string
    {
        return 'Alış faturası bulunamadı.';
    }
}
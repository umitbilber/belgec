<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\BaseFaturaServiceInterface;
use App\Interfaces\BaseFaturaRepositoryInterface;

abstract class BaseFaturaService implements BaseFaturaServiceInterface
{
    protected BaseFaturaRepositoryInterface $repository;

    public function getAll(): array
    {
        $faturalar = $this->repository->all();

        foreach ($faturalar as &$fatura) {
            $fatura['kalemler'] = $this->repository->getInvoiceItems((int) $fatura['id']);
        }

        unset($fatura);

        return $faturalar;
    }

    public function getById(int $id): ?array
    {
        return $this->repository->findInvoiceWithCariName($id);
    }

    public function getItems(int $faturaId): array
    {
        return $this->repository->getInvoiceItems($faturaId);
    }

    public function create(array $input): void
    {
        [$cariAdi, $faturaNo, $tarih, $manuelVadeTarihi] = $this->validateHeader($input);
        [$stokKodlari, $urunAdlari, $miktarlar, $fiyatlar, $kdvler] = $this->extractLineInputs($input);

        $this->repository->beginTransaction();

        try {
            $cariId = $this->repository->findCariIdByName($cariAdi);
if (!$cariId) {
    $cariId = $this->repository->createCari($cariAdi);
}

$vadeTarihi = $this->resolveVadeTarihi($manuelVadeTarihi, $tarih, (int) $cariId);

$faturaId = $this->repository->createInvoice($faturaNo, (int) $cariId, $tarih, $vadeTarihi);
            $genelToplam = 0.0;

            for ($i = 0; $i < count($urunAdlari); $i++) {
                $urunAdi = trim((string) ($urunAdlari[$i] ?? ''));
                $stokKodu = trim((string) ($stokKodlari[$i] ?? ''));

                if ($urunAdi === '') {
                    continue;
                }

                $stokId = $this->repository->findStockId($urunAdi, $stokKodu);
                if (!$stokId) {
                    $stokId = $this->repository->createStock($urunAdi, $stokKodu);
                }

                $miktar = (float) ($miktarlar[$i] ?? 0);
                $fiyat = (float) ($fiyatlar[$i] ?? 0);
                $kdv = (int) ($kdvler[$i] ?? 0);

                $satirTutar = $miktar * $fiyat;
                $satirKdv = $satirTutar * ($kdv / 100);
                $satirToplam = $satirTutar + $satirKdv;
                $genelToplam += $satirToplam;

                $this->repository->insertInvoiceItem($faturaId, (int) $stokId, $miktar, $fiyat, $kdv);
                $this->applyStockEffect((int) $stokId, $miktar);
                $this->repository->insertStockMovement((int) $stokId, (int) $cariId, $faturaNo, $miktar, $fiyat);
            }

            $this->repository->updateInvoiceTotal($faturaId, $genelToplam);
            $this->applyCariEffect((int) $cariId, $genelToplam);
            $this->repository->insertCariMovement((int) $cariId, $genelToplam, $faturaNo);

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function delete(int $faturaId): void
    {
        $fatura = $this->repository->findInvoiceById($faturaId);

        if (!$fatura) {
            throw new RuntimeException($this->getNotFoundMessage());
        }

        $this->repository->beginTransaction();

        try {
            $this->revertCariEffect((int) $fatura['cari_id'], (float) $fatura['genel_toplam']);
            $this->repository->deleteCariMovementLikeInvoiceNo((int) $fatura['cari_id'], (string) $fatura['fatura_no']);

            $kalemler = $this->repository->getInvoiceItemsRaw($faturaId);
            foreach ($kalemler as $kalem) {
                $this->revertStockEffect((int) $kalem['stok_id'], (float) $kalem['miktar']);
                $this->repository->deleteStockMovementByStockIdAndInvoiceNo((int) $kalem['stok_id'], (string) $fatura['fatura_no']);
            }

            $this->repository->deleteInvoiceItems($faturaId);
            $this->repository->deleteInvoice($faturaId);

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function update(int $faturaId, array $input): void
    {
        $eskiFatura = $this->repository->findInvoiceById($faturaId);

        if (!$eskiFatura) {
            throw new RuntimeException($this->getNotFoundMessage());
        }

        [$cariAdi, $faturaNo, $tarih, $manuelVadeTarihi] = $this->validateHeader($input);
        [$stokKodlari, $urunAdlari, $miktarlar, $fiyatlar, $kdvler] = $this->extractLineInputs($input);

        $this->repository->beginTransaction();

        try {
            $this->revertCariEffect((int) $eskiFatura['cari_id'], (float) $eskiFatura['genel_toplam']);
            $this->deleteCariMovementForInvoice((int) $eskiFatura['cari_id'], (string) $eskiFatura['fatura_no']);

            $eskiKalemler = $this->repository->getInvoiceItemsRaw($faturaId);
            foreach ($eskiKalemler as $kalem) {
                $this->revertStockEffect((int) $kalem['stok_id'], (float) $kalem['miktar']);
            }

            $this->repository->deleteStockMovementsByInvoiceNo((string) $eskiFatura['fatura_no']);
            $this->repository->deleteInvoiceItems($faturaId);

            $cariId = $this->repository->findCariIdByName($cariAdi);
if (!$cariId) {
    $cariId = $this->repository->createCari($cariAdi);
}

$vadeTarihi = $this->resolveVadeTarihi($manuelVadeTarihi, $tarih, (int) $cariId);

$this->repository->updateInvoiceHeader($faturaId, $faturaNo, (int) $cariId, $tarih, $vadeTarihi);

            $genelToplam = 0.0;

            for ($i = 0; $i < count($urunAdlari); $i++) {
                $urunAdi = trim((string) ($urunAdlari[$i] ?? ''));
                $stokKodu = trim((string) ($stokKodlari[$i] ?? ''));

                if ($urunAdi === '') {
                    continue;
                }

                $stokId = $this->repository->findStockId($urunAdi, $stokKodu);
                if (!$stokId) {
                    $stokId = $this->repository->createStock($urunAdi, $stokKodu);
                }

                $miktar = (float) ($miktarlar[$i] ?? 0);
                $fiyat = (float) ($fiyatlar[$i] ?? 0);
                $kdv = (int) ($kdvler[$i] ?? 0);

                $satirTutar = $miktar * $fiyat;
                $satirKdv = $satirTutar * ($kdv / 100);
                $satirToplam = $satirTutar + $satirKdv;
                $genelToplam += $satirToplam;

                $this->repository->insertInvoiceItem($faturaId, (int) $stokId, $miktar, $fiyat, $kdv);
                $this->applyStockEffect((int) $stokId, $miktar);
                $this->repository->insertStockMovement((int) $stokId, (int) $cariId, $faturaNo, $miktar, $fiyat);
            }

            $this->repository->updateInvoiceTotal($faturaId, $genelToplam);
            $this->applyCariEffect((int) $cariId, $genelToplam);
            $this->repository->insertCariMovement((int) $cariId, $genelToplam, $faturaNo);

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    private function validateHeader(array $input): array
{
    $cariAdi = trim((string) ($input['cari_adi'] ?? ''));
    $faturaNo = trim((string) ($input['fatura_no'] ?? ''));
    $tarih = trim((string) ($input['tarih'] ?? ''));
    $vadeTarihi = trim((string) ($input['vade_tarihi'] ?? ''));

    if ($cariAdi === '' || $faturaNo === '' || $tarih === '') {
        throw new RuntimeException('Cari adı, fatura numarası ve tarih zorunludur.');
    }

    if ($vadeTarihi === '') {
        $vadeTarihi = null;
    }

    return [$cariAdi, $faturaNo, $tarih, $vadeTarihi];
}

    private function extractLineInputs(array $input): array
    {
        return [
            $input['stok_kodu'] ?? [],
            $input['urun_adi'] ?? [],
            $input['miktar'] ?? [],
            $input['birim_fiyat'] ?? [],
            $input['kdv_orani'] ?? [],
        ];
    }
    
    private function resolveVadeTarihi(?string $manuelVadeTarihi, string $tarih, int $cariId): ?string
{
    if (!empty($manuelVadeTarihi)) {
        return $manuelVadeTarihi;
    }

    $cari = $this->repository->findCariById($cariId);
    $vadeGun = max(0, (int) ($cari['varsayilan_vade_gun'] ?? 0));

    if ($vadeGun <= 0) {
        return $tarih;
    }

    $date = \DateTimeImmutable::createFromFormat('Y-m-d', $tarih);
    if (!$date) {
        return $tarih;
    }

    return $date->modify('+' . $vadeGun . ' days')->format('Y-m-d');
}

    abstract protected function applyStockEffect(int $stokId, float $miktar): void;
    abstract protected function revertStockEffect(int $stokId, float $miktar): void;
    abstract protected function applyCariEffect(int $cariId, float $tutar): void;
    abstract protected function revertCariEffect(int $cariId, float $tutar): void;
    abstract protected function deleteCariMovementForInvoice(int $cariId, string $faturaNo): void;
    abstract protected function getNotFoundMessage(): string;
}
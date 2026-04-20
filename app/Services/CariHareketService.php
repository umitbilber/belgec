<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CariHareketRepositoryInterface;
use App\Interfaces\CariHareketServiceInterface;
use App\Interfaces\CariRepositoryInterface;

class CariHareketService implements CariHareketServiceInterface
{
    private CariHareketRepositoryInterface $repository;
    private CariRepositoryInterface $cariRepository;

    public function __construct(
    CariHareketRepositoryInterface $repository,
    CariRepositoryInterface $cariRepository
) {
    $this->repository = $repository;
    $this->cariRepository = $cariRepository;
}

    public function getReport(array $input): array
    {
        $filters = [
            'cari_id' => (int) ($input['cari_id'] ?? 0),
            'islem_tipi' => trim((string) ($input['islem_tipi'] ?? '')),
            'tarih_baslangic' => trim((string) ($input['tarih_baslangic'] ?? '')),
            'tarih_bitis' => trim((string) ($input['tarih_bitis'] ?? '')),
        ];

        if (!in_array($filters['islem_tipi'], ['tahsilat', 'tediye', 'duzeltme'], true)) {
            $filters['islem_tipi'] = '';
        }

        $satirlar = $this->repository->getFiltered($filters);

        $ozet = [
            'kayit_sayisi' => count($satirlar),
            'toplam_tahsilat' => 0.0,
            'toplam_tediye' => 0.0,
            'toplam_duzeltme' => 0.0,
        ];

        foreach ($satirlar as $row) {
            $tutar = (float) ($row['tutar'] ?? 0);
            $tip = (string) ($row['islem_tipi'] ?? '');

            if ($tip === 'tahsilat') {
                $ozet['toplam_tahsilat'] += $tutar;
            } elseif ($tip === 'tediye') {
                $ozet['toplam_tediye'] += $tutar;
            } elseif ($tip === 'duzeltme') {
                $ozet['toplam_duzeltme'] += $tutar;
            }
        }

        return [
            'filters' => $filters,
            'satirlar' => $satirlar,
            'ozet' => $ozet,
        ];
    }
    public function getById(int $id): ?array
{
    return $this->repository->findById($id);
}

public function update(int $id, array $input): void
{
    $mevcut = $this->repository->findById($id);
    if (!$mevcut) {
        throw new \RuntimeException('Cari hareket kaydı bulunamadı.');
    }

    $data = $this->validateMovementInput($input);

    $this->repository->beginTransaction();

    try {
        $this->revertMovementBalanceEffect($mevcut);

        $this->repository->updateMovement($id, $data);

        $guncel = $this->repository->findById($id);
        if (!$guncel) {
            throw new \RuntimeException('Güncellenmiş cari hareket kaydı okunamadı.');
        }

        $this->applyMovementBalanceEffect($guncel);
        $this->rebuildInvoicePaymentsFromMovements();

        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}

public function delete(int $id): void
{
    $mevcut = $this->repository->findById($id);
    if (!$mevcut) {
        throw new \RuntimeException('Cari hareket kaydı bulunamadı.');
    }

    $this->repository->beginTransaction();

    try {
        $this->revertMovementBalanceEffect($mevcut);
        $this->repository->deleteMovement($id);
        $this->rebuildInvoicePaymentsFromMovements();

        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}

private function validateMovementInput(array $input): array
{
    $cariId = (int) ($input['cari_id'] ?? 0);
    $islemTipi = trim((string) ($input['islem_tipi'] ?? ''));
    $tutar = (float) ($input['tutar'] ?? 0);
    $aciklama = trim((string) ($input['aciklama'] ?? ''));
    $tarih = trim((string) ($input['tarih'] ?? ''));

    if ($cariId <= 0) {
        throw new \RuntimeException('Geçerli bir cari seçilmelidir.');
    }

    if (!in_array($islemTipi, ['tahsilat', 'tediye', 'duzeltme'], true)) {
        throw new \RuntimeException('Geçersiz işlem tipi.');
    }

    if ($tutar <= 0) {
        throw new \RuntimeException('Tutar sıfırdan büyük olmalıdır.');
    }

    if ($aciklama === '') {
        throw new \RuntimeException('Açıklama alanı zorunludur.');
    }

    if ($tarih === '') {
        $tarih = date('Y-m-d H:i:s');
    } elseif (strlen($tarih) === 10) {
        $tarih .= ' 00:00:00';
    }

    return [
        'cari_id' => $cariId,
        'islem_tipi' => $islemTipi,
        'tutar' => $tutar,
        'aciklama' => $aciklama,
        'tarih' => $tarih,
    ];
}

private function revertMovementBalanceEffect(array $movement): void
{
    $cariId = (int) ($movement['cari_id'] ?? 0);
    $type = (string) ($movement['islem_tipi'] ?? '');
    $amount = (float) ($movement['tutar'] ?? 0);

    if ($cariId <= 0 || $amount <= 0) {
        return;
    }

    if ($type === 'tahsilat') {
        $this->cariRepository->increaseBalance($cariId, $amount);
    } elseif ($type === 'tediye') {
        $this->cariRepository->decreaseBalance($cariId, $amount);
    } elseif ($type === 'duzeltme') {
        $this->cariRepository->decreaseBalance($cariId, $amount);
    }
}

private function applyMovementBalanceEffect(array $movement): void
{
    $cariId = (int) ($movement['cari_id'] ?? 0);
    $type = (string) ($movement['islem_tipi'] ?? '');
    $amount = (float) ($movement['tutar'] ?? 0);

    if ($cariId <= 0 || $amount <= 0) {
        return;
    }

    if ($type === 'tahsilat') {
        $this->cariRepository->decreaseBalance($cariId, $amount);
    } elseif ($type === 'tediye') {
        $this->cariRepository->increaseBalance($cariId, $amount);
    } elseif ($type === 'duzeltme') {
        $this->cariRepository->increaseBalance($cariId, $amount);
    }
}

private function rebuildInvoicePaymentsFromMovements(): void
{
    $this->cariRepository->resetAllInvoicePaidAmounts();

    $hareketler = $this->cariRepository->getAllPaymentMovements();

    foreach ($hareketler as $hareket) {
        $cariId = (int) ($hareket['cari_id'] ?? 0);
        $type = (string) ($hareket['islem_tipi'] ?? '');
        $amount = (float) ($hareket['tutar'] ?? 0);

        if ($cariId <= 0 || $amount <= 0 || !in_array($type, ['tahsilat', 'tediye'], true)) {
            continue;
        }

        $invoiceType = $type === 'tahsilat' ? 'satis' : 'alis';
        $remaining = $amount;

        $openInvoices = $this->cariRepository->getOpenInvoicesForCariAndType($cariId, $invoiceType);

        foreach ($openInvoices as $invoice) {
            if ($remaining <= 0) {
                break;
            }

            $acikTutar = max(0, (float) ($invoice['acik_tutar'] ?? 0));
            if ($acikTutar <= 0) {
                continue;
            }

            $uygulanacakTutar = min($remaining, $acikTutar);
            if ($uygulanacakTutar <= 0) {
                continue;
            }

            $this->cariRepository->increaseInvoicePaidAmount((int) ($invoice['id'] ?? 0), $uygulanacakTutar);
            $remaining -= $uygulanacakTutar;
        }
    }

    $this->cariRepository->clampInvoicePaidAmounts();
}
}
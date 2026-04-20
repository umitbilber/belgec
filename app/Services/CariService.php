<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\CariRepositoryInterface;
use App\Interfaces\CariServiceInterface;

class CariService implements CariServiceInterface
{
    private CariRepositoryInterface $repository;

    public function __construct(CariRepositoryInterface $repository)
{
    $this->repository = $repository;
}

    public function getAll(): array
{
    $cariler = $this->repository->all();

    foreach ($cariler as &$cari) {
        $cari['hareketler'] = $this->repository->getMovementsByCariId((int) $cari['id']);
    }

    unset($cari);

    return $cariler;
}

    public function getById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function getMovements(int $cariId): array
    {
        return $this->repository->getMovementsByCariId($cariId);
    }

    public function create(array $input): void
    {
        $data = [
    'ad_soyad' => trim((string) ($input['ad_soyad'] ?? '')),
    'telefon' => trim((string) ($input['telefon'] ?? '')),
    'eposta' => trim((string) ($input['eposta'] ?? '')),
    'adres' => trim((string) ($input['adres'] ?? '')),
    'vergi_no' => trim((string) ($input['vergi_no'] ?? '')),
    'bakiye' => !empty($input['bakiye']) ? (float) $input['bakiye'] : 0.00,
    'varsayilan_vade_gun' => max(0, (int) ($input['varsayilan_vade_gun'] ?? 0)),
];

        if ($data['ad_soyad'] === '') {
            throw new RuntimeException('Cari unvanı boş bırakılamaz.');
        }

        $this->repository->create($data);
    }

        public function update(int $id, array $input): void
    {
        $data = [
    'ad_soyad' => trim((string) ($input['ad_soyad'] ?? '')),
    'telefon' => trim((string) ($input['telefon'] ?? '')),
    'eposta' => trim((string) ($input['eposta'] ?? '')),
    'adres' => trim((string) ($input['adres'] ?? '')),
    'vergi_no' => trim((string) ($input['vergi_no'] ?? '')),
    'varsayilan_vade_gun' => max(0, (int) ($input['varsayilan_vade_gun'] ?? 0)),
];

        if ($data['ad_soyad'] === '') {
            throw new RuntimeException('Cari unvanı boş bırakılamaz.');
        }

        $mevcutCari = $this->repository->findById($id);

        if (!$mevcutCari) {
            throw new RuntimeException('Cari bulunamadı.');
        }

        $yeniBakiyeGirildi = isset($input['duzeltilmis_bakiye']) && $input['duzeltilmis_bakiye'] !== '';
        $yeniBakiye = $yeniBakiyeGirildi ? (float) $input['duzeltilmis_bakiye'] : (float) ($mevcutCari['bakiye'] ?? 0);
        $eskiBakiye = (float) ($mevcutCari['bakiye'] ?? 0);
        $fark = $yeniBakiye - $eskiBakiye;

        $this->repository->beginTransaction();

        try {
            $this->repository->update($id, $data);

            if ($yeniBakiyeGirildi && abs($fark) > 0.0001) {
                $this->repository->setBalance($id, $yeniBakiye);

                $this->repository->insertMovement([
                    'cari_id' => $id,
                    'islem_tipi' => 'duzeltme',
                    'tutar' => abs($fark),
                    'aciklama' => 'Bakiye düzeltildi. Eski bakiye: '
                        . number_format($eskiBakiye, 2, ',', '.')
                        . ' TL | Yeni bakiye: '
                        . number_format($yeniBakiye, 2, ',', '.')
                        . ' TL',
                    'tarih' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
{
    $cari = $this->repository->findById($id);

    if (!$cari) {
        throw new RuntimeException('Cari bulunamadı.');
    }

    if ($this->repository->hasLinkedRecords($id)) {
        throw new RuntimeException('Bu cariye bağlı fatura, teklif veya stok hareketi bulunduğu için silinemez.');
    }

    $this->repository->beginTransaction();

    try {
        $this->repository->deleteMovementsByCariId($id);
        $this->repository->delete($id);
        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}

    public function recordMovement(array $input): void
{
    $cariId = (int) ($input['cari_id'] ?? 0);
    $type = trim((string) ($input['islem_tipi'] ?? ''));
    $amount = (float) ($input['tutar'] ?? 0);
    $description = trim((string) ($input['aciklama'] ?? ''));
    $date = trim((string) ($input['tarih'] ?? ''));

    if ($cariId <= 0) {
        throw new RuntimeException('Geçersiz cari seçimi.');
    }

    if (!in_array($type, ['tahsilat', 'tediye'], true)) {
        throw new RuntimeException('Geçersiz işlem tipi.');
    }

    if ($amount <= 0) {
        throw new RuntimeException('Tutar sıfırdan büyük olmalıdır.');
    }

    if ($description === '') {
        throw new RuntimeException('Açıklama alanı boş bırakılamaz.');
    }

    $datetime = $date !== '' ? $date . ' 00:00:00' : date('Y-m-d H:i:s');

    $this->repository->beginTransaction();

    try {
        $this->repository->insertMovement([
            'cari_id' => $cariId,
            'islem_tipi' => $type,
            'tutar' => $amount,
            'aciklama' => $description,
            'tarih' => $datetime,
        ]);

        if ($type === 'tahsilat') {
            $this->repository->decreaseBalance($cariId, $amount);
        } else {
            $this->repository->increaseBalance($cariId, $amount);
        }

        $this->autoApplyMovementToInvoices($cariId, $type, $amount);

        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}
private function autoApplyMovementToInvoices(int $cariId, string $movementType, float $amount): void
{
    $invoiceType = $movementType === 'tahsilat' ? 'satis' : 'alis';
    $remaining = max(0, $amount);

    if ($remaining <= 0) {
        return;
    }

    $openInvoices = $this->repository->getOpenInvoicesForCariAndType($cariId, $invoiceType);

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

        $this->repository->increaseInvoicePaidAmount((int) $invoice['id'], $uygulanacakTutar);
        $remaining -= $uygulanacakTutar;
    }

    $this->repository->clampInvoicePaidAmounts();
}
}
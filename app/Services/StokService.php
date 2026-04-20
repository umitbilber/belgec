<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\StokServiceInterface;
use App\Interfaces\StokRepositoryInterface;

class StokService implements StokServiceInterface
{
    private StokRepositoryInterface $repository;

    public function __construct(StokRepositoryInterface $repository)
{
    $this->repository = $repository;
}

    public function getAll(): array
{
    $stoklar = $this->repository->all();

    foreach ($stoklar as &$stok) {
        $stok['hareketler'] = $this->repository->getMovementsByStokId((int) $stok['id']);
    }

    unset($stok);

    return $stoklar;
}
public function getAllBasic(): array
{
    return $this->repository->all();
}

    public function getById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function getMovements(int $stokId): array
    {
        return $this->repository->getMovementsByStokId($stokId);
    }

    public function create(array $input): void
    {
        $data = [
            'urun_adi' => trim((string) ($input['urun_adi'] ?? '')),
            'stok_kodu' => trim((string) ($input['stok_kodu'] ?? '')),
            'birim' => trim((string) ($input['birim'] ?? 'Adet')),
            'stok_miktari' => !empty($input['stok_miktari']) ? (float) $input['stok_miktari'] : 0.0,
        ];

        if ($data['urun_adi'] === '') {
            throw new RuntimeException('Ürün adı boş bırakılamaz.');
        }

        if ($data['stok_kodu'] === '') {
            throw new RuntimeException('Stok kodu boş bırakılamaz.');
        }

        $this->repository->beginTransaction();

        try {
            $stokId = $this->repository->create($data);

            if ($data['stok_miktari'] > 0) {
                $this->repository->insertMovement([
                    'stok_id' => $stokId,
                    'islem_tipi' => 'devir',
                    'miktar' => $data['stok_miktari'],
                    'aciklama' => 'Açılış / Devir Bakiyesi',
                ]);
            }

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $input): void
    {
        $mevcut = $this->repository->findById($id);

        if (!$mevcut) {
            throw new RuntimeException('Stok kartı bulunamadı.');
        }

        $yeniMiktar = (float) ($input['stok_miktari'] ?? 0);
        $eskiMiktar = (float) ($mevcut['stok_miktari'] ?? 0);
        $fark = $yeniMiktar - $eskiMiktar;

        $data = [
            'urun_adi' => trim((string) ($input['urun_adi'] ?? '')),
            'stok_kodu' => trim((string) ($input['stok_kodu'] ?? '')),
            'birim' => trim((string) ($input['birim'] ?? 'Adet')),
            'stok_miktari' => $yeniMiktar,
        ];

        if ($data['urun_adi'] === '') {
            throw new RuntimeException('Ürün adı boş bırakılamaz.');
        }

        if ($data['stok_kodu'] === '') {
            throw new RuntimeException('Stok kodu boş bırakılamaz.');
        }

        $this->repository->beginTransaction();

        try {
            $this->repository->update($id, $data);

            if ((float) $fark !== 0.0) {
                $this->repository->insertMovement([
                    'stok_id' => $id,
                    'islem_tipi' => 'duzeltme',
                    'miktar' => $fark,
                    'aciklama' => 'Elle Stok Düzeltmesi (Panelden)',
                ]);
            }

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }
    
    public function getFiyatGecmisi(string $stokKodu, string $urunAdi): array
{
    $stok = $this->repository->findByKodOrAd($stokKodu, $urunAdi);
    if (!$stok) return [];
    return $this->repository->getFiyatGecmisi((int) $stok['id']);
}

    public function delete(int $id): void
{
    $stok = $this->repository->findById($id);

    if (!$stok) {
        throw new RuntimeException('Stok kartı bulunamadı.');
    }

    if ($this->repository->hasLinkedRecords($id)) {
        throw new RuntimeException('Bu stok kartına bağlı fatura kalemi veya stok hareketi bulunduğu için silinemez.');
    }

    $this->repository->beginTransaction();

    try {
        $this->repository->deleteMovementsByStokId($id);
        $this->repository->delete($id);
        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}
}
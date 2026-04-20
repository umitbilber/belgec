<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\KullaniciServiceInterface;
use App\Interfaces\KullaniciRepositoryInterface;

class KullaniciService implements KullaniciServiceInterface
{
    private KullaniciRepositoryInterface $repository;

    public function __construct(KullaniciRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function girisDogrula(string $kullaniciAdi, string $sifre): ?array
    {
        $kullanici = $this->repository->findByKullaniciAdi($kullaniciAdi);
        if (!$kullanici) return null;
        if (!password_verify($sifre, (string) $kullanici['sifre_hash'])) return null;
        return $kullanici;
    }

    public function create(array $input): void
    {
        $ad            = trim((string) ($input['ad'] ?? ''));
        $kullaniciAdi  = trim((string) ($input['kullanici_adi'] ?? ''));
        $sifre         = trim((string) ($input['sifre'] ?? ''));
        $rol           = in_array($input['rol'] ?? '', ['yonetici', 'kullanici'], true) ? $input['rol'] : 'kullanici';

        if ($ad === '')           throw new RuntimeException('Ad boş bırakılamaz.');
        if ($kullaniciAdi === '') throw new RuntimeException('Kullanıcı adı boş bırakılamaz.');
        if ($sifre === '')        throw new RuntimeException('Şifre boş bırakılamaz.');
        if (strlen($sifre) < 6)  throw new RuntimeException('Şifre en az 6 karakter olmalıdır.');

        $this->repository->create([
            'ad'           => $ad,
            'kullanici_adi' => $kullaniciAdi,
            'sifre_hash'   => password_hash($sifre, PASSWORD_BCRYPT),
            'rol'          => $rol,
            'aktif'        => 1,
        ]);
    }

    public function update(int $id, array $input): void
    {
        $mevcut = $this->repository->findById($id);
        if (!$mevcut) throw new RuntimeException('Kullanıcı bulunamadı.');

        $ad           = trim((string) ($input['ad'] ?? ''));
        $kullaniciAdi = trim((string) ($input['kullanici_adi'] ?? ''));
        $rol          = in_array($input['rol'] ?? '', ['yonetici', 'kullanici'], true) ? $input['rol'] : 'kullanici';
        $sifre        = trim((string) ($input['sifre'] ?? ''));

        if ($ad === '')           throw new RuntimeException('Ad boş bırakılamaz.');
        if ($kullaniciAdi === '') throw new RuntimeException('Kullanıcı adı boş bırakılamaz.');

        $data = [
            'ad'            => $ad,
            'kullanici_adi' => $kullaniciAdi,
            'rol'           => $rol,
            'aktif'         => (int) ($input['aktif'] ?? 1),
        ];

        if ($sifre !== '') {
            if (strlen($sifre) < 6) throw new RuntimeException('Şifre en az 6 karakter olmalıdır.');
            $data['sifre_hash'] = password_hash($sifre, PASSWORD_BCRYPT);
        }

        $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $mevcut = $this->repository->findById($id);
        if (!$mevcut) throw new RuntimeException('Kullanıcı bulunamadı.');
        $this->repository->delete($id);
    }

    public function getIzinler(int $kullaniciId): array
    {
        return $this->repository->getIzinler($kullaniciId);
    }

    public function setIzinler(int $kullaniciId, array $izinler): void
    {
        $this->repository->setIzinler($kullaniciId, $izinler);
    }

    public function hasIzin(int $kullaniciId, string $izin): bool
    {
        $izinler = $this->repository->getIzinler($kullaniciId);
        return in_array($izin, $izinler, true);
    }
}
<?php

declare(strict_types=1);

namespace App\Interfaces;

interface KullaniciServiceInterface
{
    public function getAll(): array;
    public function findById(int $id): ?array;
    public function girisDogrula(string $kullaniciAdi, string $sifre): ?array;
    public function create(array $input): void;
    public function update(int $id, array $input): void;
    public function delete(int $id): void;
    public function getIzinler(int $kullaniciId): array;
    public function setIzinler(int $kullaniciId, array $izinler): void;
    public function hasIzin(int $kullaniciId, string $izin): bool;
}
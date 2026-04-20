<?php

declare(strict_types=1);

namespace App\Interfaces;

interface KullaniciRepositoryInterface
{
    public function all(): array;
    public function findById(int $id): ?array;
    public function findByKullaniciAdi(string $kullaniciAdi): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
    public function getIzinler(int $kullaniciId): array;
    public function setIzinler(int $kullaniciId, array $izinler): void;
}
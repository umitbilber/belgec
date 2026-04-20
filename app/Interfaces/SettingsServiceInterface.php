<?php

declare(strict_types=1);

namespace App\Interfaces;

interface SettingsServiceInterface
{
    public function getPath(): string;
    public function exists(): bool;
    public function all(): array;
    public function isInstalled(): bool;
    public function save(array $data): void;
    public function install(array $input): void;
    public function verifyAdminPassword(string $password): bool;
    public function updateGeneralSettings(array $input): void;
    public function addFavorite(string $modul): void;
    public function removeFavorite(string $modul): void;
}
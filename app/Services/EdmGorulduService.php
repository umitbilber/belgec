<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\EdmGorulduServiceInterface;

class EdmGorulduService implements EdmGorulduServiceInterface
{
    private string $dosyaYolu;

    public function __construct()
    {
        $this->dosyaYolu = BASE_PATH . '/edm_goruldu.json';
    }

    public function gorulduler(): array
    {
        if (!file_exists($this->dosyaYolu)) {
            return [];
        }

        $raw = file_get_contents($this->dosyaYolu);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    public function gorulduMu(string $uuid): bool
    {
        return in_array($uuid, $this->gorulduler(), true);
    }

    public function gorulduIsaretle(string $uuid): void
    {
        $liste = $this->gorulduler();

        if (!in_array($uuid, $liste, true)) {
            $liste[] = $uuid;
            file_put_contents(
                $this->dosyaYolu,
                json_encode($liste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
        }
    }

    public function topluGorulduIsaretle(array $uuidler): void
    {
        $liste = $this->gorulduler();
        $degisti = false;

        foreach ($uuidler as $uuid) {
            if (!in_array($uuid, $liste, true)) {
                $liste[] = $uuid;
                $degisti = true;
            }
        }

        if ($degisti) {
            file_put_contents(
                $this->dosyaYolu,
                json_encode($liste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
        }
    }
}
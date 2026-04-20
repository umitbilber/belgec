<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class YedekService
{
    private string $dbPath;
    private string $backupDir;

    public function __construct()
    {
        $config          = require BASE_PATH . '/config/database.php';
        $this->dbPath    = $config['connections']['sqlite']['database'];
        $this->backupDir = BASE_PATH . '/storage/backups';
    }

    public function manuelYedekAl(): string
    {
        $this->dizinOlustur();

        $dosyaAdi = 'belgec_' . date('Y-m-d_H-i-s') . '.sqlite';
        $hedef    = $this->backupDir . '/' . $dosyaAdi;

        if (!copy($this->dbPath, $hedef)) {
            throw new RuntimeException('Yedek dosyası oluşturulamadı.');
        }

        return $hedef;
    }

    public function otomatikYedekAl(int $maxYedek = 5): void
{
    $this->dizinOlustur();

    $dosyaAdi = 'belgec_' . date('Y-m-d_H-i-s') . '.sqlite';
    $hedef    = $this->backupDir . '/' . $dosyaAdi;

    if (!copy($this->dbPath, $hedef)) {
        return;
    }

    $this->eskiYedekleriSil($maxYedek);

    // Bildirim flag'i bırak
    $flag = BASE_PATH . '/storage/backups/son_otomatik_bildirim.json';
    file_put_contents($flag, json_encode([
        'dosya'  => $dosyaAdi,
        'zaman'  => date('d.m.Y H:i'),
        'okundu' => false,
    ]), LOCK_EX);
}

    public function yedekleriListele(): array
    {
        $this->dizinOlustur();

        $dosyalar = glob($this->backupDir . '/belgec_*.sqlite') ?: [];
        rsort($dosyalar);

        return array_map(function (string $yol) {
            return [
                'dosya_adi' => basename($yol),
                'boyut'     => $this->boyutFormatla((int) filesize($yol)),
                'tarih'     => date('d.m.Y H:i:s', (int) filemtime($yol)),
            ];
        }, $dosyalar);
    }

    public function zamanKontrol(string $siklık, string $sonYedekZamani): bool
    {
        if ($sonYedekZamani === '') return true;

        $son  = strtotime($sonYedekZamani);
        $simdi = time();

        return match ($siklık) {
            'gunluk'  => ($simdi - $son) >= 86400,
            'haftalik' => ($simdi - $son) >= 604800,
            'aylik'   => ($simdi - $son) >= 2592000,
            default   => false,
        };
    }

    public function yedekIndir(string $dosyaAdi): string
    {
        $yol = $this->backupDir . '/' . basename($dosyaAdi);

        if (!file_exists($yol) || !str_starts_with(basename($yol), 'belgec_')) {
            throw new RuntimeException('Yedek dosyası bulunamadı.');
        }

        return $yol;
    }

    private function eskiYedekleriSil(int $maxYedek): void
    {
        $dosyalar = glob($this->backupDir . '/belgec_*.sqlite') ?: [];
        rsort($dosyalar);

        $silinecekler = array_slice($dosyalar, $maxYedek);
        foreach ($silinecekler as $dosya) {
            @unlink($dosya);
        }
    }

    private function dizinOlustur(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    private function boyutFormatla(int $bayt): string
    {
        if ($bayt >= 1048576) return round($bayt / 1048576, 1) . ' MB';
        if ($bayt >= 1024)    return round($bayt / 1024, 1) . ' KB';
        return $bayt . ' B';
    }
}
<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\CariPrintServiceInterface;
use App\Interfaces\CariServiceInterface;
use App\Interfaces\SettingsServiceInterface;

class CariPrintService implements CariPrintServiceInterface
{
    private CariServiceInterface $cariService;
private SettingsServiceInterface $settingsService;

public function __construct(
    CariServiceInterface $cariService,
    SettingsServiceInterface $settingsService
) {
    $this->cariService = $cariService;
    $this->settingsService = $settingsService;
}

    public function getPrintData(int $cariId): array
    {
        $cari = $this->cariService->getById($cariId);

        if (!$cari) {
            throw new RuntimeException('Cari bulunamadı.');
        }

        $hareketler = $this->cariService->getMovements($cariId);
        $ayarlar = $this->settingsService->all();

        return [
            'ayarlar' => $ayarlar,
            'cari' => $cari,
            'hareketler' => $hareketler,
        ];
    }

    public function getBakiyeDurumu(float $bakiye): array
{
    if ($bakiye > 0) {
        return [
            'metin' => 'Firmamıza borcunuz bulunmaktadır.',
            'etiket' => 'B',
            'renk' => '#16a34a',
        ];
    }

    if ($bakiye < 0) {
        return [
            'metin' => 'Firmamızdan alacağınız bulunmaktadır.',
            'etiket' => 'A',
            'renk' => '#d61f1f',
        ];
    }

    return [
        'metin' => 'Cari hesabınız dengededir.',
        'etiket' => '',
        'renk' => '#444444',
    ];
}

    public function formatIslemTipi(string $tip): string
    {
        return match ($tip) {
            'tahsilat' => 'Tahsilat',
            'tediye' => 'Tediye',
            'alis' => 'Alış Faturası',
            'satis' => 'Satış Faturası',
            default => ucfirst($tip),
        };
    }
}
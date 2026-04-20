<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\TeklifPrintServiceInterface;
use App\Interfaces\TeklifServiceInterface;
use App\Interfaces\SettingsServiceInterface;

class TeklifPrintService implements TeklifPrintServiceInterface
{
    private TeklifServiceInterface $teklifService;
private SettingsServiceInterface $settingsService;

public function __construct(
    TeklifServiceInterface $teklifService,
    SettingsServiceInterface $settingsService
) {
    $this->teklifService = $teklifService;
    $this->settingsService = $settingsService;
}

    public function getPrintData(int $teklifId): array
{
    $teklif = $this->teklifService->getById($teklifId);

    if (!$teklif) {
        throw new \RuntimeException('Teklif bulunamadı.');
    }

    $kalemler = $this->teklifService->getItems($teklifId);
    $ayarlar = $this->settingsService->all();

    $toplamlar = [
    'TL' => 0.0,
    'USD' => 0.0,
    'EUR' => 0.0,
];

foreach ($kalemler as $kalem) {
    $pb = trim((string) ($kalem['para_birimi'] ?? ''));
    if ($pb === '') {
        $pb = 'TL';
    }

    $satirToplam = (float) ($kalem['satir_toplam'] ?? (((float) ($kalem['miktar'] ?? 0)) * ((float) ($kalem['birim_fiyat'] ?? 0))));

    if (!isset($toplamlar[$pb])) {
        $toplamlar[$pb] = 0.0;
    }

    $toplamlar[$pb] += $satirToplam;
}

$filtreliToplamlar = array_filter(
    $toplamlar,
    fn ($tutar) => abs((float) $tutar) > 0.00001
);

if (empty($filtreliToplamlar)) {
    $filtreliToplamlar = [
        (string) ($teklif['para_birimi'] ?? 'TL') => (float) ($teklif['genel_toplam'] ?? 0),
    ];
}

    return [
        'ayarlar' => $ayarlar,
        'teklif' => $teklif,
        'kalemler' => $kalemler,
        'toplamlar' => $filtreliToplamlar,
    ];
}
}
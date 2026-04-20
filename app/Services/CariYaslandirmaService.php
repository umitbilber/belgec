<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CariYaslandirmaRepositoryInterface;
use App\Interfaces\CariYaslandirmaServiceInterface;

class CariYaslandirmaService implements CariYaslandirmaServiceInterface
{
    private CariYaslandirmaRepositoryInterface $repository;

    public function __construct(CariYaslandirmaRepositoryInterface $repository)
{
    $this->repository = $repository;
}

    public function getReport(?string $tip = null, int $cariId = 0): array
{
    $rows = $this->repository->getOpenInvoices($tip, $cariId);
    $bugun = date('Y-m-d');

    $ozet = [
        'toplam_acik' => 0.0,
        'vadesi_gelmemis' => 0.0,
        'gun_0_30' => 0.0,
        'gun_31_60' => 0.0,
        'gun_61_90' => 0.0,
        'gun_90_uzeri' => 0.0,
    ];

    $rapor = [];

    foreach ($rows as $row) {
        $vadeTarihi = (string) ($row['vade_tarihi'] ?? '');
        $acikTutar = (float) ($row['acik_tutar'] ?? 0);
        $yaslandirma = $this->resolveBucket($vadeTarihi, $bugun);
        $gecikmeGunu = $this->resolveDelayDays($vadeTarihi, $bugun);

        $row['acik_tutar'] = $acikTutar;
        $row['yaslandirma_kovasi'] = $yaslandirma;
        $row['gecikme_gun'] = $gecikmeGunu;

        $ozet['toplam_acik'] += $acikTutar;

        if ($yaslandirma === 'Vadesi Gelmemiş') {
            $ozet['vadesi_gelmemis'] += $acikTutar;
        } elseif ($yaslandirma === '0-30 Gün') {
            $ozet['gun_0_30'] += $acikTutar;
        } elseif ($yaslandirma === '31-60 Gün') {
            $ozet['gun_31_60'] += $acikTutar;
        } elseif ($yaslandirma === '61-90 Gün') {
            $ozet['gun_61_90'] += $acikTutar;
        } else {
            $ozet['gun_90_uzeri'] += $acikTutar;
        }

        $rapor[] = $row;
    }

    return [
        'bugun' => $bugun,
        'tip' => $tip,
        'cari_id' => $cariId,
        'ozet' => $ozet,
        'satirlar' => $rapor,
    ];
}

    private function resolveBucket(string $vadeTarihi, string $bugun): string
    {
        $fark = $this->dateDiffInDays($vadeTarihi, $bugun);

        if ($fark <= 0) {
            return 'Vadesi Gelmemiş';
        }

        if ($fark <= 30) {
            return '0-30 Gün';
        }

        if ($fark <= 60) {
            return '31-60 Gün';
        }

        if ($fark <= 90) {
            return '61-90 Gün';
        }

        return '90+ Gün';
    }

    private function resolveDelayDays(string $vadeTarihi, string $bugun): int
    {
        $fark = $this->dateDiffInDays($vadeTarihi, $bugun);
        return max(0, $fark);
    }

    private function dateDiffInDays(string $from, string $to): int
    {
        $fromObj = \DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $toObj = \DateTimeImmutable::createFromFormat('Y-m-d', $to);

        if (!$fromObj || !$toObj) {
            return 0;
        }

        $seconds = $toObj->getTimestamp() - $fromObj->getTimestamp();
        return (int) floor($seconds / 86400);
    }
}
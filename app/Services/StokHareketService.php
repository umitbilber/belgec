<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\StokHareketServiceInterface;
use App\Interfaces\StokHareketRepositoryInterface;

class StokHareketService implements StokHareketServiceInterface
{
    private StokHareketRepositoryInterface $repository;

    public function __construct(StokHareketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getReport(array $input): array
    {
        $filters = [
            'stok_id'         => (int) ($input['stok_id'] ?? 0),
            'islem_tipi'      => trim((string) ($input['islem_tipi'] ?? '')),
            'tarih_baslangic' => trim((string) ($input['tarih_baslangic'] ?? '')),
            'tarih_bitis'     => trim((string) ($input['tarih_bitis'] ?? '')),
        ];

        if (!in_array($filters['islem_tipi'], ['alis', 'satis', 'devir', 'duzeltme'], true)) {
            $filters['islem_tipi'] = '';
        }

        $satirlar = $this->repository->getFiltered($filters);
        // Bakiye hesabı için ASC sırala
$satirlarAsc = array_reverse($satirlar);

        // Stok bazında kümülatif bakiye hesapla
        $bakiyeler = [];
        foreach ($satirlarAsc as &$row) {
            $stokId  = (int) ($row['stok_id'] ?? 0);
            $miktar  = (float) ($row['miktar'] ?? 0);
            $tip     = strtolower((string) ($row['islem_tipi'] ?? ''));

            if (!isset($bakiyeler[$stokId])) {
                $bakiyeler[$stokId] = 0.0;
            }

            // Giriş/çıkış ayrımı
            if (in_array($tip, ['alis', 'devir'], true)) {
                $giris  = abs($miktar);
                $cikis  = 0.0;
            } elseif ($tip === 'satis') {
                $giris  = 0.0;
                $cikis  = abs($miktar);
            } else {
                // duzeltme: pozitif = giriş, negatif = çıkış
                $giris  = $miktar > 0 ? $miktar : 0.0;
                $cikis  = $miktar < 0 ? abs($miktar) : 0.0;
            }

            $bakiyeler[$stokId] += $giris - $cikis;

            $row['giris']  = $giris;
            $row['cikis']  = $cikis;
            $row['bakiye'] = $bakiyeler[$stokId];
        }
        
        // Bakiyeleri id'ye göre indeksle
$bakiyeMap = [];
foreach ($satirlarAsc as $r) {
    $bakiyeMap[(int)$r['id']] = ['giris' => $r['giris'], 'cikis' => $r['cikis'], 'bakiye' => $r['bakiye']];
}

// DESC sıralı $satirlar'a bakiyeleri uygula
foreach ($satirlar as &$row) {
    $id = (int)$row['id'];
    $row['giris']  = $bakiyeMap[$id]['giris']  ?? 0.0;
    $row['cikis']  = $bakiyeMap[$id]['cikis']  ?? 0.0;
    $row['bakiye'] = $bakiyeMap[$id]['bakiye'] ?? 0.0;
}
unset($row);

        // Özet
        $toplamGiris = 0.0;
        $toplamCikis = 0.0;
        foreach ($satirlar as $row) {
            $toplamGiris += (float) ($row['giris'] ?? 0);
            $toplamCikis += (float) ($row['cikis'] ?? 0);
        }

        return [
            'filters' => $filters,
            'satirlar' => $satirlar,
            'ozet' => [
                'kayit_sayisi'  => count($satirlar),
                'toplam_giris'  => $toplamGiris,
                'toplam_cikis'  => $toplamCikis,
                'net_degisim'   => $toplamGiris - $toplamCikis,
            ],
        ];
    }
}
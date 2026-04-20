<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\SettingsServiceInterface;
use RuntimeException;
use App\Interfaces\TeklifRepositoryInterface;
use App\Interfaces\TeklifServiceInterface;

class TeklifService implements TeklifServiceInterface
{
    private TeklifRepositoryInterface $repository;
    private SettingsServiceInterface $settingsService;
	
	private function buildCurrencyTotals(array $urunAdlari, array $miktarlar, array $fiyatlar, array $satirToplamlari, array $paraBirimleri): array
{
    $totals = [];

    for ($i = 0; $i < count($urunAdlari); $i++) {
        $urunAdi = trim((string) ($urunAdlari[$i] ?? ''));
        if ($urunAdi === '') {
            continue;
        }

        $pb = trim((string) ($paraBirimleri[$i] ?? 'TL'));
        if ($pb === '') {
            $pb = 'TL';
        }

        $miktar = (float) ($miktarlar[$i] ?? 0);
        $birimFiyat = (float) ($fiyatlar[$i] ?? 0);
        $satirToplam = $this->resolveLineTotal($miktar, $birimFiyat, $satirToplamlari[$i] ?? '');

        if (!isset($totals[$pb])) {
            $totals[$pb] = 0.0;
        }

        $totals[$pb] += $satirToplam;
    }

    return $totals;
}

private function resolveOfferHeaderCurrencyAndTotal(array $totals): array
{
    $filtered = array_filter($totals, fn ($tutar) => abs((float) $tutar) > 0.00001);

    if (count($filtered) === 1) {
        $currency = array_key_first($filtered);
        return [
            'para_birimi' => (string) $currency,
            'genel_toplam' => (float) $filtered[$currency],
        ];
    }

    if (count($filtered) > 1) {
        return [
            'para_birimi' => 'ÇOKLU',
            'genel_toplam' => 0.0,
        ];
    }

    return [
        'para_birimi' => 'TL',
        'genel_toplam' => 0.0,
    ];
}

private function resolveLineTotal(float $miktar, float $birimFiyat, mixed $satirToplamInput): float
{
    $satirToplamRaw = trim((string) ($satirToplamInput ?? ''));

    if ($satirToplamRaw !== '') {
        return (float) $satirToplamRaw;
    }

    return $miktar * $birimFiyat;
}

    public function __construct(
    TeklifRepositoryInterface $repository,
    SettingsServiceInterface $settingsService
) {
    $this->repository = $repository;
    $this->settingsService = $settingsService;
}
    
    private function normalizeLineInputs(array $input): array
{
    return [
        'urun_adi' => $input['urun_adi'] ?? [],
        'marka' => $input['marka'] ?? [],
        'miktar' => $input['miktar'] ?? [],
        'birim_fiyat' => $input['birim_fiyat'] ?? [],
        'satir_toplam' => $input['satir_toplam'] ?? [],
        'termin' => $input['termin'] ?? [],
        'kalem_para_birimi' => $input['kalem_para_birimi'] ?? [],
    ];
}

private function ensureAtLeastOneValidLine(array $urunAdlari): void
{
    foreach ($urunAdlari as $urunAdi) {
        if (trim((string) $urunAdi) !== '') {
            return;
        }
    }

    throw new RuntimeException('Teklif için en az bir ürün satırı girilmelidir.');
}

public function getAll(): array
{
    $teklifler = $this->repository->all();

    foreach ($teklifler as &$teklif) {
        $kalemler = $this->repository->getItems((int) $teklif['id']);
        $teklif['kalemler'] = $kalemler;

        $totals = [];
        foreach ($kalemler as $kalem) {
            $pb = trim((string) ($kalem['para_birimi'] ?? ''));
            if ($pb === '') {
                $pb = 'TL';
            }

            $satirToplam = (float) ($kalem['satir_toplam'] ?? (((float) ($kalem['miktar'] ?? 0)) * ((float) ($kalem['birim_fiyat'] ?? 0))));

            if (!isset($totals[$pb])) {
                $totals[$pb] = 0.0;
            }

            $totals[$pb] += $satirToplam;
        }

        $parcalar = [];
        foreach ($totals as $pb => $tutar) {
            $simge = $pb === 'USD' ? '$' : ($pb === 'EUR' ? '€' : '₺');
            $parcalar[] = number_format((float) $tutar, 2, ',', '.') . ' ' . $simge;
        }

        if (empty($parcalar)) {
            $simge = ($teklif['para_birimi'] ?? 'TL') === 'USD' ? '$' : (($teklif['para_birimi'] ?? 'TL') === 'EUR' ? '€' : '₺');
            $parcalar[] = number_format((float) ($teklif['genel_toplam'] ?? 0), 2, ',', '.') . ' ' . $simge;
        }

        $teklif['toplam_ozeti'] = implode(' + ', $parcalar);
        $teklif['teklif_notlari'] = (string) ($teklif['teklif_notlari'] ?? '');
    }

    unset($teklif);

    return $teklifler;
}

    public function getById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function getItems(int $teklifId): array
    {
        return $this->repository->getItems($teklifId);
    }

public function create(array $input): void
{
    $cariAdi = trim((string) ($input['cari_adi'] ?? ''));
    $teklifNo = trim((string) ($input['teklif_no'] ?? ''));
    $tarih = trim((string) ($input['tarih'] ?? ''));
    $teklifNotlari = trim((string) ($input['teklif_notlari'] ?? ''));

    if ($cariAdi === '' || $teklifNo === '' || $tarih === '') {
        throw new RuntimeException('Cari adı, teklif numarası ve tarih zorunludur.');
    }

    $ayarlar = $this->settingsService->all();
    if ($teklifNotlari === '') {
        $teklifNotlari = trim((string) ($ayarlar['varsayilan_teklif_sartlari'] ?? ''));
    }

    $lines = $this->normalizeLineInputs($input);

$urunAdlari = $lines['urun_adi'];
$markalar = $lines['marka'];
$miktarlar = $lines['miktar'];
$fiyatlar = $lines['birim_fiyat'];
$satirToplamlari = $lines['satir_toplam'];
$terminler = $lines['termin'];
$paraBirimleri = $lines['kalem_para_birimi'];

$this->ensureAtLeastOneValidLine($urunAdlari);

    $currencyTotals = $this->buildCurrencyTotals($urunAdlari, $miktarlar, $fiyatlar, $satirToplamlari, $paraBirimleri);
    $headerData = $this->resolveOfferHeaderCurrencyAndTotal($currencyTotals);

    $this->repository->beginTransaction();

    try {
        $cariId = $this->repository->findCariIdByName($cariAdi);
        if (!$cariId) {
            $cariId = $this->repository->createCari($cariAdi);
        }

        $teklifId = $this->repository->createOffer(
            $teklifNo,
            (int) $cariId,
            $tarih,
            $headerData['para_birimi'],
            $teklifNotlari
        );

        for ($i = 0; $i < count($urunAdlari); $i++) {
            $urunAdi = trim((string) ($urunAdlari[$i] ?? ''));
            if ($urunAdi === '') {
                continue;
            }

            $marka = trim((string) ($markalar[$i] ?? ''));
$miktar = (float) ($miktarlar[$i] ?? 0);
$birimFiyat = (float) ($fiyatlar[$i] ?? 0);
$satirToplam = $this->resolveLineTotal($miktar, $birimFiyat, $satirToplamlari[$i] ?? '');
$termin = trim((string) ($terminler[$i] ?? ''));
$kalemParaBirimi = trim((string) ($paraBirimleri[$i] ?? 'TL'));

if ($kalemParaBirimi === '') {
    $kalemParaBirimi = 'TL';
}

$this->repository->insertItem(
    $teklifId,
    $urunAdi,
    $marka,
    $miktar,
    $birimFiyat,
    $satirToplam,
    $termin,
    $kalemParaBirimi
);
        }

        $this->repository->updateOfferTotal($teklifId, (float) $headerData['genel_toplam']);

        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}

public function update(int $id, array $input): void
{
    $teklif = $this->repository->findById($id);

    if (!$teklif) {
        throw new RuntimeException('Teklif bulunamadı.');
    }

    $cariAdi = trim((string) ($input['cari_adi'] ?? ''));
    $teklifNo = trim((string) ($input['teklif_no'] ?? ''));
    $tarih = trim((string) ($input['tarih'] ?? ''));
    $teklifNotlari = trim((string) ($input['teklif_notlari'] ?? ''));

    if ($cariAdi === '' || $teklifNo === '' || $tarih === '') {
        throw new RuntimeException('Cari adı, teklif numarası ve tarih zorunludur.');
    }

    $ayarlar = (new SettingsService())->all();
    if ($teklifNotlari === '') {
        $teklifNotlari = trim((string) ($ayarlar['varsayilan_teklif_sartlari'] ?? ''));
    }

    $lines = $this->normalizeLineInputs($input);

$urunAdlari = $lines['urun_adi'];
$markalar = $lines['marka'];
$miktarlar = $lines['miktar'];
$fiyatlar = $lines['birim_fiyat'];
$satirToplamlari = $lines['satir_toplam'];
$terminler = $lines['termin'];
$paraBirimleri = $lines['kalem_para_birimi'];

$this->ensureAtLeastOneValidLine($urunAdlari);

    $currencyTotals = $this->buildCurrencyTotals($urunAdlari, $miktarlar, $fiyatlar, $satirToplamlari, $paraBirimleri);
    $headerData = $this->resolveOfferHeaderCurrencyAndTotal($currencyTotals);

    $this->repository->beginTransaction();

    try {
        $cariId = $this->repository->findCariIdByName($cariAdi);
        if (!$cariId) {
            $cariId = $this->repository->createCari($cariAdi);
        }

        $this->repository->updateOfferHeader(
            $id,
            $teklifNo,
            (int) $cariId,
            $tarih,
            $headerData['para_birimi'],
            $teklifNotlari
        );

        $this->repository->deleteItems($id);

        for ($i = 0; $i < count($urunAdlari); $i++) {
            $urunAdi = trim((string) ($urunAdlari[$i] ?? ''));
            if ($urunAdi === '') {
                continue;
            }

            $marka = trim((string) ($markalar[$i] ?? ''));
$miktar = (float) ($miktarlar[$i] ?? 0);
$birimFiyat = (float) ($fiyatlar[$i] ?? 0);
$satirToplam = $this->resolveLineTotal($miktar, $birimFiyat, $satirToplamlari[$i] ?? '');
$termin = trim((string) ($terminler[$i] ?? ''));
$kalemParaBirimi = trim((string) ($paraBirimleri[$i] ?? 'TL'));

if ($kalemParaBirimi === '') {
    $kalemParaBirimi = 'TL';
}

$this->repository->insertItem(
    $id,
    $urunAdi,
    $marka,
    $miktar,
    $birimFiyat,
    $satirToplam,
    $termin,
    $kalemParaBirimi
);
        }

        $this->repository->updateOfferTotal($id, (float) $headerData['genel_toplam']);

        $this->repository->commit();
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}

    public function delete(int $id): void
    {
        $teklif = $this->repository->findById($id);

        if (!$teklif) {
            throw new RuntimeException('Teklif bulunamadı.');
        }

        $this->repository->beginTransaction();

        try {
            $this->repository->deleteItems($id);
            $this->repository->deleteOffer($id);
            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }
}
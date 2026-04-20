<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;
use Smalot\PdfParser\Parser;
use App\Interfaces\CariAktarimRepositoryInterface;
use App\Interfaces\CariAktarimServiceInterface;

class CariAktarimService implements CariAktarimServiceInterface
{
	private CariAktarimRepositoryInterface $repository;
	
	public function __construct(CariAktarimRepositoryInterface $repository)
{
    $this->repository = $repository;
}
	
    public function storeUploadedStatement(int $cariId, ?array $dosya): array
{
    if ($cariId <= 0) {
        throw new RuntimeException('Geçersiz cari seçimi.');
    }

    if (!$dosya || !isset($dosya['tmp_name'])) {
        throw new RuntimeException('Lütfen bir ekstre dosyası seçin.');
    }

    if (($dosya['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Dosya yüklenirken bir hata oluştu.');
    }

    $orijinalAd = (string) ($dosya['name'] ?? '');
    $uzanti = strtolower((string) pathinfo($orijinalAd, PATHINFO_EXTENSION));

    if (!in_array($uzanti, ['xlsx', 'pdf'], true)) {
        throw new RuntimeException('Şimdilik sadece .xlsx ve .pdf dosyası yüklenebilir.');
    }

    $hedefKlasor = BASE_PATH . '/storage/cache/imports';

    if (!is_dir($hedefKlasor) && !mkdir($hedefKlasor, 0777, true) && !is_dir($hedefKlasor)) {
        throw new RuntimeException('Geçici import klasörü oluşturulamadı.');
    }

    $guvenliAd = 'wolvox_' . $cariId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $uzanti;
    $hedefYol = $hedefKlasor . '/' . $guvenliAd;

    if (!move_uploaded_file((string) $dosya['tmp_name'], $hedefYol)) {
        throw new RuntimeException('Yüklenen dosya geçici klasöre taşınamadı.');
    }

    return [
    'cari_id' => $cariId,
    'orijinal_ad' => $orijinalAd,
    'kaydedilen_ad' => $guvenliAd,
    'tam_yol' => $hedefYol,
    'uzanti' => $uzanti,
    'aktarim_profili' => $this->normalizeImportProfile($_POST['aktarim_profili'] ?? 'musteri'),
];
}

    public function buildPreviewFromStoredFile(array $uploaded): array
{
    $uzanti = strtolower((string) ($uploaded['uzanti'] ?? pathinfo((string) ($uploaded['tam_yol'] ?? ''), PATHINFO_EXTENSION)));

    if ($uzanti === 'pdf') {
        $rows = $this->readPdfRows((string) $uploaded['tam_yol']);
    } else {
        $rows = $this->readXlsxRows((string) $uploaded['tam_yol']);
    }

    $previewRows = [];
    $toplamBorc = 0.0;
    $toplamAlacak = 0.0;
    $satisSayisi = 0;
    $tahsilatSayisi = 0;
    $belirsizSayisi = 0;

    foreach ($rows as $row) {
        $borc = $this->parseMoney($row['F'] ?? $row['borc'] ?? null);
        $alacak = $this->parseMoney($row['G'] ?? $row['alacak'] ?? null);

        if ($borc <= 0 && $alacak <= 0) {
            continue;
        }

        $profil = $this->normalizeImportProfile((string) ($uploaded['aktarim_profili'] ?? 'musteri'));
$yorum = 'Belirsiz';

if ($profil === 'tedarikci') {
    if ($borc > 0 && $alacak <= 0) {
        $yorum = 'Tediye';
        $tahsilatSayisi++;
    } elseif ($alacak > 0 && $borc <= 0) {
        $yorum = 'Alış Faturası';
        $satisSayisi++;
    } else {
        $belirsizSayisi++;
    }
} else {
    if ($borc > 0 && $alacak <= 0) {
        $yorum = 'Satış Faturası';
        $satisSayisi++;
    } elseif ($alacak > 0 && $borc <= 0) {
        $yorum = 'Tahsilat';
        $tahsilatSayisi++;
    } else {
        $belirsizSayisi++;
    }
}

        $toplamBorc += $borc;
        $toplamAlacak += $alacak;

        $previewRows[] = [
            'satir_no' => $row['_row'] ?? $row['satir_no'] ?? null,
            'tarih' => $this->normalizeExcelDate($row['B'] ?? $row['tarih'] ?? ''),
            'borc' => $borc,
            'alacak' => $alacak,
            'yorum' => $yorum,
        ];
    }

    return [
    'queue_id' => bin2hex(random_bytes(8)),
    'cari_id' => (int) ($uploaded['cari_id'] ?? 0),
    'dosya_adi' => (string) ($uploaded['orijinal_ad'] ?? ''),
    'kaydedilen_ad' => (string) ($uploaded['kaydedilen_ad'] ?? ''),
    'dosya_tipi' => $uzanti,
	'aktarim_profili' => $profil,
    'satirlar' => $previewRows,
    'ozet' => [
        'satis_sayisi' => $satisSayisi,
        'tahsilat_sayisi' => $tahsilatSayisi,
        'belirsiz_sayisi' => $belirsizSayisi,
        'toplam_borc' => $toplamBorc,
        'toplam_alacak' => $toplamAlacak,
        'beklenen_bakiye' => $toplamBorc - $toplamAlacak,
    ],
];
}

    private function readXlsxRows(string $xlsxPath): array
    {
        if (!is_file($xlsxPath)) {
            throw new RuntimeException('Yüklenen ekstre dosyası bulunamadı.');
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Sunucuda ZipArchive desteği yok.');
        }

        $zip = new ZipArchive();

        if ($zip->open($xlsxPath) !== true) {
            throw new RuntimeException('XLSX dosyası açılamadı.');
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml !== false) {
            $sharedStrings = $this->parseSharedStrings($sharedStringsXml);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException('XLSX içinde sheet1.xml bulunamadı.');
        }

        $rows = $this->parseSheetRows($sheetXml, $sharedStrings);
        $zip->close();

        return $rows;
    }
private function readPdfRows(string $pdfPath): array
{
    if (!is_file($pdfPath)) {
        throw new RuntimeException('Yüklenen PDF dosyası bulunamadı.');
    }

    if (class_exists(Parser::class)) {
        $rows = $this->readPdfRowsUsingCoordinates($pdfPath);

        if (!empty($rows)) {
            return $rows;
        }
    }

    $text = $this->extractPdfText($pdfPath);

    if ($text === '') {
        throw new RuntimeException('PDF içinden metin okunamadı. Dosya taranmış görsel olabilir.');
    }

    $rows = $this->parseWolvoxCariHareketPdfText($text);

    if (empty($rows)) {
        $snippet = $this->flattenPdfTextForPatternScan($text);
        $snippet = mb_substr($snippet, 0, 700);

        throw new RuntimeException(
            'PDF metni okundu ama Wolvox satırları ayıklanamadı. İlk okunan metin: ' . $snippet
        );
    }

    return $rows;
}
private function readPdfRowsUsingCoordinates(string $pdfPath): array
{
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $pages = $pdf->getPages();
    } catch (\Throwable $e) {
        return [];
    }

    $allItems = [];

    foreach ($pages as $pageIndex => $page) {
        if (!method_exists($page, 'getDataTm')) {
            continue;
        }

        try {
            $dataTm = $page->getDataTm();
        } catch (\Throwable $e) {
            continue;
        }

        foreach ($dataTm as $item) {
            if (!is_array($item) || count($item) < 2 || !is_array($item[0])) {
                continue;
            }

            $tm = $item[0];
            $text = trim($this->ensureValidUtf8((string) ($item[1] ?? '')));

            if ($text === '') {
                continue;
            }

            $allItems[] = [
                'page' => $pageIndex + 1,
                'x' => isset($tm[4]) ? (float) $tm[4] : 0.0,
                'y' => isset($tm[5]) ? (float) $tm[5] : 0.0,
                'text' => $text,
            ];
        }
    }

    if (empty($allItems)) {
        return [];
    }

    $amountColumns = $this->detectWolvoxAmountColumns($allItems);

    if ($amountColumns === null) {
        throw new RuntimeException('PDF koordinatları okundu ama borç/alacak kolonları bulunamadı.');
    }

    $rows = $this->parseWolvoxRowsFromCoordinateItems($allItems, $amountColumns);

    if (empty($rows)) {
        return [];
    }

    $text = $this->extractPdfText($pdfPath);
    $totals = $this->extractWolvoxPdfTotals($text);

    if ($totals !== null) {
        $rows = $this->rebalanceRowsByTotalsIfNeeded($rows, $totals);
    }

    return $rows;
}
private function rebalanceRowsByTotalsIfNeeded(array $rows, array $totals): array
{
    $currentBorc = 0.0;
    $currentAlacak = 0.0;

    foreach ($rows as $row) {
        $currentBorc += (float) ($row['borc'] ?? 0);
        $currentAlacak += (float) ($row['alacak'] ?? 0);
    }

    $targetBorc = (float) ($totals['toplam_borc'] ?? 0);
    $targetAlacak = (float) ($totals['toplam_alacak'] ?? 0);

    if (
        $this->moneyEquals($currentBorc, $targetBorc) &&
        $this->moneyEquals($currentAlacak, $targetAlacak)
    ) {
        return $rows;
    }

    if (!$this->shouldUseSubsetRebalance($rows)) {
        return $rows;
    }

    $reassigned = $this->assignRowsByTotals($rows, $targetBorc, $targetAlacak);

    if ($reassigned !== null) {
        return $reassigned;
    }

    return $rows;
}

private function assignRowsByTotals(array $rows, float $targetBorc, float $targetAlacak): ?array
{
	if (!$this->shouldUseSubsetRebalance($rows)) {
    return null;
}
    $amountsKurus = [];
    $rawAmounts = [];

    foreach ($rows as $row) {
        $amount = max(
            (float) ($row['borc'] ?? 0),
            (float) ($row['alacak'] ?? 0)
        );

        $rawAmounts[] = $amount;
        $amountsKurus[] = (int) round($amount * 100);
    }

    $targetAlacakKurus = (int) round($targetAlacak * 100);
    $selectedIndexes = $this->findSubsetIndexesForTarget($amountsKurus, $targetAlacakKurus);

    if ($selectedIndexes === null) {
        return null;
    }

    $selectedMap = array_fill_keys($selectedIndexes, true);
    $result = [];

    foreach ($rows as $index => $row) {
        $amount = $rawAmounts[$index];
        $borc = 0.0;
        $alacak = 0.0;

        if (isset($selectedMap[$index])) {
            $alacak = $amount;
        } else {
            $borc = $amount;
        }

        $row['borc'] = $borc;
        $row['alacak'] = $alacak;
        $result[] = $row;
    }

    $sumBorc = array_reduce($result, static function ($carry, $row) {
        return $carry + (float) ($row['borc'] ?? 0);
    }, 0.0);

    $sumAlacak = array_reduce($result, static function ($carry, $row) {
        return $carry + (float) ($row['alacak'] ?? 0);
    }, 0.0);

    if (
        !$this->moneyEquals($sumBorc, $targetBorc) ||
        !$this->moneyEquals($sumAlacak, $targetAlacak)
    ) {
        return null;
    }

    return $result;
}

private function moneyEquals(float $a, float $b): bool
{
    return abs(round($a, 2) - round($b, 2)) < 0.01;
}

private function shouldUseSubsetRebalance(array $rows): bool
{
    $rowCount = count($rows);

    if ($rowCount > 40) {
        return false;
    }

    $distinctAmounts = [];

    foreach ($rows as $row) {
        $amount = max(
            (float) ($row['borc'] ?? 0),
            (float) ($row['alacak'] ?? 0)
        );

        if ($amount > 0) {
            $distinctAmounts[number_format($amount, 2, '.', '')] = true;
        }
    }

    if (count($distinctAmounts) > 35) {
        return false;
    }

    return true;
}

private function detectWolvoxAmountColumns(array $items): ?array
{
    $fromHeaders = $this->detectWolvoxAmountColumnsFromHeaders($items);

    if ($fromHeaders !== null) {
        return $fromHeaders;
    }

    $amountXs = [];

    foreach ($items as $item) {
        $text = trim((string) ($item['text'] ?? ''));

        if (!$this->isMoneyText($text)) {
            continue;
        }

        // alt toplam / footer satırlarını kümeye sokma
        $y = (float) ($item['y'] ?? 0);
        if ($y > 470 && $y < 505) {
            continue;
        }

        $amountXs[] = (float) ($item['x'] ?? 0);
    }

    if (count($amountXs) < 2) {
        return null;
    }

    sort($amountXs);

    $clusters = $this->clusterAmountXsIntoTwoColumns($amountXs);
    if ($clusters === null) {
        return null;
    }

    [$leftCenter, $rightCenter] = $clusters;

    if (abs($rightCenter - $leftCenter) < 8) {
        return null;
    }

    return [
        'borc_x' => $leftCenter,
        'alacak_x' => $rightCenter,
    ];
}

private function detectWolvoxAmountColumnsFromHeaders(array $items): ?array
{
    $grouped = [];

    foreach ($items as $item) {
        $page = (int) ($item['page'] ?? 1);
        $y = (float) ($item['y'] ?? 0);

        $groupKey = $this->findNearestRowGroupKey($grouped, $page, $y, 1.5);

        if ($groupKey === null) {
            $groupKey = $page . '|' . round($y, 1);
            $grouped[$groupKey] = [
                'page' => $page,
                'y' => $y,
                'items' => [],
            ];
        }

        $grouped[$groupKey]['items'][] = $item;
    }

    foreach ($grouped as $group) {
        $lineItems = $group['items'];

        usort($lineItems, static function ($a, $b) {
            return ((float) ($a['x'] ?? 0)) <=> ((float) ($b['x'] ?? 0));
        });

        $lineText = '';
        foreach ($lineItems as $item) {
            $lineText .= ' ' . trim((string) ($item['text'] ?? ''));
        }

        $normalizedLine = $this->normalizeTurkishTextForMatch($lineText);

        if (
            !str_contains($normalizedLine, 'borc tutar') ||
            !str_contains($normalizedLine, 'alacak tutar')
        ) {
            continue;
        }

        $borcX = null;
        $alacakX = null;

        foreach ($lineItems as $item) {
            $normalized = $this->normalizeTurkishTextForMatch((string) ($item['text'] ?? ''));
            $x = (float) ($item['x'] ?? 0);

            if ($borcX === null && str_contains($normalized, 'borc tutar')) {
                $borcX = $x;
            }

            if ($alacakX === null && str_contains($normalized, 'alacak tutar')) {
                $alacakX = $x;
            }
        }

        if ($borcX !== null && $alacakX !== null && abs($alacakX - $borcX) >= 8) {
            if ($borcX > $alacakX) {
                [$borcX, $alacakX] = [$alacakX, $borcX];
            }

            return [
                'borc_x' => $borcX,
                'alacak_x' => $alacakX,
            ];
        }
    }

    return null;
}

private function clusterAmountXsIntoTwoColumns(array $values): ?array
{
    if (count($values) < 2) {
        return null;
    }

    $c1 = (float) min($values);
    $c2 = (float) max($values);

    if (abs($c2 - $c1) < 0.01) {
        return null;
    }

    for ($i = 0; $i < 12; $i++) {
        $g1 = [];
        $g2 = [];

        foreach ($values as $value) {
            if (abs($value - $c1) <= abs($value - $c2)) {
                $g1[] = $value;
            } else {
                $g2[] = $value;
            }
        }

        if (empty($g1) || empty($g2)) {
            return null;
        }

        $newC1 = array_sum($g1) / count($g1);
        $newC2 = array_sum($g2) / count($g2);

        if (abs($newC1 - $c1) < 0.01 && abs($newC2 - $c2) < 0.01) {
            $c1 = $newC1;
            $c2 = $newC2;
            break;
        }

        $c1 = $newC1;
        $c2 = $newC2;
    }

    if ($c1 > $c2) {
        [$c1, $c2] = [$c2, $c1];
    }

    return [$c1, $c2];
}

private function isMoneyText(string $text): bool
{
    $text = trim($text);

    return (bool) preg_match('/^\d{1,3}(?:\.\d{3})*,\d{2}$/u', $text);
}

private function classifyAmountColumn(float $x, array $columns): string
{
    $borcX = (float) ($columns['borc_x'] ?? 0);
    $alacakX = (float) ($columns['alacak_x'] ?? 0);

    $borcDistance = abs($x - $borcX);
    $alacakDistance = abs($x - $alacakX);

    return $borcDistance <= $alacakDistance ? 'borc' : 'alacak';
}

private function parseWolvoxRowsFromCoordinateItems(array $items, array $amountColumns): array
{
    $grouped = [];

    foreach ($items as $item) {
        $page = (int) ($item['page'] ?? 1);
        $y = (float) ($item['y'] ?? 0);

        $groupKey = $this->findNearestRowGroupKey($grouped, $page, $y, 2.5);

        if ($groupKey === null) {
            $groupKey = $page . '|' . round($y, 1);
            $grouped[$groupKey] = [
                'page' => $page,
                'y' => $y,
                'items' => [],
            ];
        }

        $grouped[$groupKey]['items'][] = $item;
    }

    uasort($grouped, static function ($a, $b) {
        if ($a['page'] === $b['page']) {
            return $a['y'] <=> $b['y'];
        }

        return $a['page'] <=> $b['page'];
    });

    $rows = [];
    $rowNo = 1;

    foreach ($grouped as $group) {
        $lineItems = $group['items'];

        usort($lineItems, static function ($a, $b) {
            return $a['x'] <=> $b['x'];
        });

        $texts = array_map(static function ($item) {
            return trim((string) ($item['text'] ?? ''));
        }, $lineItems);

        $lineText = trim(implode(' ', array_filter($texts)));

        if (!preg_match('/^\d+\s+\d{1,2}\.\d{2}\.\d{4}\b/u', $lineText)) {
            continue;
        }

        if (!preg_match('/^\d+\s+(\d{1,2}\.\d{2}\.\d{4})\b/u', $lineText, $dateMatch)) {
            continue;
        }

        $moneyItems = [];

        foreach ($lineItems as $item) {
            $text = trim((string) ($item['text'] ?? ''));

            if ($this->isMoneyText($text)) {
                $moneyItems[] = $item;
            }
        }

        if (empty($moneyItems)) {
            continue;
        }

        $amountItem = null;
$bestDistance = null;

foreach ($moneyItems as $candidate) {
    $candidateX = (float) ($candidate['x'] ?? 0);

    $distance = min(
        abs($candidateX - (float) ($amountColumns['borc_x'] ?? 0)),
        abs($candidateX - (float) ($amountColumns['alacak_x'] ?? 0))
    );

    if ($bestDistance === null || $distance < $bestDistance) {
        $bestDistance = $distance;
        $amountItem = $candidate;
    }
}

if ($amountItem === null) {
    continue;
}
        $amount = $this->parseMoney((string) ($amountItem['text'] ?? ''));

        if ($amount <= 0) {
            continue;
        }

        $column = $this->classifyAmountColumn((float) ($amountItem['x'] ?? 0), $amountColumns);

        $borc = 0.0;
        $alacak = 0.0;

        if ($column === 'borc') {
            $borc = $amount;
        } else {
            $alacak = $amount;
        }

        $rows[] = [
            '_row' => $rowNo,
            'tarih' => $dateMatch[1],
            'borc' => $borc,
            'alacak' => $alacak,
            'raw_line' => $lineText,
        ];

        $rowNo++;
    }

    return $rows;
}

private function findNearestRowGroupKey(array $grouped, int $page, float $y, float $tolerance): ?string
{
    foreach ($grouped as $key => $group) {
        if ((int) $group['page'] !== $page) {
            continue;
        }

        if (abs((float) $group['y'] - $y) <= $tolerance) {
            return $key;
        }
    }

    return null;
}

private function normalizeTurkishTextForMatch(string $text): string
{
    $text = mb_strtolower($this->ensureValidUtf8($text), 'UTF-8');

    return str_replace(
        ['ı', 'i̇', 'ç', 'ğ', 'ö', 'ş', 'ü'],
        ['i', 'i', 'c', 'g', 'o', 's', 'u'],
        $text
    );
}
private function parseWolvoxCariHareketPdfText(string $text): array
{
    $flatText = $this->flattenPdfTextForPatternScan($text);
    $lower = mb_strtolower($flatText, 'UTF-8');

    if (
        !str_contains($lower, 'cari hareket raporu') &&
        !str_contains($lower, 'alt toplam')
    ) {
        return [];
    }

    preg_match_all(
        '/(?:^|\s)(\d+)\s+(\d{1,2}\.\d{2}\.\d{4})\s+([A-Z0-9]+)\s+(.+?)\s+(\d{1,3}(?:\.\d{3})*,\d{2})(?=\s+\d+\s+\d{1,2}\.\d{2}\.\d{4}\s+[A-Z0-9]+\s+|\s+Alt\s+Toplam|$)/u',
        $flatText,
        $matches,
        PREG_SET_ORDER
    );

    $rows = [];
    $rowNo = 1;

    foreach ($matches as $match) {
        $rows[] = [
            '_row' => $rowNo,
            'tarih' => $match[2],
            'tutar' => $this->parseMoney($match[5]),
            'raw_line' => trim($match[0]),
        ];

        $rowNo++;
    }

    if (empty($rows)) {
        return [];
    }

    $totals = $this->extractWolvoxPdfTotals($flatText);

    if ($totals === null) {
        throw new RuntimeException('PDF satırları okundu ama alt toplamlar bulunamadı.');
    }

    $assigned = $this->assignDebitCreditByTotals($rows, $totals['toplam_borc'], $totals['toplam_alacak']);

if (!empty($assigned)) {
    return $assigned;
}

return $rows;
}
private function flattenPdfTextForPatternScan(string $text): string
{
    $text = $this->ensureValidUtf8($text);
    $text = str_replace("\0", ' ', $text);
    $text = preg_replace('/[\r\n\t]+/u', ' ', $text) ?? $text;
    $text = preg_replace('/\s{2,}/u', ' ', $text) ?? $text;

    return trim($text);
}

private function extractWolvoxPdfTotals(string $text): ?array
{
    $flatText = $this->flattenPdfTextForPatternScan($text);

    // önce son kısma odaklan
    $tail = mb_substr($flatText, max(0, mb_strlen($flatText, 'UTF-8') - 1200), null, 'UTF-8');

    // 1) en rahat senaryo: son kısımda TL ile geçen tutarları al
    preg_match_all('/(\d{1,3}(?:\.\d{3})*,\d{2})\s*(?:TL)?/iu', $tail, $moneyMatches);
    $amounts = $moneyMatches[1] ?? [];

    // sayfa no / tarih gibi şeyleri yanlış almamak için sadece para formatındakileri bırak
    $amounts = array_values(array_filter($amounts, function ($value) {
        return preg_match('/^\d{1,3}(?:\.\d{3})*,\d{2}$/u', (string) $value);
    }));

    if (count($amounts) >= 3) {
        $lastThree = array_slice($amounts, -3);

        preg_match('/\b(Bor[cç]|Alacak)\b/iu', $tail, $dirMatch);
        $direction = $dirMatch[1] ?? 'Borç';

        return [
            'toplam_borc' => $this->parseMoney($lastThree[0]),
            'toplam_alacak' => $this->parseMoney($lastThree[1]),
            'toplam_bakiye' => $this->parseMoney($lastThree[2]),
            'bakiye_yonu' => $direction,
        ];
    }

    // 2) son fallback: tüm metindeki son 3 para değerini al
    preg_match_all('/(\d{1,3}(?:\.\d{3})*,\d{2})/u', $flatText, $allMoneyMatches);
    $allAmounts = $allMoneyMatches[1] ?? [];

    if (count($allAmounts) >= 3) {
        $lastThree = array_slice($allAmounts, -3);

        preg_match('/\b(Bor[cç]|Alacak)\b/iu', $flatText, $dirMatch);
        $direction = $dirMatch[1] ?? 'Borç';

        return [
            'toplam_borc' => $this->parseMoney($lastThree[0]),
            'toplam_alacak' => $this->parseMoney($lastThree[1]),
            'toplam_bakiye' => $this->parseMoney($lastThree[2]),
            'bakiye_yonu' => $direction,
        ];
    }

    return null;
}

private function assignDebitCreditByTotals(array $rows, float $toplamBorc, float $toplamAlacak): array
{
    $assigned = $this->assignRowsByTotals($rows, $toplamBorc, $toplamAlacak);

    if ($assigned !== null) {
        return $assigned;
    }

    throw new RuntimeException(
        'PDF satırları okundu ama borç/alacak ayrımı toplamlarla eşleştirilemedi.'
    );
}

private function findSubsetIndexesForTarget(array $amountsKurus, int $targetKurus): ?array
{
	if (count($amountsKurus) > 40) {
        return null;
    }
    if ($targetKurus < 0) {
        return null;
    }

    $reachable = [
        0 => [],
    ];

    foreach ($amountsKurus as $index => $amount) {
        if ($amount <= 0) {
            continue;
        }

        $current = $reachable;

        foreach ($reachable as $sum => $indexes) {
            $newSum = $sum + $amount;

            if ($newSum > $targetKurus) {
                continue;
            }

            if (!array_key_exists($newSum, $current)) {
                $newIndexes = $indexes;
                $newIndexes[] = $index;
                $current[$newSum] = $newIndexes;
            }
        }

        $reachable = $current;

        if (array_key_exists($targetKurus, $reachable)) {
            return $reachable[$targetKurus];
        }
    }

    return $reachable[$targetKurus] ?? null;
}

private function extractPdfText(string $pdfPath): string
{
    if (class_exists(Parser::class)) {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = (string) $pdf->getText();
            $text = $this->normalizePdfText($text);

            if ($text !== '') {
                return $text;
            }
        } catch (\Throwable $e) {
            // aşağıdaki fallback'lere düş
        }
    }

    $commandText = $this->tryExtractPdfTextWithPdftotext($pdfPath);
    if ($commandText !== '') {
        return $this->normalizePdfText($commandText);
    }

    return $this->normalizePdfText($this->extractPdfTextFromStreams($pdfPath));
}

private function tryExtractPdfTextWithPdftotext(string $pdfPath): string
{
    if (!function_exists('shell_exec')) {
        return '';
    }

    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    if (in_array('shell_exec', $disabled, true)) {
        return '';
    }

    $binary = trim((string) @shell_exec('command -v pdftotext 2>/dev/null'));
    if ($binary === '') {
        return '';
    }

    $tmpOutput = tempnam(sys_get_temp_dir(), 'pdftext_');
    if ($tmpOutput === false) {
        return '';
    }

    $cmd = escapeshellcmd($binary) . ' ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpOutput) . ' 2>/dev/null';
    @shell_exec($cmd);

    $text = '';
    if (is_file($tmpOutput)) {
        $text = (string) @file_get_contents($tmpOutput);
        @unlink($tmpOutput);
    }

    return trim($text);
}

private function extractPdfTextFromStreams(string $pdfPath): string
{
    $content = @file_get_contents($pdfPath);

    if ($content === false || $content === '') {
        throw new RuntimeException('PDF dosyası okunamadı.');
    }

    preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches);

    $chunks = [];

    foreach ($matches[1] ?? [] as $stream) {
        $decoded = $this->decodePdfStream($stream);

        if ($decoded === '') {
            continue;
        }

        $text = $this->extractTextSegmentsFromDecodedStream($decoded);

        if ($text !== '') {
            $chunks[] = $text;
        }
    }

    $text = trim(implode("\n", $chunks));
$text = $this->ensureValidUtf8($text);

return $text;
}

private function decodePdfStream(string $stream): string
{
    $stream = ltrim($stream, "\r\n");

    $candidates = [
        $stream,
        @gzuncompress($stream),
        @gzdecode($stream),
        @gzinflate($stream),
    ];

    if (strlen($stream) > 2) {
        $candidates[] = @gzinflate(substr($stream, 2));
    }

    foreach ($candidates as $candidate) {
        if (!is_string($candidate) || $candidate === '') {
            continue;
        }

        if (preg_match('/BT|Tj|TJ|Tf|Td|ET/', $candidate)) {
            return $candidate;
        }
    }

    return '';
}

private function extractTextSegmentsFromDecodedStream(string $decoded): string
{
    $parts = [];

    if (preg_match_all('/\((?:\\\\.|[^\\\\])*?\)\s*Tj/s', $decoded, $matches)) {
        foreach ($matches[0] as $match) {
            if (preg_match('/\(((?:\\\\.|[^\\\\])*?)\)\s*Tj/s', $match, $m)) {
                $parts[] = $this->unescapePdfText($m[1]);
            }
        }
    }

    if (preg_match_all('/\[(.*?)\]\s*TJ/s', $decoded, $matches)) {
        foreach ($matches[1] as $arrayContent) {
            if (preg_match_all('/\((?:\\\\.|[^\\\\])*?\)/s', $arrayContent, $innerMatches)) {
                $line = '';

                foreach ($innerMatches[0] as $piece) {
                    $line .= $this->unescapePdfText(substr($piece, 1, -1));
                }

                if (trim($line) !== '') {
                    $parts[] = $line;
                }
            }
        }
    }

    if (preg_match_all('/\((?:\\\\.|[^\\\\])*?\)\s*[\'"]/s', $decoded, $matches)) {
        foreach ($matches[0] as $match) {
            if (preg_match('/\(((?:\\\\.|[^\\\\])*?)\)\s*[\'"]/s', $match, $m)) {
                $parts[] = $this->unescapePdfText($m[1]);
            }
        }
    }

    $text = implode("\n", $parts);
    return trim($text);
}

private function unescapePdfText(string $text): string
{
    $text = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $text);

    $text = preg_replace_callback('/\\\\([0-7]{3})/', static function ($matches) {
        return chr(octdec($matches[1]));
    }, $text) ?? $text;

   $text = str_replace(["\r", "\n", "\t"], ' ', $text);
$text = $this->ensureValidUtf8($text);

return trim($text);
}

private function normalizePdfText(string $text): string
{
    $text = $this->ensureValidUtf8($text);
    $text = str_replace("\0", ' ', $text);
    $text = preg_replace('/[^\P{C}\n\t]+/u', ' ', $text) ?? $text;
    $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
    $text = preg_replace('/\n{2,}/u', "\n", $text) ?? $text;

    return trim($text);
}

private function looksLikeTahsilatLine(string $line): bool
{
    $line = mb_strtolower($line);

    return
        str_contains($line, 'tahsilat') ||
        str_contains($line, 'ödeme') ||
        str_contains($line, 'odeme') ||
        str_contains($line, 'havale') ||
        str_contains($line, 'eft') ||
        str_contains($line, 'nakit') ||
        str_contains($line, 'virman') ||
        str_contains($line, 'kredi kartı') ||
        str_contains($line, 'kredi karti') ||
        str_contains($line, 'pos');
}

    private function parseSharedStrings(string $xmlContent): array
{
    $xml = simplexml_load_string($xmlContent);
    if (!$xml instanceof SimpleXMLElement) {
        return [];
    }

    $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $children = $xml->children($ns);

    $result = [];

    foreach ($children->si ?? [] as $si) {
        $text = '';
        $siChildren = $si->children($ns);

        foreach ($siChildren->t ?? [] as $t) {
            $text .= (string) $t;
        }

        foreach ($siChildren->r ?? [] as $run) {
            $runChildren = $run->children($ns);
            if (isset($runChildren->t)) {
                $text .= (string) $runChildren->t;
            }
        }

        $result[] = trim($text);
    }

    return $result;
}
private function ensureValidUtf8(string $text): string
{
    if ($text === '') {
        return '';
    }

    if (mb_check_encoding($text, 'UTF-8')) {
        return $text;
    }

    $converted = @mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-9, ISO-8859-1, Windows-1254');
    if (is_string($converted) && $converted !== '') {
        $text = $converted;
    }

    $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;

    return $text;
}

    private function parseSheetRows(string $xmlContent, array $sharedStrings): array
{
    $xml = simplexml_load_string($xmlContent);
    if (!$xml instanceof SimpleXMLElement) {
        throw new RuntimeException('Çalışma sayfası XML verisi okunamadı.');
    }

    $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $children = $xml->children($ns);

    $rows = [];

    if (!isset($children->sheetData)) {
        return $rows;
    }

    foreach ($children->sheetData->row ?? [] as $row) {
        $rowAttributes = $row->attributes();
        $rowIndex = (int) ($rowAttributes['r'] ?? 0);
        $parsed = ['_row' => $rowIndex];

        foreach ($row->children($ns)->c ?? [] as $cell) {
            $cellAttributes = $cell->attributes();

            $cellRef = (string) ($cellAttributes['r'] ?? '');
            $column = preg_replace('/\d+/', '', $cellRef);
            $type = (string) ($cellAttributes['t'] ?? '');

            $value = '';
            $cellChildren = $cell->children($ns);

            if (isset($cellChildren->v)) {
                $value = (string) $cellChildren->v;
            }

            if ($type === 's') {
                $value = $sharedStrings[(int) $value] ?? '';
            }

            $parsed[$column] = trim((string) $value);
        }

        $marker = mb_strtolower((string) ($parsed['A'] ?? ''));
        $markerB = mb_strtolower((string) ($parsed['B'] ?? ''));

        if (str_contains($marker, 'alt toplam') || str_contains($markerB, 'alt toplam')) {
            break;
        }

        if ($rowIndex < 4) {
            continue;
        }

        $rows[] = $parsed;
    }

    return $rows;
}

    private function normalizeExcelDate(mixed $raw): string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return '-';
        }

        if (is_numeric($raw)) {
            $serial = (float) $raw;
            $unixTime = (int) round(($serial - 25569) * 86400);

            if ($unixTime > 0) {
                return gmdate('d.m.Y', $unixTime);
            }
        }

        $timestamp = strtotime($raw);
        if ($timestamp !== false) {
            return date('d.m.Y', $timestamp);
        }

        return $raw;
    }
	
	private function normalizeImportProfile(string $profile): string
{
    $profile = trim(mb_strtolower($profile, 'UTF-8'));

    return in_array($profile, ['musteri', 'tedarikci'], true) ? $profile : 'musteri';
}

    private function parseMoney(mixed $value): float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0.0;
        }

        $value = str_replace(['TL', 'tl', '₺', ' '], '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }
	public function addPreviewToQueue(array $preview): void
{
    if (empty($preview['queue_id'])) {
        $preview['queue_id'] = bin2hex(random_bytes(8));
    }

    if (!isset($_SESSION['wolvox_import_queue']) || !is_array($_SESSION['wolvox_import_queue'])) {
        $_SESSION['wolvox_import_queue'] = [];
    }

    foreach ($_SESSION['wolvox_import_queue'] as $existing) {
        if (
            (int) ($existing['cari_id'] ?? 0) === (int) ($preview['cari_id'] ?? 0) &&
            (string) ($existing['dosya_adi'] ?? '') === (string) ($preview['dosya_adi'] ?? '')
        ) {
            throw new RuntimeException('Aynı cari ve aynı dosya zaten kuyrukta görünüyor.');
        }
    }

    $_SESSION['wolvox_import_queue'][] = $preview;
}

public function removePreviewFromQueue(string $queueId): void
{
    $queue = $_SESSION['wolvox_import_queue'] ?? [];

    if (!is_array($queue) || $queueId === '') {
        throw new RuntimeException('Silinecek kuyruk kaydı bulunamadı.');
    }

    $found = false;
    $newQueue = [];

    foreach ($queue as $item) {
        if ((string) ($item['queue_id'] ?? '') === $queueId) {
            $found = true;
            continue;
        }

        $newQueue[] = $item;
    }

    if (!$found) {
        throw new RuntimeException('Kuyruk kaydı bulunamadı.');
    }

    $_SESSION['wolvox_import_queue'] = array_values($newQueue);

    $currentPreview = $_SESSION['wolvox_preview'] ?? null;
    if (is_array($currentPreview) && (string) ($currentPreview['queue_id'] ?? '') === $queueId) {
        unset($_SESSION['wolvox_preview']);
    }
}

public function executeAllImportsFromQueue(): array
{
    $queue = $_SESSION['wolvox_import_queue'] ?? [];

    if (!is_array($queue) || empty($queue)) {
        throw new RuntimeException('Toplu aktarım için kuyrukta kayıt bulunamadı.');
    }

    $results = [];
    $errors = [];

    foreach ($queue as $preview) {
        try {
            $result = $this->importPreview($preview);
            $results[] = $result;
        } catch (\Throwable $e) {
            $errors[] = [
                'cari_id' => (int) ($preview['cari_id'] ?? 0),
                'dosya_adi' => (string) ($preview['dosya_adi'] ?? ''),
                'mesaj' => $e->getMessage(),
            ];
        }
    }

    $_SESSION['wolvox_import_queue'] = [];
    unset($_SESSION['wolvox_preview']);

    return [
        'basarili' => count($results),
        'hatali' => count($errors),
        'sonuclar' => $results,
        'hatalar' => $errors,
    ];
}
	public function executeImportFromSession(int $requestedCariId): array
{
    $preview = $_SESSION['wolvox_preview'] ?? null;

    if (!is_array($preview) || empty($preview['satirlar'])) {
        throw new RuntimeException('Aktarılacak önizleme verisi bulunamadı.');
    }

    $previewCariId = (int) ($preview['cari_id'] ?? 0);

    if ($previewCariId <= 0 || $previewCariId !== $requestedCariId) {
        throw new RuntimeException('Önizleme verisi ile seçilen cari eşleşmiyor.');
    }

    $result = $this->importPreview($preview);

    $queueId = (string) ($preview['queue_id'] ?? '');
    if ($queueId !== '' && isset($_SESSION['wolvox_import_queue']) && is_array($_SESSION['wolvox_import_queue'])) {
        $_SESSION['wolvox_import_queue'] = array_values(array_filter(
            $_SESSION['wolvox_import_queue'],
            static function ($item) use ($queueId) {
                return (string) ($item['queue_id'] ?? '') !== $queueId;
            }
        ));
    }

    unset($_SESSION['wolvox_preview']);

    return $result;
}

private function importPreview(array $preview): array
{
    $cariId = (int) ($preview['cari_id'] ?? 0);
    $satirlar = $preview['satirlar'] ?? [];

    if ($cariId <= 0) {
        throw new RuntimeException('Geçersiz cari kaydı.');
    }

    $cari = $this->repository->findCariById($cariId);
    if (!$cari) {
        throw new RuntimeException('Seçilen cari bulunamadı.');
    }

    $yedekYolu = $this->createDatabaseBackup();

    $this->repository->beginTransaction();

    try {
        $this->clearCariFinancialHistory($cariId);

        $satisSayisi = 0;
        $tahsilatSayisi = 0;
        $siraNo = 1;

        $profil = $this->normalizeImportProfile((string) ($preview['aktarim_profili'] ?? 'musteri'));

foreach ($satirlar as $satir) {
    $yorum = trim((string) ($satir['yorum'] ?? ''));
    $tarih = $this->toDatabaseDate((string) ($satir['tarih'] ?? ''));
    $borc = (float) ($satir['borc'] ?? 0);
    $alacak = (float) ($satir['alacak'] ?? 0);

    if ($profil === 'tedarikci') {
        if ($yorum === 'Alış Faturası' && $alacak > 0) {
            $faturaNo = $this->generateImportInvoiceNo($cariId, $tarih, $siraNo);

            $this->repository->insertInvoice(
                $cariId,
                'alis',
                $faturaNo,
                $tarih,
                $alacak,
                $tarih
            );

            $this->repository->insertInvoiceMovement(
                $cariId,
                'alis',
                $alacak,
                $faturaNo,
                $tarih . ' 00:00:00'
            );

            $this->repository->decreaseBalance($cariId, $alacak);
            $satisSayisi++;
            $siraNo++;
            continue;
        }

        if ($yorum === 'Tediye' && $borc > 0) {
            $aciklama = 'Wolvox Devir Tediye';

            $this->repository->insertPaymentMovement(
                $cariId,
                'tediye',
                $borc,
                $aciklama,
                $tarih . ' 00:00:00'
            );

            $this->repository->increaseBalance($cariId, $borc);
            $tahsilatSayisi++;
        }

        continue;
    }

    if ($yorum === 'Satış Faturası' && $borc > 0) {
        $faturaNo = $this->generateImportInvoiceNo($cariId, $tarih, $siraNo);

        $this->repository->insertInvoice(
            $cariId,
            'satis',
            $faturaNo,
            $tarih,
            $borc,
            $tarih
        );

        $this->repository->insertInvoiceMovement(
            $cariId,
            'satis',
            $borc,
            $faturaNo,
            $tarih . ' 00:00:00'
        );

        $this->repository->increaseBalance($cariId, $borc);
        $satisSayisi++;
        $siraNo++;
        continue;
    }

    if ($yorum === 'Tahsilat' && $alacak > 0) {
        $aciklama = 'Wolvox Devir Tahsilat';

        $this->repository->insertPaymentMovement(
            $cariId,
            'tahsilat',
            $alacak,
            $aciklama,
            $tarih . ' 00:00:00'
        );

        $this->repository->decreaseBalance($cariId, $alacak);
        $tahsilatSayisi++;
    }
}

        $this->rebuildInvoicePaymentsFromMovements();

        $this->repository->commit();

        return [
            'cari_id' => $cariId,
            'cari_adi' => (string) ($cari['ad_soyad'] ?? ''),
            'satis_sayisi' => $satisSayisi,
            'tahsilat_sayisi' => $tahsilatSayisi,
            'yedek_yolu' => $yedekYolu,
        ];
    } catch (\Throwable $e) {
        $this->repository->rollBack();
        throw $e;
    }
}

private function clearCariFinancialHistory(int $cariId): void
{
    $invoiceIds = $this->repository->getInvoiceIdsByCariId($cariId);

    if (!empty($invoiceIds)) {
        $this->repository->deleteInvoiceItemsByInvoiceIds($invoiceIds);
    }

    $this->repository->deleteStockMovementsByCariId($cariId);
    $this->repository->deleteInvoicesByCariId($cariId);
    $this->repository->deleteMovementsByCariId($cariId);
    $this->repository->setCariBalance($cariId, 0);
}

private function createDatabaseBackup(): string
{
    $dbPath = BASE_PATH . '/veritabani.sqlite';

    if (!is_file($dbPath)) {
        return '';
    }

    $backupDir = BASE_PATH . '/storage/backups';

    if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true) && !is_dir($backupDir)) {
        throw new RuntimeException('Veritabanı yedek klasörü oluşturulamadı.');
    }

    $backupPath = $backupDir . '/wolvox_import_' . date('Ymd_His') . '.sqlite';

    if (!copy($dbPath, $backupPath)) {
        throw new RuntimeException('Veritabanı yedeği alınamadı.');
    }

    return $backupPath;
}

private function toDatabaseDate(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return date('Y-m-d');
    }

    $date = \DateTimeImmutable::createFromFormat('d.m.Y', $value);
    if ($date instanceof \DateTimeImmutable) {
        return $date->format('Y-m-d');
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return date('Y-m-d');
}

private function generateImportInvoiceNo(int $cariId, string $tarih, int $siraNo): string
{
    return sprintf(
        'WVX-S-%d-%s-%03d',
        $cariId,
        str_replace('-', '', $tarih),
        $siraNo
    );
}

private function rebuildInvoicePaymentsFromMovements(): void
{
    $this->repository->resetAllInvoicePaidAmounts();

    $hareketler = $this->repository->getAllPaymentMovements();

    foreach ($hareketler as $hareket) {
        $cariId = (int) ($hareket['cari_id'] ?? 0);
        $tip = (string) ($hareket['islem_tipi'] ?? '');
        $tutar = (float) ($hareket['tutar'] ?? 0);

        if ($cariId <= 0 || $tutar <= 0 || !in_array($tip, ['tahsilat', 'tediye'], true)) {
            continue;
        }

        $faturaTipi = $tip === 'tahsilat' ? 'satis' : 'alis';
        $kalan = $tutar;

        $acikFaturalar = $this->repository->getOpenInvoicesForCariAndType($cariId, $faturaTipi);

        foreach ($acikFaturalar as $fatura) {
            if ($kalan <= 0) {
                break;
            }

            $acikTutar = max(0, (float) ($fatura['acik_tutar'] ?? 0));

            if ($acikTutar <= 0) {
                continue;
            }

            $uygulanacak = min($kalan, $acikTutar);

            if ($uygulanacak <= 0) {
                continue;
            }

            $this->repository->increaseInvoicePaidAmount((int) ($fatura['id'] ?? 0), $uygulanacak);
            $kalan -= $uygulanacak;
        }
    }

    $this->repository->clampInvoicePaidAmounts();
}
}
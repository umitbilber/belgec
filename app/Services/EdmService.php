<?php

declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use SoapClient;
use SoapFault;
use App\Interfaces\EdmServiceInterface;
use App\Interfaces\SettingsServiceInterface;

class EdmService implements EdmServiceInterface
{
    private SettingsServiceInterface $settingsService;

public function __construct(SettingsServiceInterface $settingsService)
{
    $this->settingsService = $settingsService;
}
    public function testConnection(): array
    {
        $client = $this->createClient();
        $sessionId = $this->login($client);

        try {
            $counter = $this->checkCounter($client, $sessionId);

            return [
                'ok' => true,
                'session_id' => $sessionId,
                'counter' => $counter,
                'wsdl' => $this->getWsdlUrl(),
            ];
        } finally {
            $this->safeLogout($client, $sessionId);
        }
    }

    public function previewRecentInvoices(string $direction, int $days = 7): array
    {
        $direction = strtoupper(trim($direction));

        if (!in_array($direction, ['IN', 'OUT'], true)) {
            throw new RuntimeException('EDM yön bilgisi geçersiz. IN veya OUT kullanılmalıdır.');
        }

        $client = $this->createClient();
        $sessionId = $this->login($client);

        try {
            return $this->getInvoiceHeaders($client, $sessionId, $direction, $days);
        } finally {
            $this->safeLogout($client, $sessionId);
        }
    }
    
    public function getInvoicesByRange(string $direction, string $baslangic, string $bitis): array
{
    $direction = strtoupper(trim($direction));

    if (!in_array($direction, ['IN', 'OUT'], true)) {
        throw new RuntimeException('EDM yön bilgisi geçersiz. IN veya OUT kullanılmalıdır.');
    }

    $start = new DateTimeImmutable($baslangic . ' 00:00:00', new \DateTimeZone('Europe/Istanbul'));
$end   = new DateTimeImmutable($bitis   . ' 23:59:59', new \DateTimeZone('Europe/Istanbul'));

    $client    = $this->createClient();
    $sessionId = $this->login($client);

    try {
        return $this->getInvoiceHeadersByRange($client, $sessionId, $direction, $start, $end);
    } finally {
        $this->safeLogout($client, $sessionId);
    }
}
public function getFaturaIcerik(string $uuid, string $direction): string
{
    $direction = strtoupper(trim($direction));

    if (!in_array($direction, ['IN', 'OUT'], true)) {
        throw new RuntimeException('Geçersiz yön bilgisi.');
    }

    if ($uuid === '') {
        throw new RuntimeException('UUID boş.');
    }

    $client    = $this->createClient();
    $sessionId = $this->login($client);

    try {
        $response = $client->__soapCall('GetInvoice', [[
            'REQUEST_HEADER' => $this->buildRequestHeader($sessionId, 'Belgeç EDM fatura içeriği'),
            'INVOICE_SEARCH_KEY' => [
                'READ_INCLUDED'          => true,
'READ_INCLUDEDSpecified' => true,
                'UUID'               => $uuid,
                'DIRECTION'          => $direction,
            ],
            'HEADER_ONLY'          => 'N',
            'INVOICE_CONTENT_TYPE' => 'HTML',
        ]]);
    } catch (SoapFault $e) {
        throw new RuntimeException('Fatura içeriği alınamadı: ' . $e->getMessage());
    } finally {
        $this->safeLogout($client, $sessionId);
    }

    $data = $this->normalizeSoapValue($response);

    $invoices = $data['INVOICE'] ?? null;
    if ($this->looksLikeSingleInvoice($invoices)) {
        $invoices = [$invoices];
    }

    if (!is_array($invoices) || empty($invoices)) {
        throw new RuntimeException('Fatura bulunamadı.');
    }

    $invoice = $invoices[0];
    $rawContent = $invoice['CONTENT'] ?? '';

// EDM bazen ['_' => 'base64data', 'type' => '...'] şeklinde array döner
if (is_array($rawContent)) {
    $content = (string) ($rawContent['_'] ?? $rawContent[0] ?? '');
} else {
    $content = (string) $rawContent;
}

    if ($content === '') {
        throw new RuntimeException('Fatura içeriği boş döndü.');
    }

    // EDM base64 ile dönebilir
    $decoded = base64_decode($content, true);
    return $decoded !== false ? $decoded : $content;
}
public function getKontor(): array
{
    $client    = $this->createClient();
    $sessionId = $this->login($client);

    try {
        return $this->checkCounter($client, $sessionId);
    } finally {
        $this->safeLogout($client, $sessionId);
    }
}

public function getFaturaKalemleri(string $uuid, string $direction): array
{
    $direction = strtoupper(trim($direction));
    if (!in_array($direction, ['IN', 'OUT'], true)) {
        throw new RuntimeException('Geçersiz yön bilgisi.');
    }
    if ($uuid === '') {
        throw new RuntimeException('UUID boş.');
    }

    $client    = $this->createClient();
    $sessionId = $this->login($client);

    try {
        $response = $client->__soapCall('GetInvoice', [[
            'REQUEST_HEADER'       => $this->buildRequestHeader($sessionId, 'Belgeç EDM fatura kalemleri'),
            'INVOICE_SEARCH_KEY'   => [
                'READ_INCLUDED'          => true,
'READ_INCLUDEDSpecified' => true,
                'UUID'                   => $uuid,
                'DIRECTION'              => $direction,
            ],
            'HEADER_ONLY'          => 'N',
            'INVOICE_CONTENT_TYPE' => 'XML',
        ]]);
    } catch (SoapFault $e) {
        throw new RuntimeException('Fatura XML alınamadı: ' . $e->getMessage());
    } finally {
        $this->safeLogout($client, $sessionId);
    }

    $data     = $this->normalizeSoapValue($response);
    $invoices = $data['INVOICE'] ?? null;
    if ($this->looksLikeSingleInvoice($invoices)) {
        $invoices = [$invoices];
    }
    if (!is_array($invoices) || empty($invoices)) {
        return [];
    }

    $invoice    = $invoices[0];
    $rawContent = $invoice['CONTENT'] ?? '';
    if (is_array($rawContent)) {
        $content = (string) ($rawContent['_'] ?? $rawContent[0] ?? '');
    } else {
        $content = (string) $rawContent;
    }
    if ($content === '') {
        return [];
    }

    $xml = base64_decode($content, true);
    if ($xml === false) {
        $xml = $content;
    }

    return $this->parseUblKalemler($xml);
}

private function parseUblKalemler(string $xml): array
{
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    if (!$dom->loadXML($xml)) {
        return [];
    }

    $xpath = new \DOMXPath($dom);
    $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
    $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

    $lineNodes = $xpath->query('//cac:InvoiceLine | //cac:CreditNoteLine');
    if (!$lineNodes || $lineNodes->length === 0) {
        return [];
    }

    $kalemler = [];
    foreach ($lineNodes as $line) {
        $stokKodu   = '';
        $urunAdi    = '';
        $miktar     = '1';
        $birimFiyat = '';
        $kdvOrani   = '20';

        $qty = $xpath->evaluate('string(cbc:InvoicedQuantity | cbc:CreditedQuantity)', $line);
        if ($qty !== '') $miktar = $qty;

        $name = $xpath->evaluate('string(cac:Item/cbc:Name)', $line);
        if ($name !== '') $urunAdi = $name;

        $sellerId = $xpath->evaluate('string(cac:Item/cac:SellersItemIdentification/cbc:ID)', $line);
        $buyerId  = $xpath->evaluate('string(cac:Item/cac:BuyersItemIdentification/cbc:ID)', $line);
        if ($sellerId !== '') $stokKodu = $sellerId;
        elseif ($buyerId !== '') $stokKodu = $buyerId;

        $price = $xpath->evaluate('string(cac:Price/cbc:PriceAmount)', $line);
        if ($price !== '') $birimFiyat = $price;

        $taxPct = $xpath->evaluate('string(cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:Percent)', $line);
        if ($taxPct !== '') $kdvOrani = $taxPct;

        if ($urunAdi === '' && $birimFiyat === '') {
            continue;
        }

        $kalemler[] = [
            'stok_kodu'   => $stokKodu,
            'urun_adi'    => $urunAdi,
            'miktar'      => $miktar,
            'birim_fiyat' => $birimFiyat,
            'kdv_orani'   => $kdvOrani,
        ];
    }

    return $kalemler;
}

    private function createClient(): SoapClient
    {
        if (!class_exists(SoapClient::class)) {
            throw new RuntimeException('SoapClient bulunamadı. Hosting veya PHP ortamında SOAP eklentisi aktif olmalı.');
        }

        try {
            return new SoapClient($this->getWsdlUrl(), [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'soap_version' => SOAP_1_1,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                    ],
                ]),
            ]);
        } catch (SoapFault $e) {
            throw new RuntimeException('EDM servisine bağlanılamadı: ' . $e->getMessage());
        }
    }

    private function getWsdlUrl(): string
    {
        $settings = $this->settingsService->all();
        $ortam = strtolower(trim((string) ($settings['edm_ortam'] ?? 'test')));

        if ($ortam === 'canli') {
            return 'https://portal2.edmbilisim.com.tr/EFaturaEDM/EFaturaEDM.svc?singleWsdl';
        }

        return 'https://test.edmbilisim.com.tr/EFaturaEDM21ea/EFaturaEDM.svc?singleWsdl';
    }

    private function login(SoapClient $client): string
    {
        $settings = $this->getValidatedSettings();

        try {
            $response = $client->__soapCall('Login', [[
                'REQUEST_HEADER' => $this->buildRequestHeader(null, 'Belgeç EDM bağlantı testi'),
                'USER_NAME' => $settings['edm_kullanici'],
                'PASSWORD' => $settings['edm_sifre'],
            ]]);
        } catch (SoapFault $e) {
            throw new RuntimeException('EDM giriş başarısız: ' . $e->getMessage());
        }

        $data = $this->normalizeSoapValue($response);
        $sessionId = trim((string) ($data['SESSION_ID'] ?? ''));

        if ($sessionId === '') {
            throw new RuntimeException('EDM oturumu açılamadı. SESSION_ID boş döndü.');
        }

        return $sessionId;
    }

    private function checkCounter(SoapClient $client, string $sessionId): array
    {
        try {
            $response = $client->__soapCall('CheckCounter', [[
                'REQUEST_HEADER' => $this->buildRequestHeader($sessionId, 'Belgeç EDM kontör kontrolü'),
            ]]);
        } catch (SoapFault $e) {
            throw new RuntimeException('EDM kontör bilgisi alınamadı: ' . $e->getMessage());
        }

        return $this->normalizeSoapValue($response);
    }

    private function getInvoiceHeaders(SoapClient $client, string $sessionId, string $direction, int $days): array
    {
        $end = new DateTimeImmutable('now');
        $start = $end->sub(new DateInterval('P' . max(1, $days) . 'D'));

        try {
            $response = $client->__soapCall('GetInvoice', [[
                'REQUEST_HEADER' => $this->buildRequestHeader($sessionId, 'Belgeç EDM fatura önizleme'),
                'INVOICE_SEARCH_KEY' => [
                    'READ_INCLUDED'          => true,
'READ_INCLUDEDSpecified' => true,
                    'DIRECTION' => $direction,
                    'CR_START_DATE' => $start->format(DateTimeInterface::ATOM),
                    'CR_START_DATESpecified' => true,
                    'CR_END_DATE' => $end->format(DateTimeInterface::ATOM),
                    'CR_END_DATESpecified' => true,
                    'ISARCHIVED' => false,
                    'ISARCHIVEDSpecified' => true,
                    'LIMIT' => 25,
                    'LIMITSpecified' => true,
                ],
                'HEADER_ONLY' => 'Y',
                'INVOICE_CONTENT_TYPE' => 'XML',
            ]]);
        } catch (SoapFault $e) {
            throw new RuntimeException('EDM fatura başlıkları alınamadı: ' . $e->getMessage());
        }

        $data = $this->normalizeSoapValue($response);
        $invoices = $data['INVOICE'] ?? [];

        if ($invoices === [] || $invoices === null) {
            return [];
        }

        if ($this->looksLikeSingleInvoice($invoices)) {
            $invoices = [$invoices];
        }

        if (!is_array($invoices)) {
            return [];
        }

        $rows = [];

        foreach ($invoices as $invoice) {
            if (!is_array($invoice)) {
                continue;
            }

            $header = $invoice['HEADER'] ?? [];
            if (!is_array($header)) {
                $header = [];
            }

            $rows[] = [
                'id' => (string) ($invoice['ID'] ?? ''),
                'uuid' => (string) ($invoice['UUID'] ?? ''),
                'trxid'      => (string) ($invoice['TRXID'] ?? ''),
'fatura_no'  => $this->extractFaturaNo($invoice, $header),
                'sender' => (string) ($header['SENDER'] ?? ''),
                'receiver' => (string) ($header['RECEIVER'] ?? ''),
                'supplier' => (string) ($header['SUPPLIER'] ?? ''),
                'customer' => (string) ($header['CUSTOMER'] ?? ''),
                'issue_date' => (string) ($header['ISSUE_DATE'] ?? ''),
                'payable_amount' => $this->extractAmount($header['PAYABLE_AMOUNT'] ?? null),
                'profile_id' => (string) ($header['PROFILEID'] ?? ''),
                'status' => (string) ($header['STATUS'] ?? ''),
                'status_description' => (string) ($header['STATUS_DESCRIPTION'] ?? ''),
                'invoice_type' => (string) ($header['INVOICE_TYPE'] ?? ''),
                'invoice_send_type' => (string) ($header['INVOICE_SEND_TYPE'] ?? ''),
                'from' => (string) ($header['FROM'] ?? ''),
                'to' => (string) ($header['TO'] ?? ''),
                'cdate' => (string) ($header['CDATE'] ?? ''),
                'invoice_id'           => (string) ($header['INVOICE_ID'] ?? ''),
'tax_exclusive_amount' => $this->extractAmount($header['TAX_EXCLUSIVE_AMOUNT'] ?? null),
            ];
        }

        usort($rows, fn($a, $b) => strcmp($b['issue_date'], $a['issue_date']));

return $rows;
    }
    
    private function getInvoiceHeadersByRange(SoapClient $client, string $sessionId, string $direction, DateTimeImmutable $start, DateTimeImmutable $end): array
{
    try {
        $response = $client->__soapCall('GetInvoice', [[
            'REQUEST_HEADER' => $this->buildRequestHeader($sessionId, 'Belgeç EDM fatura aralık sorgusu'),
            'INVOICE_SEARCH_KEY' => [
                'READ_INCLUDED'          => true,
'READ_INCLUDEDSpecified' => true,
    'DIRECTION'              => $direction,
    'CR_START_DATE'          => $start->format(DateTimeInterface::ATOM),
    'CR_START_DATESpecified' => true,
    'CR_END_DATE'            => $end->format(DateTimeInterface::ATOM),
    'CR_END_DATESpecified'   => true,
    'LIMIT'                  => 500,
    'LIMITSpecified'         => true,
],
            'HEADER_ONLY'          => 'Y',
            'INVOICE_CONTENT_TYPE' => 'XML',
        ]]);
    } catch (SoapFault $e) {
        throw new RuntimeException('EDM fatura başlıkları alınamadı: ' . $e->getMessage());
    }

    $data     = $this->normalizeSoapValue($response);
    $invoices = $data['INVOICE'] ?? [];

    if ($invoices === [] || $invoices === null) return [];
    if ($this->looksLikeSingleInvoice($invoices)) $invoices = [$invoices];
    if (!is_array($invoices)) return [];

    $rows = [];
    foreach ($invoices as $invoice) {
        if (!is_array($invoice)) continue;
        $header = is_array($invoice['HEADER'] ?? null) ? $invoice['HEADER'] : [];
        $rows[] = [
            'id'                  => (string) ($invoice['ID']    ?? ''),
            'uuid'                => (string) ($invoice['UUID']  ?? ''),
            'trxid'      => (string) ($invoice['TRXID'] ?? ''),
'fatura_no'  => $this->extractFaturaNo($invoice, $header),
            'sender'              => (string) ($header['SENDER']   ?? ''),
            'receiver'            => (string) ($header['RECEIVER'] ?? ''),
            'supplier'            => (string) ($header['SUPPLIER'] ?? ''),
            'customer'            => (string) ($header['CUSTOMER'] ?? ''),
            'issue_date'          => (string) ($header['ISSUE_DATE'] ?? ''),
            'payable_amount'      => $this->extractAmount($header['PAYABLE_AMOUNT'] ?? null),
            'profile_id'          => (string) ($header['PROFILEID']    ?? ''),
            'status'              => (string) ($header['STATUS']        ?? ''),
            'status_description'  => (string) ($header['STATUS_DESCRIPTION'] ?? ''),
            'invoice_type'        => (string) ($header['INVOICE_TYPE']      ?? ''),
            'invoice_send_type'   => (string) ($header['INVOICE_SEND_TYPE'] ?? ''),
            'from'                => (string) ($header['FROM']  ?? ''),
            'to'                  => (string) ($header['TO']    ?? ''),
            'cdate'               => (string) ($header['CDATE'] ?? ''),
            'invoice_id'           => (string) ($header['INVOICE_ID'] ?? ''),
'tax_exclusive_amount' => $this->extractAmount($header['TAX_EXCLUSIVE_AMOUNT'] ?? null),
        ];
    }

    usort($rows, fn($a, $b) => strcmp($b['issue_date'], $a['issue_date']));
    return $rows;
}

    private function safeLogout(SoapClient $client, string $sessionId): void
    {
        if ($sessionId === '') {
            return;
        }

        try {
            $client->__soapCall('Logout', [[
                'REQUEST_HEADER' => $this->buildRequestHeader($sessionId, 'Belgeç EDM oturum kapatma'),
            ]]);
        } catch (\Throwable $e) {
            // Sessiz geçiyoruz. Test akışını bozmasın.
        }
    }

    private function buildRequestHeader(?string $sessionId, string $reason): array
    {
        $header = [
            'CLIENT_TXN_ID' => $this->uuidV4(),
            'ACTION_DATE' => (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM),
            'REASON' => $reason,
            'APPLICATION_NAME' => 'Belgeç',
            'HOSTNAME' => gethostname() ?: 'belgec',
            'CHANNEL_NAME' => 'Belgeç',
            'COMPRESSED' => 'N',
        ];

        if ($sessionId !== null && $sessionId !== '') {
            $header['SESSION_ID'] = $sessionId;
        }

        return $header;
    }

    private function getValidatedSettings(): array
    {
        $settings = $this->settingsService->all();

        if (empty($settings['edm_aktif'])) {
            throw new RuntimeException('EDM entegrasyonu kapalı görünüyor. Önce Ayarlar ekranından aktif et.');
        }

        if (trim((string) ($settings['edm_kullanici'] ?? '')) === '') {
            throw new RuntimeException('EDM kullanıcı adı boş.');
        }

        if (trim((string) ($settings['edm_sifre'] ?? '')) === '') {
            throw new RuntimeException('EDM şifresi boş.');
        }

        if (trim((string) ($settings['edm_firma_vkn'] ?? '')) === '') {
            throw new RuntimeException('EDM firma VKN boş.');
        }

        return $settings;
    }

    private function normalizeSoapValue($value)
    {
        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (!is_array($value)) {
            return $value;
        }

        $normalized = [];

        foreach ($value as $key => $item) {
            $normalized[$key] = $this->normalizeSoapValue($item);
        }

        return $normalized;
    }

    private function looksLikeSingleInvoice($value): bool
    {
        return is_array($value) && array_key_exists('HEADER', $value);
    }
    
    private function extractFaturaNo(array $invoice, array $header): string
{
    // GIB fatura numarası harf içerir ama tire (-) içermez
    // UUID ise her zaman tire içerir → bu sayede ayırt edebiliriz
    $adaylar = [
        (string) ($invoice['INVOICE_ID'] ?? ''),
        (string) ($invoice['TRXID']      ?? ''),
        (string) ($invoice['ID']         ?? ''),
        (string) ($header['INVOICE_ID']  ?? ''),
        (string) ($header['ID']          ?? ''),
        (string) ($header['FATURA_NO']   ?? ''),
        (string) ($invoice['FATURA_NO']  ?? ''),
        (string) ($invoice['GIB_ID']     ?? ''),
    ];

    foreach ($adaylar as $aday) {
        $aday = trim($aday);
        if ($aday === '') continue;
        // Tire yoksa VE harf varsa → gerçek fatura numarasıdır
        if (!str_contains($aday, '-') && preg_match('/[A-Za-z]/', $aday)) {
            return $aday;
        }
    }

    return '';
}

    private function extractAmount($value): array
    {
        if (is_array($value)) {
            return [
                'value' => (string) ($value['_'] ?? $value[0] ?? ''),
                'currency' => (string) ($value['currencyID'] ?? ''),
            ];
        }

        return [
            'value' => (string) $value,
            'currency' => '',
        ];
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
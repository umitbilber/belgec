<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\CariServiceInterface;
use App\Interfaces\CariYaslandirmaServiceInterface;

class CariYaslandirmaController extends BaseController
{
    private CariYaslandirmaServiceInterface $service;
private CariServiceInterface $cariService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    CariYaslandirmaServiceInterface $service,
    CariServiceInterface $cariService
) {
    parent::__construct($settingsService);
    $this->service = $service;
    $this->cariService = $cariService;
}

    public function index(Request $request, Response $response): void
{
    $this->guardIzin($response, 'cari_yaslandirma.goruntule');

    $tip = trim((string) $request->query('tip', ''));
    $cariId = (int) $request->query('cari_id', 0);

    if (!in_array($tip, ['alis', 'satis'], true)) {
        $tip = '';
    }

    $rapor = $this->service->getReport($tip !== '' ? $tip : null, $cariId);
    $cariler = $this->cariService->getAll();

    $response->view('cari_yaslandirma.index', [
        'pageTitle' => 'Cari Yaşlandırma',
        'ayarlar' => $this->settingsService->all(),
        'rapor' => $rapor,
        'cariler' => $cariler,
        'include_modal_js' => true,
    ], 'layouts.app');
}
public function sendHatirlatma(Request $request, Response $response): void
{
    $this->guardIzin($response, 'cari_yaslandirma.goruntule');
    
    $this->guardCsrf($request, $response);

    $cariId   = (int)    $request->input('cari_id', 0);
    $faturaNo = trim((string) $request->input('fatura_no', ''));
    $metin    = trim((string) $request->input('metin', ''));
    $eposta   = trim((string) $request->input('eposta', ''));

    if ($cariId === 0 || $faturaNo === '' || $eposta === '') {
        $response->json(['ok' => false, 'message' => 'Eksik bilgi.'], 400);
        return;
    }

    try {
        $ayarlar  = $this->settingsService->all();
        $sirket   = $ayarlar['sirket_adi'] ?? 'Belgeç';
        $konu     = $sirket . ' — Vade Hatırlatması: ' . $faturaNo;

        $htmlMetin = '
            <div style="font-family:Arial,sans-serif;color:#333;line-height:1.7;">
                <h2 style="margin-bottom:12px;">Vade Hatırlatması</h2>
                <div style="margin-bottom:24px;">' . nl2br(htmlspecialchars($metin)) . '</div>
                <p>
                    <strong>' . htmlspecialchars($sirket) . '</strong><br>
                    ' . htmlspecialchars((string) ($ayarlar['telefon'] ?? '')) . '<br>
                    ' . htmlspecialchars((string) ($ayarlar['eposta'] ?? '')) . '
                </p>
            </div>';

        $mailService = new \App\Services\MailService();
        $mailService->sendHtml($eposta, $konu, $htmlMetin);

        $response->json(['ok' => true]);
    } catch (\Throwable $e) {
        $response->json(['ok' => false, 'message' => $e->getMessage()]);
    }
}
}
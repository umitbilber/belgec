<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use Throwable;
use App\Interfaces\MutabakatServiceInterface;

class MutabakatController extends BaseController
{
    private MutabakatServiceInterface $service;

    public function __construct(
    SettingsServiceInterface $settingsService,
    MutabakatServiceInterface $service
) {
    parent::__construct($settingsService);
    $this->service = $service;
}

    public function form(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'mutabakat.goruntule');

        $id = (int) $request->query('id', 0);
        $hata = (string) $request->query('hata', '');
        $bilgi = (string) $request->query('bilgi', '');

        $cari = $id > 0 ? $this->service->getCariById($id) : null;

        $response->view('mutabakat.form', [
            'pageTitle' => 'Mutabakat Gönder',
            'ayarlar' => $this->settingsService->all(),
            'cari' => $cari,
            'hata_mesaji' => $hata,
            'bilgi_mesaji' => $bilgi,
        ], 'layouts.app');
    }
    
    public function onizle(Request $request, Response $response): void
{
    $this->guardIzin($response, 'mutabakat.goruntule');
    
    $this->guardCsrf($request, $response);

    $id = (int) $request->input('cari_id', 0);

    try {
        $mailData = $this->service->buildMutabakatMail($id);
        $response->json(['ok' => true, 'konu' => $mailData['subject'], 'metin' => $mailData['onizleme_metni'] ?? '']);
    } catch (\Throwable $e) {
        $response->json(['ok' => false, 'message' => $e->getMessage()]);
    }
}

    public function send(Request $request, Response $response): void
{
    $this->guardIzin($response, 'mutabakat.goruntule');
    
    $this->guardCsrf($request, $response);

    $id        = (int) $request->input('cari_id', 0);
    $ozelMetin = trim((string) $request->input('mutabakat_metni', ''));

    try {
        $this->service->sendMutabakatMail($id, $ozelMetin);
        $response->redirect(url('cariler?bilgi=' . urlencode('Mutabakat e-postası başarıyla gönderildi.')));
    } catch (\Throwable $e) {
        $response->redirect(url('cariler?hata=' . urlencode($e->getMessage())));
    }
}

    public function reply(Request $request, Response $response): void
    {
       $this->guardCsrf($request, $response);
       
        $id = (int) $request->query('id', 0);
        $cevap = trim((string) $request->query('cevap', ''));

        $cari = $this->service->getCariById($id);

        if (!$cari || !in_array($cevap, ['evet', 'hayir'], true)) {
            $response->abort(404, 'Geçersiz mutabakat bağlantısı.');
        }

        if ($cevap === 'hayir' && $request->method() !== 'POST') {
            $response->view('mutabakat.reply_form', [
                'cari' => $cari,
                'cevap' => $cevap,
            ]);
            return;
        }

        $aciklama = '';
        if ($cevap === 'hayir') {
            $aciklama = trim((string) $request->input('aciklama', ''));
        }

        try {
            $this->service->sendReplyNotification($id, $cevap, $aciklama);

            $response->view('mutabakat.reply_result', [
                'ayarlar' => $this->settingsService->all(),
            ]);
        } catch (Throwable $e) {
            $response->abort(500, 'İşlem sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }
}
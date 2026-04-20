<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use Throwable;
use App\Interfaces\CariAktarimServiceInterface;

class CariAktarimController extends BaseController
{
    private CariAktarimServiceInterface $aktarimService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    CariAktarimServiceInterface $aktarimService
) {
    parent::__construct($settingsService);
    $this->aktarimService = $aktarimService;
}

    public function preview(Request $request, Response $response): void
    {
        $this->guard($response);
        
        $this->guardCsrf($request, $response);

        try {
            $sonuc = $this->aktarimService->storeUploadedStatement(
                (int) $request->input('cari_id', 0),
                $_FILES['ekstre_dosyasi'] ?? null
            );

            $onizleme = $this->aktarimService->buildPreviewFromStoredFile($sonuc);
            $this->aktarimService->addPreviewToQueue($onizleme);

            $_SESSION['wolvox_preview'] = $onizleme;

            $mesaj = sprintf(
                'Wolvox önizleme kuyruğa eklendi: %s',
                $sonuc['orijinal_ad']
            );

            $response->redirect(url('veri-aktarim?bilgi=' . urlencode($mesaj)));
        } catch (Throwable $e) {
            $response->redirect(url('veri-aktarim?hata=' . urlencode($e->getMessage())));
        }
    }

    public function execute(Request $request, Response $response): void
    {
        $this->guard($response);
        
        $this->guardCsrf($request, $response);

        try {
            $sonuc = $this->aktarimService->executeImportFromSession(
                (int) $request->input('cari_id', 0)
            );

            $mesaj = sprintf(
                'Wolvox aktarımı tamamlandı. Cari: %s | Satış: %d | Tahsilat: %d',
                $sonuc['cari_adi'],
                $sonuc['satis_sayisi'],
                $sonuc['tahsilat_sayisi']
            );

            $response->redirect(url('veri-aktarim?bilgi=' . urlencode($mesaj)));
        } catch (Throwable $e) {
            $response->redirect(url('veri-aktarim?hata=' . urlencode($e->getMessage())));
        }
    }

    public function executeAll(Request $request, Response $response): void
    {
        $this->guard($response);
        
        $this->guardCsrf($request, $response);

        try {
            $sonuc = $this->aktarimService->executeAllImportsFromQueue();

            $mesaj = sprintf(
                'Toplu Wolvox aktarımı tamamlandı. Başarılı: %d | Hatalı: %d',
                (int) ($sonuc['basarili'] ?? 0),
                (int) ($sonuc['hatali'] ?? 0)
            );

            $response->redirect(url('veri-aktarim?bilgi=' . urlencode($mesaj)));
        } catch (Throwable $e) {
            $response->redirect(url('veri-aktarim?hata=' . urlencode($e->getMessage())));
        }
    }

    public function removeQueueItem(Request $request, Response $response): void
    {
        $this->guard($response);

        try {
            $queueId = (string) $request->query('id', '');
            $this->aktarimService->removePreviewFromQueue($queueId);

            $response->redirect(url('veri-aktarim?bilgi=' . urlencode('Kuyruktaki kayıt kaldırıldı.')));
        } catch (Throwable $e) {
            $response->redirect(url('veri-aktarim?hata=' . urlencode($e->getMessage())));
        }
    }
}
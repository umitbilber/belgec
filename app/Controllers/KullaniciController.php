<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\KullaniciServiceInterface;

class KullaniciController extends BaseController
{
    private KullaniciServiceInterface $service;

    // Tüm izin tanımları
    public static function tumIzinler(): array
    {
        return [
            'Cariler'           => ['cariler.goruntule', 'cariler.ekle', 'cariler.duzenle', 'cariler.sil'],
            'Cari Hareketler'   => ['cari_hareketler.goruntule', 'cari_hareketler.duzenle', 'cari_hareketler.sil'],
            'Cari Yaşlandırma'  => ['cari_yaslandirma.goruntule'],
            'Stoklar'           => ['stoklar.goruntule', 'stoklar.ekle', 'stoklar.duzenle', 'stoklar.sil'],
            'Stok Hareketleri'  => ['stok_hareketleri.goruntule'],
            'Alış Faturaları'   => ['alis_fatura.goruntule', 'alis_fatura.ekle', 'alis_fatura.duzenle', 'alis_fatura.sil'],
            'Satış Faturaları'  => ['satis_fatura.goruntule', 'satis_fatura.ekle', 'satis_fatura.duzenle', 'satis_fatura.sil'],
            'Teklifler'         => ['teklifler.goruntule', 'teklifler.ekle', 'teklifler.duzenle', 'teklifler.sil'],
            'EDM Faturalar'     => ['edm.goruntule'],
            'Mutabakat'         => ['mutabakat.goruntule'],
            'Veri Aktarım'      => ['veri_aktarim.goruntule'],
            'Dashboard'         => ['dashboard.goruntule'],
            'Raporlar' => ['raporlar.goruntule'],
        ];
    }

    public function __construct(
        SettingsServiceInterface $settingsService,
        KullaniciServiceInterface $service
    ) {
        parent::__construct($settingsService);
        $this->service = $service;
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard($response);

        if (!$this->isYonetici()) {
            $response->abort(403, 'Bu sayfaya erişim yetkiniz yok.');
        }

        $bilgi = (string) $request->query('bilgi', '');
        $hata  = (string) $request->query('hata', '');

        $response->view('kullanici.index', [
            'pageTitle'    => 'Kullanıcı Yönetimi',
            'ayarlar'      => $this->settingsService->all(),
            'kullanicilar' => $this->service->getAll(),
            'tum_izinler'  => self::tumIzinler(),
            'bilgi_mesaji' => $bilgi,
            'hata_mesaji'  => $hata,
            'include_modal_js' => true,
        ], 'layouts.app');
    }

    public function store(Request $request, Response $response): void
    {
        $this->guard($response);
        $this->guardCsrf($request, $response);

        if (!$this->isYonetici()) {
            $response->abort(403);
        }

        try {
            $id = null;
            $this->service->create($request->input());
            // Yeni eklenen kullanıcının id'sini bul
            $tumKullanicilar = $this->service->getAll();
            $yeni = end($tumKullanicilar);
            $id = $yeni ? (int) $yeni['id'] : null;

            if ($id) {
                $izinler = array_keys(array_filter($request->input(), fn($v, $k) => str_contains($k, '.'), ARRAY_FILTER_USE_BOTH));
                $this->service->setIzinler($id, $izinler);
            }

            $this->auditLog('ekle', 'kullanicilar');
            $response->redirect(url('kullanicilar?bilgi=' . urlencode('Kullanıcı eklendi.')));
        } catch (\Throwable $e) {
            $response->redirect(url('kullanicilar?hata=' . urlencode($e->getMessage())));
        }
    }

    public function update(Request $request, Response $response): void
    {
        $this->guard($response);
        $this->guardCsrf($request, $response);

        if (!$this->isYonetici()) {
            $response->abort(403);
        }

        $id = (int) $request->input('kullanici_id', 0);

        try {
            $this->service->update($id, $request->input());

            $izinler = [];
            foreach ($request->input() as $k => $v) {
                if (str_contains($k, '.')) {
                    $izinler[] = $k;
                }
            }
            $this->service->setIzinler($id, $izinler);

            $this->auditLog('duzenle', 'kullanicilar');
            $response->redirect(url('kullanicilar?bilgi=' . urlencode('Kullanıcı güncellendi.')));
        } catch (\Throwable $e) {
            $response->redirect(url('kullanicilar?hata=' . urlencode($e->getMessage())));
        }
    }

    public function delete(Request $request, Response $response): void
    {
        $this->guard($response);
        $this->guardCsrf($request, $response);

        if (!$this->isYonetici()) {
            $response->abort(403);
        }

        $id = (int) $request->input('kullanici_id', 0);

        try {
            $this->service->delete($id);
            $this->auditLog('sil', 'kullanicilar');
            $response->redirect(url('kullanicilar?bilgi=' . urlencode('Kullanıcı silindi.')));
        } catch (\Throwable $e) {
            $response->redirect(url('kullanicilar?hata=' . urlencode($e->getMessage())));
        }
    }
    public function getIzinler(Request $request, Response $response): void
{
    $this->guard($response);

    if (!$this->isYonetici()) {
        $response->json(['ok' => false], 403);
        return;
    }

    $id = (int) $request->query('id', 0);
    $response->json([
        'ok'      => true,
        'izinler' => $this->service->getIzinler($id),
    ]);
}
}
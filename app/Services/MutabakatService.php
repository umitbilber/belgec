<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\MailServiceInterface;
use App\Interfaces\MutabakatRepositoryInterface;
use App\Interfaces\MutabakatServiceInterface;

class MutabakatService implements MutabakatServiceInterface
{
    private MutabakatRepositoryInterface $repository;
private SettingsService $settingsService;
private MailServiceInterface $mailService;

    public function __construct(
    MutabakatRepositoryInterface $repository,
    SettingsServiceInterface $settingsService,
    MailServiceInterface $mailService
) {
    $this->repository = $repository;
    $this->settingsService = $settingsService;
    $this->mailService = $mailService;
}

    public function getCariById(int $id): ?array
    {
        return $this->repository->findCariById($id);
    }

    public function buildMutabakatMail(int $cariId, string $ozelMetin = ''): array
{
    $ayarlar = $this->settingsService->all();
    $cari    = $this->repository->findCariById($cariId);

    if (!$cari) throw new RuntimeException('Cari bulunamadı.');

    $to = trim((string) ($cari['eposta'] ?? ''));
    if ($to === '') throw new RuntimeException('Bu cari için kayıtlı e-posta adresi bulunmuyor.');

    $baseUrl   = $this->guessBaseUrl();
    $evetLink  = $baseUrl . '/mutabakat/cevap?id=' . $cariId . '&cevap=evet';
    $hayirLink = $baseUrl . '/mutabakat/cevap?id=' . $cariId . '&cevap=hayir';

    $bakiyeBilgisi = $this->formatBakiye((float) ($cari['bakiye'] ?? 0));
    $konu          = ($ayarlar['sirket_adi'] ?? 'Belgeç') . ' - Bakiye Mutabakat Bildirimi';

    // Özel metin varsa onu kullan, yoksa ayarlardan al, o da yoksa varsayılan
    $varsayilanMetin = trim((string) ($ayarlar['varsayilan_mutabakat_metni'] ?? ''));
    if ($varsayilanMetin === '') {
        $varsayilanMetin = "Sayın {cari_adi},\n\nSistemimizde cari hesabınıza ait güncel bakiye {bakiye} olarak görünmektedir.\n\nBu bakiye ile mutabık iseniz veya değilseniz lütfen aşağıdaki butonlardan yanıt veriniz.\n\nİyi çalışmalar dileriz.";
    }

    $kullanilacakMetin = $ozelMetin !== '' ? $ozelMetin : $varsayilanMetin;

    // Yer tutucuları doldur
    $doluMetin = str_replace(
    ['{cari_adi}', '{bakiye}'],
    [(string) $cari['ad_soyad'], $bakiyeBilgisi['metin']],
    $kullanilacakMetin
);

    $mesaj = '
        <div style="font-family:Arial,sans-serif;color:#333;line-height:1.7;">
            <h2 style="margin-bottom:12px;">Cari Mutabakat Bildirimi</h2>
            <div style="margin-bottom:24px;">' . nl2br(htmlspecialchars($doluMetin)) . '</div>
            <div style="margin:24px 0;">
                <a href="' . htmlspecialchars($evetLink) . '" style="display:inline-block;padding:12px 18px;background:#27ae60;color:#fff;text-decoration:none;border-radius:6px;margin-right:10px;">Evet, Mutabıkız</a>
                <a href="' . htmlspecialchars($hayirLink) . '" style="display:inline-block;padding:12px 18px;background:#e74c3c;color:#fff;text-decoration:none;border-radius:6px;">Hayır, Mutabık Değiliz</a>
            </div>
            <p><strong>' . htmlspecialchars((string) ($ayarlar['sirket_adi'] ?? '')) . '</strong><br>
            ' . htmlspecialchars((string) ($ayarlar['telefon'] ?? '')) . '<br>
            ' . htmlspecialchars((string) ($ayarlar['eposta'] ?? '')) . '</p>
        </div>';

    return [
        'cari'            => $cari,
        'to'              => $to,
        'subject'         => $konu,
        'body'            => $mesaj,
        'onizleme_metni'  => $kullanilacakMetin,
        'varsayilan_metin'=> $varsayilanMetin,
    ];
}

    public function sendMutabakatMail(int $cariId, string $ozelMetin = ''): void
{
    $mailData = $this->buildMutabakatMail($cariId, $ozelMetin);
    $this->mailService->sendHtml($mailData['to'], $mailData['subject'], $mailData['body']);
}

    public function sendReplyNotification(int $cariId, string $cevap, string $aciklama = ''): void
    {
        $ayarlar = $this->settingsService->all();
        $cari = $this->repository->findCariById($cariId);

        if (!$cari) {
            throw new RuntimeException('Cari bulunamadı.');
        }

        $firmaEposta = trim((string) ($ayarlar['eposta'] ?? ''));
        if ($firmaEposta === '') {
            throw new RuntimeException('Firma e-posta adresi ayarlarda tanımlı değil.');
        }

        $cevapDurumu = $cevap === 'evet'
            ? '<span style="color:green; font-weight:bold;">✓ EVET, MUTABIKIZ</span>'
            : '<span style="color:red; font-weight:bold;">✗ HAYIR, MUTABIK DEĞİLİZ</span>';

        $konu = 'Mutabakat Yanıtı Geldi: ' . $cari['ad_soyad'];

        $mesaj = '<h3>Mutabakat Yanıtı</h3>';
        $mesaj .= '<p>Müşteriniz <strong>' . htmlspecialchars((string) $cari['ad_soyad']) . '</strong> gönderdiğiniz mutabakat formunu yanıtladı.</p>';
        $mesaj .= '<p>Müşterinin Cevabı: <span style="font-size:16px;">' . $cevapDurumu . '</span></p>';

        if ($cevap === 'hayir' && $aciklama !== '') {
            $mesaj .= '<p><strong>Müşterinin İtiraz Nedeni / Açıklaması:</strong></p>';
            $mesaj .= '<div style="background:#fff3f3; padding:15px; border-left:4px solid #e74c3c; font-style:italic; color:#333; margin-bottom:15px;">'
                . nl2br(htmlspecialchars($aciklama))
                . '</div>';
        }

        $bakiyeBilgisi = $this->formatBakiye((float) ($cari['bakiye'] ?? 0));
        $mesaj .= '<p>Sistemdeki Güncel Bakiye: <strong style="color:' . $bakiyeBilgisi['renk'] . ';">' . $bakiyeBilgisi['metin'] . '</strong></p>';

        $this->mailService->sendHtml($firmaEposta, $konu, $mesaj);
    }

    private function formatBakiye(float $bakiye): array
    {
        if ($bakiye > 0) {
            $renk = '#16a34a';
            $etiket = 'B';
        } elseif ($bakiye < 0) {
            $renk = '#d61f1f';
            $etiket = 'A';
        } else {
            $renk = '#444444';
            $etiket = '';
        }

        $metin = number_format(abs($bakiye), 2, ',', '.') . ' TL';

        if ($etiket !== '') {
            $metin .= ' (' . $etiket . ')';
        }

        return [
            'renk' => $renk,
            'etiket' => $etiket,
            'metin' => $metin,
        ];
    }

    private function guessBaseUrl(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $app = config('app');
        $baseUrl = rtrim((string) ($app['base_url'] ?? ''), '/');

        return $https . '://' . $host . ($baseUrl === '' ? '' : $baseUrl);
    }
}
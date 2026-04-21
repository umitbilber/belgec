<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use ZipArchive;

class GuncellemeUygulamaService
{
    private const ATLANAN_DOSYALAR = [
        'ayarlar.json',
        'veritabani.sqlite',
        'database.sqlite',
        'edm_goruldu.json',
        'edm_kontrol_cache.json',
        'guncelleme_cache.json',
        'error_log',
    ];

    private const ATLANAN_KLASORLER = [
        'storage/backups',
        'storage/cache',
        'storage/logs',
        '.git',
    ];

    private string $jobDir;
    private string $backupDir;
    private string $tmpDir;

    public function __construct()
    {
        $this->jobDir    = BASE_PATH . '/storage/update_jobs';
        $this->backupDir = BASE_PATH . '/storage/update_backup';
        $this->tmpDir    = BASE_PATH . '/storage/update_tmp';

        foreach ([$this->jobDir, $this->backupDir, $this->tmpDir] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }

    public function calistir(string $jobId, string $zipUrl, string $hedefSurum): void
    {
        $this->durumGuncelle($jobId, 'baslatildi', 0, 'Güncelleme başlatılıyor');

        try {
            $zipDosya = $this->tmpDir . '/' . $jobId . '.zip';

            $this->durumGuncelle($jobId, 'indiriliyor', 10, 'Güncelleme paketi indiriliyor');
            $this->indir($zipUrl, $zipDosya);

            $this->durumGuncelle($jobId, 'yedekleniyor', 30, 'Mevcut dosyaların yedeği alınıyor');
            $yedekKlasor = $this->yedekAl($jobId);

            $this->durumGuncelle($jobId, 'aciliyor', 50, 'Paket açılıyor');
            $acilmisKlasor = $this->zipAc($jobId, $zipDosya);

            $this->durumGuncelle($jobId, 'uygulaniyor', 70, 'Yeni dosyalar kopyalanıyor');
            $this->dosyalariKopyala($acilmisKlasor, BASE_PATH);

            $this->durumGuncelle($jobId, 'migration', 85, 'Veritabanı güncellemesi yapılıyor');
            $this->migrationCalistir();

            $this->durumGuncelle($jobId, 'temizleniyor', 95, 'Geçici dosyalar temizleniyor');
            $this->temizle($zipDosya, $acilmisKlasor);

            $this->durumGuncelle($jobId, 'tamamlandi', 100, "Güncelleme başarılı. Yeni sürüm: $hedefSurum", [
                'yedek_klasor' => $yedekKlasor,
                'hedef_surum'  => $hedefSurum,
            ]);
        } catch (\Throwable $e) {
            $this->durumGuncelle($jobId, 'hata', 0, 'HATA: ' . $e->getMessage());
            // Rollback dene
            try {
                if (!empty($yedekKlasor) && is_dir($yedekKlasor)) {
                    $this->durumGuncelle($jobId, 'geri_aliniyor', 50, 'Hata nedeniyle yedekten geri alınıyor');
                    $this->yedektenGeriAl($yedekKlasor);
                    $this->durumGuncelle($jobId, 'geri_alindi', 100, 'Yedekten geri alındı. Sistem eski haline döndü.');
                }
            } catch (\Throwable $e2) {
                $this->durumGuncelle($jobId, 'kritik_hata', 0, 'Rollback da başarısız: ' . $e2->getMessage());
            }
        }
    }

    public function durumOku(string $jobId): ?array
    {
        $dosya = $this->jobDir . '/' . $jobId . '.json';
        if (!file_exists($dosya)) return null;
        $data = json_decode((string) file_get_contents($dosya), true);
        return is_array($data) ? $data : null;
    }

    private function durumGuncelle(string $jobId, string $adim, int $yuzde, string $mesaj, array $extra = []): void
    {
        $dosya = $this->jobDir . '/' . $jobId . '.json';
        $mevcut = $this->durumOku($jobId) ?? ['log' => []];

        $mevcut['job_id']    = $jobId;
        $mevcut['adim']      = $adim;
        $mevcut['yuzde']     = $yuzde;
        $mevcut['mesaj']     = $mesaj;
        $mevcut['zaman']     = date('Y-m-d H:i:s');
        $mevcut['log'][]     = ['zaman' => date('H:i:s'), 'adim' => $adim, 'mesaj' => $mesaj];

        foreach ($extra as $k => $v) {
            $mevcut[$k] = $v;
        }

        @file_put_contents($dosya, json_encode($mevcut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function indir(string $url, string $hedefDosya): void
    {
        $fp = fopen($hedefDosya, 'w');
        if (!$fp) throw new RuntimeException('Hedef dosya açılamadı');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => ['User-Agent: Belgec-Updater'],
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$ok) throw new RuntimeException('İndirme başarısız: ' . $err);
        if (filesize($hedefDosya) < 1000) throw new RuntimeException('İndirilen zip çok küçük, bozuk olabilir.');
    }

    private function yedekAl(string $jobId): string
    {
        $hedef = $this->backupDir . '/' . $jobId;
        if (!@mkdir($hedef, 0755, true) && !is_dir($hedef)) {
            throw new RuntimeException('Yedek klasörü oluşturulamadı');
        }

        $this->klasorKopyala(BASE_PATH, $hedef, true);
        return $hedef;
    }

    private function zipAc(string $jobId, string $zipDosya): string
    {
        $hedef = $this->tmpDir . '/' . $jobId . '_extract';
        if (!@mkdir($hedef, 0755, true) && !is_dir($hedef)) {
            throw new RuntimeException('Geçici klasör oluşturulamadı');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipDosya) !== true) {
            throw new RuntimeException('Zip açılamadı');
        }
        $zip->extractTo($hedef);
        $zip->close();

        // Zip içinde tek bir kök klasör varsa (örn: github source-code zip'i) içeri girelim
        $icerik = array_diff(scandir($hedef) ?: [], ['.', '..']);
        if (count($icerik) === 1) {
            $tek = $hedef . '/' . reset($icerik);
            if (is_dir($tek)) {
                return $tek;
            }
        }

        return $hedef;
    }

    private function dosyalariKopyala(string $kaynak, string $hedef): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($kaynak, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $rel = substr($item->getPathname(), strlen($kaynak) + 1);
            $rel = str_replace('\\', '/', $rel);

            if ($this->atlamaGerekli($rel)) continue;

            $hedefYol = $hedef . '/' . $rel;

            if ($item->isDir()) {
                if (!is_dir($hedefYol)) @mkdir($hedefYol, 0755, true);
            } else {
                $hedefKlasor = dirname($hedefYol);
                if (!is_dir($hedefKlasor)) @mkdir($hedefKlasor, 0755, true);
                copy($item->getPathname(), $hedefYol);
            }
        }
    }

    private function klasorKopyala(string $kaynak, string $hedef, bool $atlaHassasOlani = false): void
    {
        if (!is_dir($hedef)) @mkdir($hedef, 0755, true);

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($kaynak, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $rel = substr($item->getPathname(), strlen($kaynak) + 1);
            $rel = str_replace('\\', '/', $rel);

            // Yedek klasörünü yedekleme (sonsuz döngü olmasın)
            if (strpos($rel, 'storage/update_') === 0) continue;
            if (strpos($rel, 'storage/backups') === 0) continue;

            $hedefYol = $hedef . '/' . $rel;

            if ($item->isDir()) {
                if (!is_dir($hedefYol)) @mkdir($hedefYol, 0755, true);
            } else {
                $hedefKlasor = dirname($hedefYol);
                if (!is_dir($hedefKlasor)) @mkdir($hedefKlasor, 0755, true);
                copy($item->getPathname(), $hedefYol);
            }
        }
    }

    private function atlamaGerekli(string $rel): bool
    {
        foreach (self::ATLANAN_DOSYALAR as $d) {
            if ($rel === $d) return true;
        }
        foreach (self::ATLANAN_KLASORLER as $k) {
            if ($rel === $k || strpos($rel, $k . '/') === 0) return true;
        }
        return false;
    }

    private function yedektenGeriAl(string $yedekKlasor): void
    {
        $this->klasorKopyala($yedekKlasor, BASE_PATH, false);
    }

    private function migrationCalistir(): void
    {
        if (!class_exists('\App\Core\Migrator')) {
            require_once BASE_PATH . '/app/Core/Migrator.php';
        }
        $m = new \App\Core\Migrator();
        $m->run();
    }

    private function temizle(string $zipDosya, string $acilmisKlasor): void
    {
        if (file_exists($zipDosya)) @unlink($zipDosya);
        if (is_dir($acilmisKlasor)) $this->klasorSil(dirname($acilmisKlasor) === $this->tmpDir ? $acilmisKlasor : dirname($acilmisKlasor));
    }

    private function klasorSil(string $klasor): void
    {
        if (!is_dir($klasor)) return;
        $items = array_diff(scandir($klasor) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $yol = $klasor . '/' . $item;
            is_dir($yol) ? $this->klasorSil($yol) : @unlink($yol);
        }
        @rmdir($klasor);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Interfaces\SettingsServiceInterface;

class SettingsService implements SettingsServiceInterface
{
    private string $settingsPath;
    private ?array $cache = null;

    public function __construct()
    {
        $this->settingsPath = BASE_PATH . '/ayarlar.json';
    }

    public function getPath(): string
    {
        return $this->settingsPath;
    }

    public function exists(): bool
    {
        return file_exists($this->settingsPath);
    }

    public function all(): array
{
    if ($this->cache !== null) {
        return $this->cache;
    }

    if (!$this->exists()) {
        return $this->cache = $this->getDefaultSettings();
    }

    $raw = file_get_contents($this->settingsPath);
    $data = json_decode((string) $raw, true);

    if (!is_array($data)) {
        return $this->cache = $this->getDefaultSettings();
    }

    return $this->cache = $this->normalizeSettings($data);
}

    public function isInstalled(): bool
    {
        $settings = $this->all();
        return !empty($settings['kurulum_tamamlandi']);
    }

    public function save(array $data): void
    {
        $json = json_encode(
            $this->normalizeSettings($data),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            throw new RuntimeException('Ayarlar JSON formatına dönüştürülemedi.');
        }

        $result = file_put_contents($this->settingsPath, $json, LOCK_EX);
        $this->cache = null;

        if ($result === false) {
            throw new RuntimeException('Ayarlar dosyası kaydedilemedi.');
        }
    }

    public function install(array $input): void
    {
        $sifre = trim((string) ($input['sifre'] ?? ''));

        if ($sifre === '') {
            throw new RuntimeException('Yönetici şifresi boş bırakılamaz.');
        }

        $newSettings = [
            'sirket_adi' => trim((string) ($input['sirket_adi'] ?? '')),
            'logo_url' => trim((string) ($input['logo_url'] ?? '')),
            'tema_rengi' => trim((string) ($input['tema_rengi'] ?? '#3498db')),
            'eposta' => trim((string) ($input['eposta'] ?? '')),
            'telefon' => trim((string) ($input['telefon'] ?? '')),
            'adres' => trim((string) ($input['adres'] ?? '')),
            'vergi_no' => trim((string) ($input['vergi_no'] ?? '')),
            'vergi_dairesi' => trim((string) ($input['vergi_dairesi'] ?? '')),
            'web_sitesi' => trim((string) ($input['web_sitesi'] ?? '')),
            'smtp_host' => trim((string) ($input['smtp_host'] ?? '')),
            'smtp_mail' => trim((string) ($input['smtp_mail'] ?? '')),
            'smtp_sifre' => trim((string) ($input['smtp_sifre'] ?? '')),
            'varsayilan_teklif_sartlari' => "• Fiyatlarımız %20 KDV hariç fiyatlardır.\n• Teklif geçerlilik süresi 3 iş günüdür.\n• Euro '€' fiyatlar fatura tarihindeki T.C.M.B döviz satış kuru ile 'TL'ye çevrilir.",
            'varsayilan_mutabakat_metni' => "Sayın {cari_adi},\n\nSistemimizde cari hesabınıza ait güncel bakiye {bakiye} olarak görünmektedir.\n\nBu bakiye ile mutabık iseniz veya değilseniz lütfen aşağıdaki butonlardan yanıt veriniz.\n\nİyi çalışmalar dileriz.",
            'yonetici_sifre' => password_hash($sifre, PASSWORD_DEFAULT),
            'favoriler' => [],
            'db_driver' => in_array(($input['db_driver'] ?? 'sqlite'), ['sqlite', 'mysql'], true)
                ? $input['db_driver']
                : 'sqlite',
            'db_mysql' => [
                'host'     => trim((string) ($input['db_mysql_host']     ?? '127.0.0.1')),
                'port'     => (int)    ($input['db_mysql_port']     ?? 3306),
                'database' => trim((string) ($input['db_mysql_database'] ?? 'belgec')),
                'username' => trim((string) ($input['db_mysql_username'] ?? '')),
                'password' => (string) ($input['db_mysql_password'] ?? ''),
                'charset'  => 'utf8mb4',
            ],
            'kurulum_tamamlandi' => true,
        ];

        $this->save($newSettings);
    }

    public function verifyAdminPassword(string $password): bool
    {
        $settings = $this->all();
        $hash = (string) ($settings['yonetici_sifre'] ?? '');

        if ($hash === '') {
            return false;
        }

        return password_verify($password, $hash);
    }

    public function updateGeneralSettings(array $input): void
    {
        $current = $this->all();

        $current['sirket_adi'] = trim((string) ($input['sirket_adi'] ?? ''));
        $current['logo_url'] = trim((string) ($input['logo_url'] ?? ''));
        $current['tema_rengi'] = trim((string) ($input['tema_rengi'] ?? ($current['tema_rengi'] ?? '#3498db')));
        $current['telefon'] = trim((string) ($input['telefon'] ?? ''));
        $current['eposta'] = trim((string) ($input['eposta'] ?? ''));
        $current['adres'] = trim((string) ($input['adres'] ?? ''));
        $current['vergi_no'] = trim((string) ($input['vergi_no'] ?? ''));
        $current['vergi_dairesi'] = trim((string) ($input['vergi_dairesi'] ?? ''));
        $current['web_sitesi'] = trim((string) ($input['web_sitesi'] ?? ''));
        $current['smtp_host'] = trim((string) ($input['smtp_host'] ?? ''));
        $current['smtp_mail'] = trim((string) ($input['smtp_mail'] ?? ''));
        $current['varsayilan_teklif_sartlari'] = trim((string) ($input['varsayilan_teklif_sartlari'] ?? ($current['varsayilan_teklif_sartlari'] ?? '')));
        $current['varsayilan_mutabakat_metni'] = trim((string) ($input['varsayilan_mutabakat_metni'] ?? ($current['varsayilan_mutabakat_metni'] ?? '')));

        $smtpSifre = trim((string) ($input['smtp_sifre'] ?? ''));
        if ($smtpSifre !== '') {
            $current['smtp_sifre'] = $smtpSifre;
        }
        $current['edm_aktif'] = (string) ($input['edm_aktif'] ?? '0') === '1';
        $current['yedek_sikligi']  = trim((string) ($input['yedek_sikligi'] ?? 'haftalik'));
$current['yedek_max_adet'] = max(1, min(10, (int) ($input['yedek_max_adet'] ?? 5)));
$current['edm_ortam'] = trim((string) ($input['edm_ortam'] ?? ($current['edm_ortam'] ?? 'test')));
$current['edm_kullanici'] = trim((string) ($input['edm_kullanici'] ?? ''));
$current['edm_firma_vkn'] = trim((string) ($input['edm_firma_vkn'] ?? ''));
$edmSifre = trim((string) ($input['edm_sifre'] ?? ''));
if ($edmSifre !== '') {
    $current['edm_sifre'] = $edmSifre;
}

        $this->save($current);
    }

    public function addFavorite(string $modul): void
    {
        $settings = $this->all();

        if (!in_array($modul, $settings['favoriler'], true)) {
            $settings['favoriler'][] = $modul;
        }

        $this->save($settings);
    }

    public function removeFavorite(string $modul): void
    {
        $settings = $this->all();

        $settings['favoriler'] = array_values(array_filter(
            $settings['favoriler'],
            fn ($item) => (string) $item !== $modul
        ));

        $this->save($settings);
    }

    private function getDefaultSettings(): array
    {
        return [
            'sirket_adi' => '',
            'logo_url' => '',
            'tema_rengi' => '#3498db',
            'eposta' => '',
            'telefon' => '',
            'adres' => '',
            'vergi_no' => '',
            'vergi_dairesi' => '',
            'web_sitesi' => '',
            'smtp_host' => '',
            'smtp_mail' => '',
            'smtp_sifre' => '',
            'varsayilan_teklif_sartlari' => '',
            'varsayilan_mutabakat_metni' => '',
            'yonetici_sifre' => '',
            'favoriler' => [],
            'edm_aktif' => false,
'edm_ortam' => 'test',
'edm_kullanici' => '',
'edm_sifre' => '',
'edm_firma_vkn' => '',
'edm_son_gelen_sync' => '',
'edm_son_giden_sync' => '',
            'db_driver' => 'sqlite',
            'db_mysql' => [
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'belgec',
                'username' => '',
                'password' => '',
                'charset' => 'utf8mb4',
            ],
            'kurulum_tamamlandi' => false,
        ];
    }

    private function normalizeSettings(array $settings): array
    {
        $normalized = array_merge($this->getDefaultSettings(), $settings);

        if (!is_array($normalized['favoriler'])) {
            $normalized['favoriler'] = [];
        }

        $normalized['sirket_adi'] = trim((string) $normalized['sirket_adi']);
        $normalized['logo_url'] = trim((string) $normalized['logo_url']);
        $normalized['tema_rengi'] = trim((string) $normalized['tema_rengi']) ?: '#3498db';
        $normalized['eposta'] = trim((string) $normalized['eposta']);
        $normalized['telefon'] = trim((string) $normalized['telefon']);
        $normalized['adres'] = trim((string) $normalized['adres']);
        $normalized['vergi_no'] = trim((string) $normalized['vergi_no']);
        $normalized['vergi_dairesi'] = trim((string) $normalized['vergi_dairesi']);
        $normalized['web_sitesi'] = trim((string) $normalized['web_sitesi']);
        $normalized['smtp_host'] = trim((string) $normalized['smtp_host']);
        $normalized['smtp_mail'] = trim((string) $normalized['smtp_mail']);
        $normalized['smtp_sifre'] = (string) $normalized['smtp_sifre'];
        $normalized['varsayilan_teklif_sartlari'] = trim((string) $normalized['varsayilan_teklif_sartlari']);
        $normalized['varsayilan_mutabakat_metni'] = trim((string) $normalized['varsayilan_mutabakat_metni']);
        $normalized['yonetici_sifre'] = (string) $normalized['yonetici_sifre'];
        $normalized['edm_aktif'] = (bool) $normalized['edm_aktif'];
$normalized['edm_ortam'] = trim((string) $normalized['edm_ortam']) ?: 'test';
$normalized['edm_kullanici'] = trim((string) $normalized['edm_kullanici']);
$normalized['edm_sifre'] = (string) $normalized['edm_sifre'];
$normalized['edm_firma_vkn'] = trim((string) $normalized['edm_firma_vkn']);
$normalized['edm_son_gelen_sync'] = trim((string) $normalized['edm_son_gelen_sync']);
$normalized['edm_son_giden_sync'] = trim((string) $normalized['edm_son_giden_sync']);

        $normalized['db_driver'] = in_array(($normalized['db_driver'] ?? 'sqlite'), ['sqlite', 'mysql'], true)
            ? $normalized['db_driver']
            : 'sqlite';

        $mysqlAyar = is_array($normalized['db_mysql'] ?? null) ? $normalized['db_mysql'] : [];
        $normalized['db_mysql'] = [
            'host'     => trim((string) ($mysqlAyar['host']     ?? '127.0.0.1')),
            'port'     => (int)    ($mysqlAyar['port']     ?? 3306),
            'database' => trim((string) ($mysqlAyar['database'] ?? 'belgec')),
            'username' => trim((string) ($mysqlAyar['username'] ?? '')),
            'password' => (string) ($mysqlAyar['password'] ?? ''),
            'charset'  => trim((string) ($mysqlAyar['charset']  ?? 'utf8mb4')),
        ];

        $normalized['kurulum_tamamlandi'] = (bool) $normalized['kurulum_tamamlandi'];

        return $normalized;
    }
}

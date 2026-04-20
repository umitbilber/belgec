<?php

declare(strict_types=1);

namespace App\Services;

class GuncellemeService
{
    private const GITHUB_USER = 'umitbilber';
    private const GITHUB_REPO = 'belgec';
    private const CACHE_FILE = 'guncelleme_cache.json';
    private const CACHE_TTL = 86400;

    private string $cachePath;

    public function __construct()
    {
        $this->cachePath = BASE_PATH . '/' . self::CACHE_FILE;
    }

    public function mevcutSurum(): string
    {
        $config = require BASE_PATH . '/config/app.php';
        return (string) ($config['version'] ?? '1.0.0');
    }

    public function guncellemeVarMi(bool $cacheByPass = false): array
    {
        if (!$cacheByPass && $this->cacheGecerli()) {
            $cached = json_decode((string) file_get_contents($this->cachePath), true);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $sonuc = [
            'mevcut_surum' => $this->mevcutSurum(),
            'son_surum' => null,
            'guncelleme_var' => false,
            'release_url' => null,
            'indirme_url' => null,
            'notlar' => '',
            'kontrol_zamani' => date('Y-m-d H:i:s'),
            'hata' => null,
        ];

        try {
            $release = $this->sonReleaseGetir();

            if ($release === null) {
                $sonuc['hata'] = 'Release bulunamadi.';
            } else {
                $sonSurum = ltrim((string) ($release['tag_name'] ?? ''), 'vV');
                $sonuc['son_surum']      = $sonSurum;
                $sonuc['release_url']    = (string) ($release['html_url'] ?? '');
                $sonuc['notlar']         = (string) ($release['body'] ?? '');
                $sonuc['indirme_url']    = $this->zipUrlBul($release);
                $sonuc['guncelleme_var'] = version_compare($this->mevcutSurum(), $sonSurum) < 0;
            }
        } catch (\Throwable $e) {
            $sonuc['hata'] = $e->getMessage();
        }

        @file_put_contents($this->cachePath, json_encode($sonuc, JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $sonuc;
    }

    private function cacheGecerli(): bool
    {
        return file_exists($this->cachePath) && (time() - filemtime($this->cachePath)) < self::CACHE_TTL;
    }

    private function sonReleaseGetir(): ?array
    {
        $url = sprintf('https://api.github.com/repos/%s/%s/releases/latest', self::GITHUB_USER, self::GITHUB_REPO);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "User-Agent: Belgec-Updater\r\nAccept: application/vnd.github+json\r\n",
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            throw new \RuntimeException('GitHub API ulasilamadi.');
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    private function zipUrlBul(array $release): ?string
    {
        $assets = $release['assets'] ?? [];
        if (is_array($assets)) {
            foreach ($assets as $asset) {
                $name = (string) ($asset['name'] ?? '');
                if (str_ends_with(strtolower($name), '.zip')) {
                    return (string) ($asset['browser_download_url'] ?? '');
                }
            }
        }
        return (string) ($release['zipball_url'] ?? null);
    }
}
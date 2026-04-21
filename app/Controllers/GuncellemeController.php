<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Services\GuncellemeService;

class GuncellemeController extends BaseController
{
    private GuncellemeService $guncellemeService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        GuncellemeService $guncellemeService
    ) {
        parent::__construct($settingsService);
        $this->guncellemeService = $guncellemeService;
    }

    public function kontrol(Request $request, Response $response): void
    {
        $this->guard($response);

        $force = (string) $request->query('force', '0') === '1';
        $sonuc = $this->guncellemeService->guncellemeVarMi($force);

        $response->json($sonuc);
    }

    public function sistemTestet(Request $request, Response $response): void
    {
        $this->guard($response);

        $tests = [
            'php_version'        => PHP_VERSION,
            'curl'               => function_exists('curl_init'),
            'zip_archive'        => class_exists('ZipArchive'),
            'shell_exec'         => $this->isFuncEnabled('shell_exec'),
            'exec'               => $this->isFuncEnabled('exec'),
            'proc_open'          => $this->isFuncEnabled('proc_open'),
            'allow_url_fopen'    => (bool) ini_get('allow_url_fopen'),
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'memory_limit'       => ini_get('memory_limit'),
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'writable_base'      => is_writable(BASE_PATH),
            'writable_app'       => is_writable(BASE_PATH . '/app'),
            'writable_storage'   => is_writable(BASE_PATH . '/storage'),
            'tmp_dir'            => sys_get_temp_dir(),
            'tmp_writable'       => is_writable(sys_get_temp_dir()),
            'base_path'          => BASE_PATH,
            'disk_free_mb'       => disk_free_space(BASE_PATH) !== false
                                    ? round(disk_free_space(BASE_PATH) / 1024 / 1024, 2)
                                    : null,
        ];

        $response->json($tests);
    }

    private function isFuncEnabled(string $func): bool
    {
        if (!function_exists($func)) return false;
        $disabled = explode(',', (string) ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        return !in_array($func, $disabled, true);
    }
}

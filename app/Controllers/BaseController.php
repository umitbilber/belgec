<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Core\Request;
use App\Services\AuditLogService;

abstract class BaseController
{
    public function __construct(protected SettingsServiceInterface $settingsService) {}

    protected function guard(Response $response): void
    {
        if (!$this->settingsService->isInstalled()) {
            $response->redirect(url('setup'));
        }

        if (!isset($_SESSION['giris_yapildi']) || $_SESSION['giris_yapildi'] !== true) {
            $response->redirect(url(''));
        }
    }

    protected function guardIzin(Response $response, string $izin): void
    {
        $this->guard($response);

        $rol = $_SESSION['kullanici_rol'] ?? '';
        if ($rol === 'yonetici') return;

        $izinler = $_SESSION['kullanici_izinler'] ?? [];
        if (!in_array($izin, $izinler, true)) {
            $response->abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }
    }

    protected function guardCsrf(Request $request, Response $response): void
    {
        if (!$request->verifyCsrf()) {
            $response->abort(419, 'Geçersiz veya süresi dolmuş istek. Lütfen sayfayı yenileyip tekrar deneyin.');
        }
    }

    protected function aktifKullaniciId(): ?int
    {
        $id = $_SESSION['kullanici_id'] ?? null;
        return $id !== null ? (int) $id : null;
    }

    protected function aktifKullaniciRol(): string
    {
        return (string) ($_SESSION['kullanici_rol'] ?? 'kullanici');
    }

    protected function isYonetici(): bool
    {
        return $this->aktifKullaniciRol() === 'yonetici';
    }
    protected function auditLog(
    string $islem,
    string $modul,
    ?int $kayitId = null,
    ?string $aciklama = null
): void {
    (new AuditLogService())->log($islem, $modul, $kayitId, $aciklama);
}
}
<?php

declare(strict_types=1);

namespace App\Core;

use App\Interfaces\AlisFaturasiRepositoryInterface;
use App\Interfaces\AlisFaturasiServiceInterface;
use App\Interfaces\BaseFaturaRepositoryInterface;
use App\Interfaces\CariAktarimRepositoryInterface;
use App\Interfaces\CariAktarimServiceInterface;
use App\Interfaces\CariHareketRepositoryInterface;
use App\Interfaces\CariHareketServiceInterface;
use App\Interfaces\CariPrintServiceInterface;
use App\Interfaces\CariRepositoryInterface;
use App\Interfaces\CariServiceInterface;
use App\Interfaces\CariYaslandirmaRepositoryInterface;
use App\Interfaces\CariYaslandirmaServiceInterface;
use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\DashboardServiceInterface;
use App\Interfaces\EdmGorulduServiceInterface;
use App\Interfaces\EdmServiceInterface;
use App\Interfaces\MailServiceInterface;
use App\Interfaces\MutabakatRepositoryInterface;
use App\Interfaces\MutabakatServiceInterface;
use App\Interfaces\SatisFaturasiRepositoryInterface;
use App\Interfaces\SatisFaturasiServiceInterface;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\StokRepositoryInterface;
use App\Interfaces\StokServiceInterface;
use App\Interfaces\TeklifPrintServiceInterface;
use App\Interfaces\TeklifRepositoryInterface;
use App\Interfaces\TeklifServiceInterface;
use App\Repositories\AlisFaturasiRepository;
use App\Repositories\CariAktarimRepository;
use App\Repositories\CariHareketRepository;
use App\Repositories\CariRepository;
use App\Repositories\CariYaslandirmaRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\MutabakatRepository;
use App\Repositories\SatisFaturasiRepository;
use App\Repositories\StokRepository;
use App\Repositories\TeklifRepository;
use App\Services\AlisFaturasiService;
use App\Services\CariAktarimService;
use App\Services\CariHareketService;
use App\Services\CariPrintService;
use App\Services\CariService;
use App\Services\CariYaslandirmaService;
use App\Services\DashboardService;
use App\Services\EdmGorulduService;
use App\Services\EdmService;
use App\Services\MailService;
use App\Services\MutabakatService;
use App\Services\SatisFaturasiService;
use App\Services\SettingsService;
use App\Services\StokService;
use App\Services\TeklifPrintService;
use App\Services\TeklifService;
use App\Interfaces\StokHareketRepositoryInterface;
use App\Interfaces\StokHareketServiceInterface;
use App\Repositories\StokHareketRepository;
use App\Services\StokHareketService;
use App\Interfaces\KullaniciRepositoryInterface;
use App\Interfaces\KullaniciServiceInterface;
use App\Repositories\KullaniciRepository;
use App\Services\KullaniciService;
use App\Interfaces\RaporServiceInterface;
use App\Services\RaporService;

class ServiceProvider
{
    public static function register(Container $container): void
    {
        // Repositories
        $container->singleton(CariRepositoryInterface::class, CariRepository::class);
        $container->singleton(AlisFaturasiRepositoryInterface::class, AlisFaturasiRepository::class);
        $container->singleton(SatisFaturasiRepositoryInterface::class, SatisFaturasiRepository::class);
        $container->singleton(StokRepositoryInterface::class, StokRepository::class);
        $container->singleton(TeklifRepositoryInterface::class, TeklifRepository::class);
        $container->singleton(DashboardRepositoryInterface::class, DashboardRepository::class);
        $container->singleton(CariHareketRepositoryInterface::class, CariHareketRepository::class);
        $container->singleton(CariYaslandirmaRepositoryInterface::class, CariYaslandirmaRepository::class);
        $container->singleton(CariAktarimRepositoryInterface::class, CariAktarimRepository::class);
        $container->singleton(MutabakatRepositoryInterface::class, MutabakatRepository::class);
        $container->singleton(StokHareketRepositoryInterface::class, StokHareketRepository::class);
        $container->singleton(KullaniciRepositoryInterface::class, KullaniciRepository::class);

        // Services
        $container->singleton(SettingsServiceInterface::class, SettingsService::class);
        $container->singleton(CariServiceInterface::class, CariService::class);
        $container->singleton(AlisFaturasiServiceInterface::class, AlisFaturasiService::class);
        $container->singleton(SatisFaturasiServiceInterface::class, SatisFaturasiService::class);
        $container->singleton(StokServiceInterface::class, StokService::class);
        $container->singleton(TeklifServiceInterface::class, TeklifService::class);
        $container->singleton(TeklifPrintServiceInterface::class, TeklifPrintService::class);
        $container->singleton(DashboardServiceInterface::class, DashboardService::class);
        $container->singleton(CariHareketServiceInterface::class, CariHareketService::class);
        $container->singleton(CariYaslandirmaServiceInterface::class, CariYaslandirmaService::class);
        $container->singleton(CariAktarimServiceInterface::class, CariAktarimService::class);
        $container->singleton(CariPrintServiceInterface::class, CariPrintService::class);
        $container->singleton(MutabakatServiceInterface::class, MutabakatService::class);
        $container->singleton(MailServiceInterface::class, MailService::class);
        $container->singleton(EdmServiceInterface::class, EdmService::class);
        $container->singleton(EdmGorulduServiceInterface::class, EdmGorulduService::class);
        $container->singleton(StokHareketServiceInterface::class, StokHareketService::class);
        $container->singleton(KullaniciServiceInterface::class, KullaniciService::class);
        $container->singleton(RaporServiceInterface::class, RaporService::class);
    }
}
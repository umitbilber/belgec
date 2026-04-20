<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\DashboardServiceInterface;
use App\Interfaces\DashboardRepositoryInterface;

class DashboardService implements DashboardServiceInterface
{
    private DashboardRepositoryInterface $repository;

    public function __construct(DashboardRepositoryInterface $repository)
{
    $this->repository = $repository;
}

    public function getDailySummary(?string $date = null): array
    {
        $bugun = $date ?: date('Y-m-d');

        return [
            'tarih' => $bugun,
            'gunluk_tahsilat' => $this->repository->getDailyTahsilat($bugun),
            'gunluk_tediye' => $this->repository->getDailyTediye($bugun),
            'gunluk_alis' => $this->repository->getDailyAlis($bugun),
            'gunluk_satis' => $this->repository->getDailySatis($bugun),
        ];
    }

    public function getModuleDefinitions(): array
    {
        return [
            'yonetici' => [
                'baslik' => 'Yönetici Ekranı',
                'aciklama' => 'Günlük finansal özetleri, hızlı geçişleri ve genel panel durumunu tek ekranda görün.',
                'url' => url('dashboard'),
                'buton' => 'Modülü Aç',
                'renk' => '#2c3e50',
            ],
            'kullanicilar' => [
    'baslik'   => 'Kullanıcı Yönetimi',
    'aciklama' => 'Sisteme erişecek kullanıcıları ekleyin, rollerini ve izinlerini düzenleyin.',
    'url'      => url('kullanicilar'),
    'buton'    => 'Modülü Aç',
    'renk'     => '#7c3aed',
],
'audit_log' => [
    'baslik'   => 'Audit Log',
    'aciklama' => 'Sistemde yapılan tüm işlemlerin kaydını görüntüle — kim, ne zaman, ne yaptı.',
    'url'      => url('audit-log'),
    'buton'    => 'Modülü Aç',
    'renk'     => '#475569',
],
'raporlar' => [
    'baslik'   => 'Raporlar',
    'aciklama' => 'Aylık alış/satış özeti, en çok satılan ürünler, cari analizi ve bakiye raporlarını görüntüle.',
    'url'      => url('raporlar'),
    'buton'    => 'Modülü Aç',
    'renk'     => '#0f766e',
],
            'cariler' => [
                'baslik' => 'Cari Yönetimi',
                'aciklama' => 'Cari kartları yönetin, tahsilat ve tediye kayıtları girin, ekstre görüntüleyin.',
                'url' => url('cariler'),
                'buton' => 'Modülü Aç',
                'renk' => '#16a085',
            ],
            'cari_yaslandirma' => [
    'baslik' => 'Cari Yaşlandırma',
    'aciklama' => 'Açık faturaları vade tarihine göre gruplayın, geciken alacak ve borçları hızlıca görün.',
    'url' => url('cari-yaslandirma'),
    'buton' => 'Modülü Aç',
    'renk' => '#d35400',
],
'cari_hareketler' => [
    'baslik' => 'Cari Hareketler',
    'aciklama' => 'Tahsilat, tediye ve bakiye düzeltme kayıtlarını tek ekranda listeleyin ve filtreleyin.',
    'url' => url('cari-hareketler'),
    'buton' => 'Modülü Aç',
    'renk' => '#6c5ce7',
],
            'stoklar' => [
                'baslik' => 'Stok Yönetimi',
                'aciklama' => 'Stok kartlarını yönetin, miktar düzenleyin ve stok hareket geçmişini takip edin.',
                'url' => url('stoklar'),
                'buton' => 'Modülü Aç',
                'renk' => '#2980b9',
            ],
            'stok_hareketleri' => [
    'baslik'   => 'Stok Hareketleri',
    'aciklama' => 'Stok giriş-çıkış hareketlerini tarih aralığı ve stok bazında filtreleyin, kümülatif bakiye takibi yapın.',
    'url'      => url('stok-hareketleri'),
    'buton'    => 'Modülü Aç',
    'renk'     => '#0891b2',
],
            'alis_faturalari' => [
                'baslik' => 'Alış Faturaları',
                'aciklama' => 'Alış faturalarını kaydedin, güncelleyin ve stok ile cari etkilerini otomatik işleyin.',
                'url' => url('alis-faturalari'),
                'buton' => 'Modülü Aç',
                'renk' => '#27ae60',
            ],
            'satis_faturalari' => [
                'baslik' => 'Satış Faturaları',
                'aciklama' => 'Satış faturalarını oluşturun, stok çıkışlarını ve cari bakiye etkilerini yönetin.',
                'url' => url('satis-faturalari'),
                'buton' => 'Modülü Aç',
                'renk' => '#c0392b',
            ],
            'teklifler' => [
                'baslik' => 'Teklifler',
                'aciklama' => 'Müşterileriniz için teklif hazırlayın, kalemleri yönetin ve toplamları hızlıca oluşturun.',
                'url' => url('teklifler'),
                'buton' => 'Modülü Aç',
                'renk' => '#8e44ad',
            ],
			'veri-aktarim' => [
    'baslik' => 'Veri Aktarım',
    'aciklama' => 'Wolvox ve benzeri kaynaklardan cari ekstre aktarımı yapın.',
    'url' => url('veri-aktarim'),
    'buton' => 'Modülü Aç',
    'renk' => '#34495e',
],
'edm_faturalar' => [
    'baslik' => 'EDM Faturalar',
    'aciklama' => 'Gelen ve giden e-faturaları görüntüleyin.',
    'renk' => '#2c7be5',
    'buton' => 'Modülü Aç',
    'url' => url('edm-faturalar'),
],
            'ayarlar' => [
                'baslik' => 'Ayarlar',
                'aciklama' => 'Şirket bilgilerini, SMTP ayarlarını, tema rengini ve favori modülleri yönetin.',
                'url' => url('ayarlar'),
                'buton' => 'Modülü Aç',
                'renk' => '#7f8c8d',
            ],
        ];
    }

    public function getModuleLabels(): array
    {
        $definitions = $this->getModuleDefinitions();
        $labels = [];

        foreach ($definitions as $key => $definition) {
            $labels[$key] = $definition['baslik'];
        }

        return $labels;
    }
}
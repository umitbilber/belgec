<?php
$title = 'Veri Aktarım';
$description = 'Wolvox ve benzeri dış kaynaklardan cari hareket ve fatura geçmişi aktarımı için yönetim ekranı.';
require __DIR__ . '/../partials/page-header.php';
?>

<div class="section-card">
    <div class="section-card-header">
        <div>
            <h3 style="margin-bottom:6px;">Wolvox Müşteri Ekstresi Aktarımı</h3>
            <p style="margin:0; color:#6b7280;">
                Seçilen carinin mevcut finansal geçmişini temizleyip Wolvox ekstresinden satış faturaları ve tahsilatları yeniden oluşturur.
            </p>
        </div>
    </div>

    <div style="margin-top:16px;">
        <div class="bilgi-kutusu" style="margin-bottom:14px;">
            Önce cariyi seçin, sonra Wolvox’tan aldığınız .xlsx veya .pdf ekstre dosyasını yükleyin. Sistem önce önizleme çıkarır, onaydan sonra gerçek aktarımı yapar.
        </div>

        <div class="app-form-stack">
            <label>Aktarım Yapılacak Cari</label>
<div class="cari-autocomplete-wrap">
    <input
        type="text"
        id="veriAktarimCariInput"
        placeholder="Cari adı yazarak arayın"
        autocomplete="off"
    >
    <input type="hidden" id="veriAktarimCariId" value="">
    <div id="veriAktarimCariResults" class="cari-autocomplete-results"></div>
</div>

            <div class="app-modal-footer" style="justify-content:flex-start; margin-top:14px;">
                <button type="button" class="btn btn-ana" onclick="veriAktarimWolvoxAc()">
                    Wolvox Ekstresi Yükle
                </button>
            </div>
        </div>
    </div>
</div>

<div class="section-card" style="margin-top:18px;">
    <div class="section-card-header">
        <div>
            <h3 style="margin-bottom:6px;">Aktarım Kuyruğu</h3>
            <p style="margin:0; color:#6b7280;">
                Önizlemesi oluşturulan kayıtlar burada birikir. İstersen tek tek kaldırabilir ya da tümünü toplu aktarabilirsin.
            </p>
        </div>
    </div>

    <?php $queue = $wolvox_queue ?? []; ?>

    <?php if (empty($queue)): ?>
        <div class="bilgi-kutusu" style="margin-top:16px;">
            Henüz kuyrukta bekleyen aktarım kaydı yok.
        </div>
    <?php else: ?>
        <div class="table-wrap" style="margin-top:16px;">
            <table class="cari-table">
                <thead>
                    <tr>
                        <th>Cari ID</th>
                        <th>Dosya</th>
                        <th>Satış</th>
                        <th>Tahsilat</th>
                        <th>Bakiye</th>
                        <th style="width:220px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($queue as $item): ?>
                        <tr>
                            <td><?= (int) ($item['cari_id'] ?? 0) ?></td>
                            <td><?= e((string) ($item['dosya_adi'] ?? '-')) ?></td>
                            <td><?= (int) (($item['ozet']['satis_sayisi'] ?? 0)) ?></td>
                            <td><?= (int) (($item['ozet']['tahsilat_sayisi'] ?? 0)) ?></td>
                            <td><?= e(number_format((float) (($item['ozet']['beklenen_bakiye'] ?? 0)), 2, ',', '.')) ?> TL</td>
                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <a
                                        class="btn btn-gri"
                                        href="<?= e(url('cariler/wolvox/kuyruk-sil?id=' . urlencode((string) ($item['queue_id'] ?? '')))) ?>"
                                        onclick="return confirm('Bu kuyruk kaydı kaldırılsın mı?');"
                                    >
                                        Kuyruktan Sil
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="app-modal-footer" style="justify-content:flex-start; margin-top:16px;">
            <form
                method="POST"
                action="<?= e(url('cariler/wolvox/toplu-aktar')) ?>"
                onsubmit="return confirm('Kuyruktaki tüm cari kayıtları için aktarım başlatılacak. Devam edilsin mi?');"
            >
                <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
                <button type="submit" class="btn btn-ana">Tümünü İçeri Aktar</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/import-modal.php'; ?>

<script>
    window.veriAktarimCariler = <?= json_encode(array_values(array_map(static function ($cari) {
        return [
            'id' => (int) ($cari['id'] ?? 0),
            'ad_soyad' => (string) ($cari['ad_soyad'] ?? ''),
            'telefon' => (string) ($cari['telefon'] ?? ''),
            'eposta' => (string) ($cari['eposta'] ?? ''),
        ];
    }, $cariler ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script>
    window.wolvoxPreviewData = <?= json_encode($wolvox_preview ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php require __DIR__ . '/partials/scripts/veri-aktarim-page.php'; ?>
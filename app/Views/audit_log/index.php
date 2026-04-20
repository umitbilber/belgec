<?php
$title = 'Audit Log';
$description = 'Sistemde yapılan tüm işlemlerin kaydı.';
$badgeText = 'Kayıt: ' . count($kayitlar);
$headerClass = 'audit-page-header';
$headerLeftClass = 'audit-page-header-left';
$headerRightClass = 'audit-page-header-right';
$badgeClass = 'istatistik-rozet';
$actionHtml = '';
require BASE_PATH . '/app/Views/partials/page-header.php';

function auditIslemRenk(string $islem): string {
    return match($islem) {
        'ekle'    => '#16a34a',
        'duzenle' => '#d97706',
        'sil'     => '#dc2626',
        default   => '#64748b',
    };
}

function auditModulEtiket(string $modul): string {
    return match($modul) {
        'alis_fatura'     => 'Alış Faturası',
        'satis_fatura'    => 'Satış Faturası',
        'cariler'         => 'Cariler',
        'stoklar'         => 'Stoklar',
        'teklifler'       => 'Teklifler',
        'cari_hareketler' => 'Cari Hareketler',
        'ayarlar'         => 'Ayarlar',
        'kullanicilar'    => 'Kullanıcılar',
        default           => $modul,
    };
}
?>
<style>
.audit-filtre {
    background:#fff;border:1px solid #e6edf5;border-radius:16px;
    padding:18px 20px;margin-bottom:16px;
}
.audit-filtre-grid {
    display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:12px;
}
.audit-filtre-grid label { display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;margin-top:0; }
.audit-tablo-wrap { background:#fff;border:1px solid #e6edf5;border-radius:16px;overflow:hidden; }
.audit-tablo { width:100%;border-collapse:collapse;font-size:13px; }
.audit-tablo thead th { background:#f8fafc;color:#475569;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;padding:11px 14px;text-align:left;border-bottom:1px solid #e6edf5;white-space:nowrap; }
.audit-tablo tbody tr { border-bottom:1px solid #f1f5f9; }
.audit-tablo tbody tr:last-child { border-bottom:0; }
.audit-tablo tbody tr:hover { background:#f8fbff; }
.audit-tablo td { padding:10px 14px;vertical-align:middle;color:#1e293b; }
.audit-bos { text-align:center;padding:40px;color:#94a3b8;font-size:13px; }
@media(max-width:640px) { .audit-tablo-wrap { overflow-x:auto; } .audit-tablo { min-width:600px; } }
</style>

<!-- FİLTRE -->
<div class="audit-filtre">
    <form method="GET" action="<?= e(url('audit-log')) ?>">
        <div class="audit-filtre-grid">
            <div>
                <label>Kullanıcı</label>
                <select name="kullanici_adi">
                    <option value="">Tümü</option>
                    <?php foreach ($kullanicilar as $k): ?>
                        <option value="<?= e($k) ?>" <?= ($filtreler['kullanici_adi'] === $k) ? 'selected' : '' ?>><?= e($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Modül</label>
                <select name="modul">
                    <option value="">Tümü</option>
                    <?php foreach ($moduller as $m): ?>
                        <option value="<?= e($m) ?>" <?= ($filtreler['modul'] === $m) ? 'selected' : '' ?>><?= e(auditModulEtiket($m)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>İşlem</label>
                <select name="islem">
                    <option value="">Tümü</option>
                    <option value="ekle"    <?= $filtreler['islem'] === 'ekle'    ? 'selected' : '' ?>>Ekle</option>
                    <option value="duzenle" <?= $filtreler['islem'] === 'duzenle' ? 'selected' : '' ?>>Düzenle</option>
                    <option value="sil"     <?= $filtreler['islem'] === 'sil'     ? 'selected' : '' ?>>Sil</option>
                </select>
            </div>
            <div>
                <label>Başlangıç</label>
                <input type="date" name="tarih_baslangic" value="<?= e($filtreler['tarih_baslangic']) ?>">
            </div>
            <div>
                <label>Bitiş</label>
                <input type="date" name="tarih_bitis" value="<?= e($filtreler['tarih_bitis']) ?>">
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-ana">Filtrele</button>
            <a href="<?= e(url('audit-log')) ?>" class="btn btn-gri">Sıfırla</a>
        </div>
    </form>
</div>

<!-- TABLO -->
<div class="audit-tablo-wrap">
    <table class="audit-tablo">
        <thead>
            <tr>
                <th>Tarih / Saat</th>
                <th>Kullanıcı</th>
                <th>Modül</th>
                <th>İşlem</th>
                <th>Kayıt ID</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kayitlar)): ?>
                <tr><td colspan="6" class="audit-bos">Kayıt bulunamadı.</td></tr>
            <?php else: ?>
                <?php foreach ($kayitlar as $row):
                    $renk = auditIslemRenk($row['islem']);
                    $tarih = !empty($row['tarih'])
                        ? date('d.m.Y H:i:s', strtotime($row['tarih']))
                        : '-';
                ?>
                <tr>
                    <td style="white-space:nowrap;color:#64748b;font-size:12px;"><?= e($tarih) ?></td>
                    <td style="font-weight:600;"><?= e($row['kullanici_adi']) ?></td>
                    <td><?= e(auditModulEtiket($row['modul'])) ?></td>
                    <td>
                        <span style="background:<?= $renk ?>18;color:<?= $renk ?>;font-weight:700;font-size:11px;padding:3px 10px;border-radius:20px;">
                            <?= e(ucfirst($row['islem'])) ?>
                        </span>
                    </td>
                    <td style="color:#94a3b8;font-size:12px;"><?= $row['kayit_id'] ? '#' . (int)$row['kayit_id'] : '-' ?></td>
                    <td style="font-family:monospace;font-size:12px;color:#94a3b8;"><?= e($row['ip'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
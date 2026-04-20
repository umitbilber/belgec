<?php
$title = 'Kullanıcı Yönetimi';
$description = 'Sisteme erişecek kullanıcıları ve izinlerini buradan yönetebilirsin.';
$badgeText = 'Toplam: ' . count($kullanicilar);
$headerClass = 'kullanici-page-header';
$headerLeftClass = 'kullanici-page-header-left';
$headerRightClass = 'kullanici-page-header-right';
$badgeClass = 'istatistik-rozet';
$actionHtml = '<button type="button" class="btn btn-ana" data-modal-open="createKullaniciModal">+ Yeni Kullanıcı</button>';
require BASE_PATH . '/app/Views/partials/page-header.php';

function izinEtiket(string $izin): string {
    $parcalar = explode('.', $izin);
    return match($parcalar[1] ?? '') {
        'goruntule' => 'Görüntüle',
        'ekle'      => 'Ekle',
        'duzenle'   => 'Düzenle',
        'sil'       => 'Sil',
        default     => $parcalar[1] ?? $izin,
    };
}
?>

<?php if (!empty($bilgi_mesaji)): ?>
    <div class="bilgi-kutusu"><?= e($bilgi_mesaji) ?></div>
<?php endif; ?>
<?php if (!empty($hata_mesaji)): ?>
    <div class="hata-kutusu"><?= e($hata_mesaji) ?></div>
<?php endif; ?>

<!-- KULLANICI LİSTESİ -->
<div class="kutu" style="padding:26px;">
    <?php if (empty($kullanicilar)): ?>
        <div class="bilgi-kutusu">Henüz kullanıcı eklenmemiş.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Kullanıcı Adı</th>
                        <th>Rol</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kullanicilar as $k): ?>
                    <tr id="kullanici-<?= (int)$k['id'] ?>">
                        <td><strong><?= e($k['ad']) ?></strong></td>
                        <td style="font-family:monospace;"><?= e($k['kullanici_adi']) ?></td>
                        <td>
                            <?php if ($k['rol'] === 'yonetici'): ?>
                                <span class="badge badge-mavi">Yönetici</span>
                            <?php else: ?>
                                <span class="badge badge-gri">Kullanıcı</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($k['aktif']): ?>
                                <span class="badge badge-yesil">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-kirmizi">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="aksiyonlar">
                                <button type="button" class="btn btn-sari"
                                    onclick="kullaniciDuzenleAc(this)"
                                    data-kullanici='<?= e(json_encode([
                                        'id'            => (int)$k['id'],
                                        'ad'            => $k['ad'],
                                        'kullanici_adi' => $k['kullanici_adi'],
                                        'rol'           => $k['rol'],
                                        'aktif'         => (int)$k['aktif'],
                                        'izinler'       => $tum_izinler ? array_merge(...array_values($tum_izinler)) : [],
                                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                    Düzenle
                                </button>
                                <form method="POST" action="<?= e(url('kullanicilar/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
                                    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
                                    <input type="hidden" name="kullanici_id" value="<?= (int)$k['id'] ?>">
                                    <button type="submit" class="btn btn-kirmizi">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- YENİ KULLANICI MODAL -->
<?php
ob_start();
?>
<form method="POST" action="<?= e(url('kullanicilar/ekle')) ?>" class="app-form-stack">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <div class="form-grid-3">
        <div>
            <label>Ad Soyad</label>
            <input type="text" name="ad" required>
        </div>
        <div>
            <label>Kullanıcı Adı</label>
            <input type="text" name="kullanici_adi" required autocomplete="off">
        </div>
        <div>
            <label>Şifre</label>
            <input type="password" name="sifre" required autocomplete="new-password">
        </div>
    </div>
    <div>
        <label>Rol</label>
        <select name="rol">
            <option value="kullanici">Kullanıcı</option>
            <option value="yonetici">Yönetici</option>
        </select>
    </div>

    <div class="panel-divider"></div>
    <p style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 12px;">İzinler <span style="font-weight:400;color:#64748b;">(yönetici rolü tüm izinlere sahiptir)</span></p>

    <?php foreach ($tum_izinler as $grup => $izinListesi): ?>
    <div style="margin-bottom:10px;">
        <div style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;"><?= e($grup) ?></div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <?php foreach ($izinListesi as $izin): ?>
            <label style="display:flex;align-items:center;gap:5px;font-size:13px;font-weight:500;cursor:pointer;">
                <input type="checkbox" name="<?= e($izin) ?>" value="1" style="width:auto;">
                <?= e(izinEtiket($izin)) ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="panel-divider"></div>
    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Kullanıcı Ekle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();
$modalId = 'createKullaniciModal';
$modalTitle = 'Yeni Kullanıcı';
$modalDescription = 'Yeni kullanıcı ekle ve izinlerini belirle.';
$modalSize = 'lg';
require BASE_PATH . '/app/Views/partials/modal.php';
?>

<!-- DÜZENLE MODAL -->
<?php
ob_start();
?>
<form method="POST" action="<?= e(url('kullanicilar/guncelle')) ?>" class="app-form-stack" id="editKullaniciForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="kullanici_id" value="">
    <div class="form-grid-3">
        <div>
            <label>Ad Soyad</label>
            <input type="text" name="ad" required>
        </div>
        <div>
            <label>Kullanıcı Adı</label>
            <input type="text" name="kullanici_adi" required autocomplete="off">
        </div>
        <div>
            <label>Yeni Şifre <span style="font-weight:400;color:#94a3b8;">(boş bırakırsan değişmez)</span></label>
            <input type="password" name="sifre" autocomplete="new-password">
        </div>
    </div>
    <div class="form-grid-3">
        <div>
            <label>Rol</label>
            <select name="rol">
                <option value="kullanici">Kullanıcı</option>
                <option value="yonetici">Yönetici</option>
            </select>
        </div>
        <div>
            <label>Durum</label>
            <select name="aktif">
                <option value="1">Aktif</option>
                <option value="0">Pasif</option>
            </select>
        </div>
    </div>

    <div class="panel-divider"></div>
    <p style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 12px;">İzinler</p>
    <div id="editIzinlerWrap">
        <?php foreach ($tum_izinler as $grup => $izinListesi): ?>
        <div style="margin-bottom:10px;">
            <div style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;"><?= e($grup) ?></div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($izinListesi as $izin): ?>
                <label style="display:flex;align-items:center;gap:5px;font-size:13px;font-weight:500;cursor:pointer;">
                    <input type="checkbox" name="<?= e($izin) ?>" value="1" style="width:auto;" class="edit-izin-cb" data-izin="<?= e($izin) ?>">
                    <?= e(izinEtiket($izin)) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="panel-divider"></div>
    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-turuncu">Güncelle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();
$modalId = 'editKullaniciModal';
$modalTitle = 'Kullanıcı Düzenle';
$modalDescription = 'Kullanıcı bilgilerini ve izinlerini güncelle.';
$modalSize = 'lg';
require BASE_PATH . '/app/Views/partials/modal.php';
?>

<script>
function kullaniciDuzenleAc(btn) {
    var raw = btn.getAttribute('data-kullanici');
    if (!raw) return;
    var k = JSON.parse(raw);

    var form = document.getElementById('editKullaniciForm');
    if (!form) return;

    form.querySelector('[name="kullanici_id"]').value  = k.id;
    form.querySelector('[name="ad"]').value            = k.ad;
    form.querySelector('[name="kullanici_adi"]').value = k.kullanici_adi;
    form.querySelector('[name="rol"]').value           = k.rol;
    form.querySelector('[name="aktif"]').value         = k.aktif;
    form.querySelector('[name="sifre"]').value         = '';

    // İzin checkbox'larını doldur - mevcut izinleri fetch et
    fetch('<?= e(url('kullanicilar/izinler')) ?>?id=' + k.id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            form.querySelectorAll('.edit-izin-cb').forEach(function(cb) {
                cb.checked = data.izinler && data.izinler.includes(cb.dataset.izin);
            });
        });

    if (window.appModal) window.appModal.open('editKullaniciModal');
}
</script>
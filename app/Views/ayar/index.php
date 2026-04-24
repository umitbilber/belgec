<?php require BASE_PATH . '/app/Views/partials/styles/ayar-page.php'; ?>
<div class="sayfa-ust ayar-page-header">
    <div class="sayfa-ust-sol ayar-page-header-left">
        <h2>Ayarlar</h2>
        <p class="sayfa-aciklama">
            Şirket bilgilerini, SMTP yapılandırmasını, teklif şartlarını ve sık kullanılan modülleri merkezi olarak yönetin.
        </p>
    </div>

    <div class="istatistik-rozet ayar-stat-badge">
        Favori Modül: <?= count($favoriler) ?>
    </div>
</div>

<div class="kutu form-panel ayar-form-panel">
    <div class="kutu-baslik">
        <div>
            <h3>Genel Ayarlar</h3>
            <p class="kutu-aciklama">Şirket görünümünü ve iletişim bilgilerini güncel tut.</p>
        </div>
    </div>

    <form method="POST" action="<?= e(url('ayarlar/guncelle')) ?>">
        <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
        <div class="form-grid-3">
            <div>
                <label>Şirket Adı</label>
                <input type="text" name="sirket_adi" value="<?= e($ayarlar['sirket_adi'] ?? '') ?>" required>
            </div>

            <div>
                <label>Logo URL</label>
                <input type="text" name="logo_url" value="<?= e($ayarlar['logo_url'] ?? '') ?>" required>
            </div>

            <div>
                <label>Tema Rengi</label>
                <input type="color" name="tema_rengi" value="<?= e($ayarlar['tema_rengi'] ?? '#3498db') ?>">
            </div>
        </div>

        <div class="form-grid-3">
            <div>
                <label>E-Posta</label>
                <input type="email" name="eposta" value="<?= e($ayarlar['eposta'] ?? '') ?>" required>
            </div>

            <div>
                <label>Telefon</label>
                <input type="text" name="telefon" value="<?= e($ayarlar['telefon'] ?? '') ?>" required>
            </div>

            <div>
                <label>İnternet Sitesi</label>
                <input type="text" name="web_sitesi" value="<?= e($ayarlar['web_sitesi'] ?? '') ?>" required>
            </div>
        </div>

        <label>Adres</label>
        <textarea name="adres" required><?= e($ayarlar['adres'] ?? '') ?></textarea>
        
        <div class="form-grid-3">
            <div>
                <label>Vergi Kimlik No</label>
                <input type="text" name="vergi_no" value="<?= e($ayarlar['vergi_no'] ?? '') ?>">
            </div>

            <div>
                <label>Vergi Dairesi</label>
                <input type="text" name="vergi_dairesi" value="<?= e($ayarlar['vergi_dairesi'] ?? '') ?>">
            </div>
        </div>

        <div class="panel-divider"></div>

        <div class="kutu-baslik">
            <div>
                <h4>SMTP Ayarları</h4>
                <p class="kutu-aciklama">Mutabakat ve bildirim e-postalarının gönderimi için kullanılır.</p>
            </div>
        </div>

        <div class="form-grid-3">
            <div>
                <label>SMTP Sunucusu (Host)</label>
                <input type="text" name="smtp_host" value="<?= e($ayarlar['smtp_host'] ?? '') ?>">
            </div>

            <div>
                <label>SMTP E-Posta Adresi</label>
                <input type="text" name="smtp_mail" value="<?= e($ayarlar['smtp_mail'] ?? '') ?>">
            </div>

            <div>
                <label>SMTP Şifresi</label>
                <input type="password" name="smtp_sifre" value="">
            </div>
        </div>

                <p class="alt-metin ayar-smtp-note">
            SMTP şifresi alanını boş bırakırsan mevcut şifre korunur.
        </p>

        <div class="panel-divider"></div>

        <div class="kutu-baslik">
            <div>
                <h4>Varsayılan Teklif Şartları</h4>
                <p class="kutu-aciklama">Yeni teklifler oluşturulurken otomatik doldurulacak metin.</p>
            </div>
        </div>

        <label>Teklif Şartları</label>
                <textarea name="varsayilan_teklif_sartlari" class="ayar-teklif-sartlari"><?= e($ayarlar['varsayilan_teklif_sartlari'] ?? '') ?></textarea>

        <div class="panel-divider"></div>
        
        <div class="kutu-baslik">
    <div>
        <h4>Varsayılan Mutabakat Maili</h4>
        <p class="kutu-aciklama">Mutabakat gönderilirken kullanılacak varsayılan metin. Metne <code>{cari_adi}</code> yazarsan carinin adı, <code>{bakiye}</code> yazarsan güncel bakiyesi otomatik yerleştirilir. Örnek: "Sayın {cari_adi}, bakiyeniz {bakiye} olarak görünmektedir."</p>
    </div>
</div>
<label>Mail Metni</label>
<textarea name="varsayilan_mutabakat_metni" rows="6" style="width:100%;font-family:inherit;font-size:13px;padding:10px;border:1px solid #e2e8f0;border-radius:8px;resize:vertical;"><?= e($ayarlar['varsayilan_mutabakat_metni'] ?? '') ?></textarea>

<div class="panel-divider"></div>
        
        <div class="panel-divider"></div>

<div class="kutu-baslik">
    <div>
        <h4>EDM Entegrasyonu</h4>
        <p class="kutu-aciklama">Gelen ve giden e-faturaları EDM üzerinden Belgeç'e çekmek için temel bağlantı ayarları.</p>
    </div>
</div>
<div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
    <a class="btn btn-gri" href="<?= e(url('ayarlar/edm-test')) ?>">
        EDM Bağlantısını Test Et
    </a>

    <a class="btn btn-ana" href="<?= e(url('ayarlar/edm-onizleme')) ?>" target="_blank" rel="noopener">
        Son 7 Gün Önizleme
    </a>
</div>

<div class="form-grid-3">
    <div>
        <label>EDM Aktif</label>
        <select name="edm_aktif">
            <option value="0" <?= !empty($ayarlar['edm_aktif']) ? '' : 'selected' ?>>Kapalı</option>
            <option value="1" <?= !empty($ayarlar['edm_aktif']) ? 'selected' : '' ?>>Aktif</option>
        </select>
    </div>

    <div>
        <label>EDM Ortamı</label>
        <select name="edm_ortam">
            <option value="test" <?= ($ayarlar['edm_ortam'] ?? 'test') === 'test' ? 'selected' : '' ?>>Test</option>
            <option value="canli" <?= ($ayarlar['edm_ortam'] ?? '') === 'canli' ? 'selected' : '' ?>>Canlı</option>
        </select>
    </div>

    <div>
        <label>Firma VKN</label>
        <input type="text" name="edm_firma_vkn" value="<?= e($ayarlar['edm_firma_vkn'] ?? '') ?>">
    </div>
</div>

<div class="form-grid-3">
    <div>
        <label>EDM Kullanıcı Adı</label>
        <input type="text" name="edm_kullanici" value="<?= e($ayarlar['edm_kullanici'] ?? '') ?>">
    </div>

    <div>
        <label>EDM Şifresi</label>
        <input type="password" name="edm_sifre" value="">
    </div>

    <div>
        <label>Bağlantı Durumu</label>
        <input
            type="text"
            value="<?= !empty($ayarlar['edm_aktif']) ? 'Hazır' : 'Kapalı' ?>"
            readonly
        >
    </div>
</div>

<div class="form-grid-2">
    <div>
        <label>Son Gelen Sync</label>
        <input type="text" value="<?= e($ayarlar['edm_son_gelen_sync'] ?? '') ?>" readonly>
    </div>

    <div>
        <label>Son Giden Sync</label>
        <input type="text" value="<?= e($ayarlar['edm_son_giden_sync'] ?? '') ?>" readonly>
    </div>
</div>

<p class="alt-metin ayar-smtp-note">
    EDM şifresi alanını boş bırakırsan mevcut şifre korunur. İlk aşamada sadece gelen ve giden faturaları okumaya odaklanacağız.
</p>

        <button type="submit" class="btn btn-ana ayar-save-btn">Ayarları Kaydet</button>
    </form>
</div>

<div class="kutu liste-panel ayar-list-panel">
    <div class="kutu-baslik">
        <div>
            <h3>Sık Kullanılan Modüller</h3>
            <p class="kutu-aciklama">Dashboard üzerinde hızlı erişim için öne çıkarılan modüller.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="ayar-table">
            <thead>
                <tr>
                    <th>Modül Kodu</th>
                    <th>Modül Adı</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tum_moduller as $kod => $ad): ?>
                    <?php $favorideMi = in_array($kod, $favoriler, true); ?>
                    <tr>
                        <td><span class="badge badge-gri"><?= e($kod) ?></span></td>
                        <td><span class="veri-vurgu"><?= e($ad) ?></span></td>
                        <td>
                            <?php if ($favorideMi): ?>
                                <span class="badge badge-yesil">Favorilerde</span>
                            <?php else: ?>
                                <span class="badge badge-gri">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td class="ayar-table-action">
                            <?php if ($favorideMi): ?>
                                <form method="POST" action="<?= e(url('ayarlar/favori-kaldir')) ?>" style="display:inline;">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="modul" value="<?= e($kod) ?>">
    <button type="submit" class="btn btn-kirmizi">Favoriden Kaldır</button>
</form>
                            <?php else: ?>
                                <form method="POST" action="<?= e(url('ayarlar/favori-ekle')) ?>" style="display:inline;">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="modul" value="<?= e($kod) ?>">
    <button type="submit" class="btn btn-yesil">Favoriye Ekle</button>
</form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($tum_moduller)): ?>
                    <tr>
                        <td colspan="4">Gösterilecek modül bulunmuyor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="kutu" style="padding:26px;margin-top:18px;">
    <h3 style="margin-bottom:6px;">Yedekleme</h3>
    <p style="color:#64748b;font-size:13px;margin-bottom:18px;">Veritabanını manuel olarak yedekle veya otomatik yedekleme ayarlarını düzenle.</p>

    <?php
    $yedekSikligi  = $ayarlar['yedek_sikligi']  ?? 'haftalik';
    $yedekMaxAdet  = $ayarlar['yedek_max_adet'] ?? 5;
    $sonOtoYedek   = $ayarlar['son_otomatik_yedek'] ?? '';
    ?>

    <!-- Manuel yedek -->
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
        <form method="POST" action="<?= e(url('ayarlar/yedek-al')) ?>" target="yedekFrame" onsubmit="yedekBaslat(this)">
            <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
            <button type="submit" class="btn btn-ana" id="yedekAlBtn">Şimdi Yedek Al ve İndir</button>
        </form>
        <iframe name="yedekFrame" style="display:none;"></iframe>
        <button type="button" class="btn btn-gri" onclick="yedekListesiYukle()">Mevcut Yedekleri Göster</button>

        <form method="POST" action="<?= e(url('ayarlar/yedek-yukle')) ?>" enctype="multipart/form-data" onsubmit="return yedekYuklemeOnay(this);" style="display:inline-flex;gap:8px;align-items:center;">
            <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
            <input type="file" name="yedek_dosya" accept=".sqlite,.sql" required style="font-size:13px;">
            <button type="submit" class="btn btn-kirmizi" style="white-space:nowrap;">Yedekten Geri Yükle</button>
        </form>
    </div>

    <p style="color:#94a3b8;font-size:12px;margin-top:-12px;margin-bottom:16px;">
        ⚠️ "Geri Yükle" işlemi mevcut veritabanının üzerine yazar. İşlem öncesi otomatik failsafe yedeği alınır.
    </p>

    <!-- Yedek listesi -->
    <div id="yedekListesiWrap" style="display:none;margin-bottom:20px;">
        <div id="yedekListesiIcerik" style="font-size:13px;color:#64748b;">Yükleniyor…</div>
    </div>

    <!-- Otomatik yedek ayarları -->
    <form method="POST" action="<?= e(url('ayarlar/guncelle')) ?>">
        <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:14px;">
            <div>
                <label>Otomatik Yedekleme Sıklığı</label>
                <select name="yedek_sikligi">
                    <option value="kapali"   <?= $yedekSikligi === 'kapali'   ? 'selected' : '' ?>>Kapalı</option>
                    <option value="gunluk"   <?= $yedekSikligi === 'gunluk'   ? 'selected' : '' ?>>Günlük</option>
                    <option value="haftalik" <?= $yedekSikligi === 'haftalik' ? 'selected' : '' ?>>Haftalık</option>
                    <option value="aylik"    <?= $yedekSikligi === 'aylik'    ? 'selected' : '' ?>>Aylık</option>
                </select>
            </div>
            <div>
                <label>Tutulacak Maksimum Yedek Sayısı</label>
                <select name="yedek_max_adet">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>" <?= (int)$yedekMaxAdet === $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <?php if ($sonOtoYedek): ?>
            <p style="font-size:12px;color:#94a3b8;margin-bottom:12px;">
                Son otomatik yedek: <?= e(date('d.m.Y H:i', strtotime($sonOtoYedek))) ?>
            </p>
        <?php endif; ?>
        <button type="submit" class="btn btn-gri">Yedekleme Ayarlarını Kaydet</button>
    </form>
</div>

<script>
function yedekBaslat(form) {
    var btn = document.getElementById('yedekAlBtn');
    btn.disabled = true;
    btn.textContent = 'Hazırlanıyor…';
    setTimeout(function () {
        btn.disabled = false;
        btn.textContent = 'Şimdi Yedek Al ve İndir';
    }, 4000);
}
function yedekListesiYukle() {
    var wrap    = document.getElementById('yedekListesiWrap');
    var icerik  = document.getElementById('yedekListesiIcerik');
    var gorunen = wrap.style.display !== 'none';

    if (gorunen) { wrap.style.display = 'none'; return; }

    wrap.style.display = 'block';
    icerik.innerHTML   = 'Yükleniyor…';

    fetch('<?= e(url('ayarlar/yedek-listesi')) ?>')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok || !data.yedekler.length) {
                icerik.innerHTML = '<span style="color:#94a3b8;">Henüz yedek dosyası yok.</span>';
                return;
            }

            var html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            html += '<thead><tr style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;">';
            html += '<th style="padding:7px 10px;text-align:left;border-bottom:1px solid #f1f5f9;">Dosya Adı</th>';
            html += '<th style="padding:7px 10px;text-align:left;border-bottom:1px solid #f1f5f9;">Tarih</th>';
            html += '<th style="padding:7px 10px;text-align:right;border-bottom:1px solid #f1f5f9;">Boyut</th>';
            html += '<th style="padding:7px 10px;border-bottom:1px solid #f1f5f9;"></th>';
            html += '</tr></thead><tbody>';

            data.yedekler.forEach(function(y) {
                var tipRozet = y.tip === 'sqlite'
                    ? '<span style="font-size:10px;font-weight:700;background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;">SQLite</span>'
                    : '<span style="font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;">MySQL</span>';

                html += '<tr style="border-bottom:1px solid #f8fafc;">';
                html += '<td style="padding:7px 10px;font-family:monospace;font-size:12px;color:#64748b;">' + y.dosya_adi + ' ' + tipRozet + '</td>';
                html += '<td style="padding:7px 10px;">' + y.tarih + '</td>';
                html += '<td style="padding:7px 10px;text-align:right;">' + y.boyut + '</td>';
                html += '<td style="padding:7px 10px;text-align:right;white-space:nowrap;">';
                html += '<a href="<?= e(url('ayarlar/yedek-indir')) ?>?dosya=' + encodeURIComponent(y.dosya_adi) + '" class="btn btn-gri" style="font-size:11px;padding:4px 10px;margin-right:4px;">İndir</a>';
                html += '<button type="button" class="btn btn-ana" style="font-size:11px;padding:4px 10px;margin-right:4px;" onclick="yedekRestoreEt(\'' + y.dosya_adi + '\')">Geri Yükle</button>';
                html += '<button type="button" class="btn btn-kirmizi" style="font-size:11px;padding:4px 10px;" onclick="yedekSilEt(\'' + y.dosya_adi + '\')">Sil</button>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            icerik.innerHTML = html;
        })
        .catch(function() {
            icerik.innerHTML = '<span style="color:#b91c1c;">Liste alınamadı.</span>';
        });
}
</script>
    <div class="kutu" style="padding:26px;margin-top:18px;">
    <h3 style="margin-bottom:6px;">Güncelleme</h3>
    <p style="color:#64748b;font-size:13px;margin-bottom:18px;">
        Belgeç'in yeni bir sürümünün yayınlanıp yayınlanmadığını kontrol et.
    </p>

    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <button type="button" class="btn btn-ana" id="guncellemeKontrolBtn" onclick="guncellemeKontrolTetikle()">
            Güncelleme Kontrol Et
        </button>

        <div id="guncellemeKontrolSonuc" style="font-size:13px;color:#64748b;"></div>
    </div>

    <p style="color:#94a3b8;font-size:12px;margin-top:14px;">
        Mevcut sürüm: <strong><?= e(defined('APP_VERSION') ? APP_VERSION : (require BASE_PATH . '/config/app.php')['version'] ?? '-') ?></strong>
    </p>
</div>

<script>
function guncellemeKontrolTetikle() {
    var btn    = document.getElementById('guncellemeKontrolBtn');
    var sonuc  = document.getElementById('guncellemeKontrolSonuc');
    var eskiYazi = btn.textContent;

    btn.disabled = true;
    btn.textContent = 'Kontrol ediliyor…';
    sonuc.textContent = '';
    sonuc.style.color = '#64748b';

    fetch('<?= e(url('guncelleme/kontrol')) ?>?force=1')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = eskiYazi;

            if (data && data.guncelleme_var) {
                sonuc.innerHTML = '✓ Yeni sürüm mevcut: <strong>v' + data.son_surum + '</strong>';
                sonuc.style.color = '#16a34a';

                // Topbar'daki modal sistemini tetikle
                if (typeof guncellemeSonBilgi !== 'undefined') {
                    guncellemeSonBilgi = data;
                    var wrap = document.getElementById('guncellemeBildirimWrap');
                    var metin = document.getElementById('guncellemeBildirimMetin');
                    if (wrap && metin) {
                        metin.textContent = 'v' + data.son_surum + ' Mevcut';
                        wrap.style.display = '';
                    }
                    if (typeof guncellemeBildirimAc === 'function') {
                        guncellemeBildirimAc({ preventDefault: function() {} });
                    }
                }
            } else if (data && data.hata) {
                sonuc.textContent = '✗ ' + data.hata;
                sonuc.style.color = '#b91c1c';
            } else {
                sonuc.innerHTML = '✓ Sisteminiz güncel. Mevcut: <strong>v' + (data.mevcut_surum || '-') + '</strong>';
                sonuc.style.color = '#16a34a';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = eskiYazi;
            sonuc.textContent = '✗ Kontrol başarısız oldu';
            sonuc.style.color = '#b91c1c';
        });
}
    function yedekYuklemeOnay(form) {
    var dosya = form.querySelector('[name="yedek_dosya"]').files[0];
    if (!dosya) return false;

    return confirm(
        '⚠️ DİKKAT!\n\n' +
        'Yüklediğiniz "' + dosya.name + '" dosyası mevcut veritabanının ÜZERİNE yazılacak.\n\n' +
        'Bu işlem:\n' +
        '• Mevcut tüm verilerinizi silecek\n' +
        '• Yüklediğiniz yedekteki verileri geri yükleyecek\n' +
        '• İşlem öncesi otomatik failsafe yedeği alınacak\n\n' +
        'Devam etmek istiyor musunuz?'
    );
}

function yedekRestoreEt(dosyaAdi) {
    if (!confirm(
        '⚠️ DİKKAT!\n\n' +
        '"' + dosyaAdi + '" yedeğini geri yüklemek istediğinize emin misiniz?\n\n' +
        'Bu işlem:\n' +
        '• Mevcut tüm verilerinizi silecek\n' +
        '• Seçili yedekteki verileri geri yükleyecek\n' +
        '• İşlem öncesi otomatik failsafe yedeği alınacak\n\n' +
        'Devam etmek istiyor musunuz?'
    )) return;

    var csrf = document.querySelector('[name="_csrf_token"]');
    if (!csrf) { alert('CSRF token bulunamadı.'); return; }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= e(url('ayarlar/yedek-restore')) ?>';

    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_csrf_token';
    csrfInput.value = csrf.value;
    form.appendChild(csrfInput);

    var dosyaInput = document.createElement('input');
    dosyaInput.type = 'hidden';
    dosyaInput.name = 'dosya';
    dosyaInput.value = dosyaAdi;
    form.appendChild(dosyaInput);

    document.body.appendChild(form);
    form.submit();
}

function yedekSilEt(dosyaAdi) {
    if (!confirm('"' + dosyaAdi + '" yedeği kalıcı olarak silinecek. Emin misiniz?')) return;

    var csrf = document.querySelector('[name="_csrf_token"]');
    if (!csrf) { alert('CSRF token bulunamadı.'); return; }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= e(url('ayarlar/yedek-sil')) ?>';

    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_csrf_token';
    csrfInput.value = csrf.value;
    form.appendChild(csrfInput);

    var dosyaInput = document.createElement('input');
    dosyaInput.type = 'hidden';
    dosyaInput.name = 'dosya';
    dosyaInput.value = dosyaAdi;
    form.appendChild(dosyaInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
</div>

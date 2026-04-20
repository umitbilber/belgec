<?php
$fatura_js_prefix = 'satis';
$fatura_item_class = 'satis-kalem-item';
$fatura_row_class = 'satis-kalem-row';
$fatura_handle_class = 'satis-drag-handle';
$fatura_content_class = 'satis-kalem-icerik';
$fatura_delete_button_class = 'satis-kalem-sil';
$fatura_edit_form_id = 'editSatisForm';
$fatura_edit_modal_id = 'editSatisModal';
$fatura_edit_container_id = 'duzenle-kalemler';
$fatura_parse_error_message = 'Satış faturası verisi çözümlenemedi:';

require BASE_PATH . '/app/Views/partials/scripts/fatura-form.php';
?>
<?php
$fatura_style_prefix = 'satis';
require BASE_PATH . '/app/Views/partials/styles/fatura-page.php';
?>
<?php
$title = 'Satış Faturaları';
$description = 'Satış faturalarını kaydet, stok çıkışlarını otomatik düş ve cari bakiye artışını sistem üzerinden takip et.';
$badgeText = 'Toplam Satış Faturası: ' . count($faturalar);
$headerClass = 'satis-page-header';
$headerLeftClass = 'satis-page-header-left';
$headerRightClass = 'satis-page-header-right';
$badgeClass = 'liste-ozet satis-stat-badge';
$actionHtml = '<button type="button" class="btn btn-ana" data-modal-open="createSatisModal">+ Yeni Satış Faturası</button>';

require BASE_PATH . '/app/Views/partials/page-header.php';
?>

<?php
$sectionClass = 'satis-list-panel';
$sectionTitle = 'Satış Faturası Listesi';
$sectionDescription = 'Kayıtlı satış faturalarını görüntüleyebilir, düzenleyebilir veya silebilirsin.';

require BASE_PATH . '/app/Views/partials/section-card.php';
?>
<div style="margin-bottom:12px;">
    <input
        type="text"
        id="satisFaturaAra"
        placeholder="Fatura no, cari veya tutar ara…"
        oninput="faturaListeFiltrele('satisFaturaAra', 'satis-table')"
        style="max-width:340px;"
    >
</div>
<div style="background:#fff;border:1px solid #e6edf5;border-radius:16px;padding:18px 20px;margin-bottom:16px;">
    <form method="GET" action="<?= e(url('satis-faturalari')) ?>">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:12px;">
            <div>
                <label>Fatura No</label>
                <input type="text" name="fatura_no" value="<?= e($filtreler['fatura_no'] ?? '') ?>" placeholder="Fatura no ara…">
            </div>
            <div>
                <label>Cari</label>
                <select name="cari_id">
                    <option value="">Tüm Cariler</option>
                    <?php foreach ($cariler as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)($filtreler['cari_id'] ?? 0)) === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= e($c['ad_soyad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Başlangıç Tarihi</label>
                <input type="date" name="tarih_baslangic" value="<?= e($filtreler['tarih_baslangic'] ?? '') ?>">
            </div>
            <div>
                <label>Bitiş Tarihi</label>
                <input type="date" name="tarih_bitis" value="<?= e($filtreler['tarih_bitis'] ?? '') ?>">
            </div>
            <div>
                <label>Min Tutar</label>
                <input type="number" name="tutar_min" step="0.01" value="<?= e($filtreler['tutar_min'] ?? '') ?>" placeholder="0.00">
            </div>
            <div>
                <label>Max Tutar</label>
                <input type="number" name="tutar_max" step="0.01" value="<?= e($filtreler['tutar_max'] ?? '') ?>" placeholder="0.00">
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-ana">Filtrele</button>
            <a href="<?= e(url('satis-faturalari')) ?>" class="btn btn-gri">Sıfırla</a>
        </div>
    </form>
</div>
    <div class="table-wrap">
        <table class="satis-table">
            <thead>
                <tr>
                    <th>Fatura No</th>
                    <th>Tarih</th>
                    <th>Cari</th>
                    <th>Genel Toplam</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faturalar as $row): ?>
                    <tr id="fatura-<?= (int) $row['id'] ?>">
                        <td>
                            <div class="veri-vurgu"><?= e($row['fatura_no']) ?></div>
                            <div class="satir-ikincil">Satış Faturası</div>
                        </td>
                        <td>
    <div><?= e($row['tarih']) ?></div>
    <div class="satir-ikincil">Vade: <?= e($row['vade_tarihi'] ?? '-') ?></div>
</td>
                        <td><?= e($row['cari_adi'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-yesil satis-total-badge">
                                <?= number_format((float) $row['genel_toplam'], 2, ',', '.') ?> TL
                            </span>
                        </td>
                        <td>
                            <div class="aksiyonlar satis-actions">
                                <button
    type="button"
    class="btn btn-sari"
    onclick="satisFaturaDuzenleAc(this)"
    data-fatura='<?= e(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
>
    Düzenle
</button>
                                <form method="POST" action="<?= e(url('satis-faturalari/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu satış faturasını silmek istediğinize emin misiniz?');">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <button type="submit" class="btn btn-kirmizi">Sil</button>
</form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($faturalar)): ?>
                    <tr>
                        <td colspan="5">Henüz kayıtlı satış faturası bulunmuyor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/datalists/cari-listesi.php'; ?>
<?php $include_stok_kodu_listesi = true; ?>
<?php require BASE_PATH . '/app/Views/partials/datalists/stok-listeleri.php'; ?>

<?php if (!empty($edm_onbilgi)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('createSatisForm');
    if (!form) return;

    var cariInput     = form.querySelector('input[name="cari_adi"]');
    var faturaNoInput = form.querySelector('input[name="fatura_no"]');
    var tarihInput    = form.querySelector('input[name="tarih"]');

    if (cariInput)     cariInput.value    = <?= json_encode($edm_onbilgi['cari_adi']) ?>;
    if (faturaNoInput) faturaNoInput.value = <?= json_encode($edm_onbilgi['fatura_no']) ?>;
    if (tarihInput)    tarihInput.value   = <?= json_encode($edm_onbilgi['tarih']) ?>;

    var kalemler = <?= json_encode($edm_onbilgi['kalemler'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var container = document.getElementById('yeni-kalemler');

    if (kalemler.length > 0 && container) {
        container.innerHTML = '';
        kalemler.forEach(function (k, i) {
            faturaYeniKalemEkle('yeni-kalemler', {
                stok_kodu:   k.stok_kodu   || '',
                urun_adi:    k.urun_adi    || '',
                miktar:      k.miktar      || '1',
                birim_fiyat: k.birim_fiyat || '',
                kdv_orani:   k.kdv_orani   || 20,
                sira:        i + 1
            });
        });
    } else {
        <?php
        $kdvli  = (float) ($edm_onbilgi['kdvli_tutar'] ?? 0);
        $kdvsiz = (float) ($edm_onbilgi['kdvsiz_tutar'] ?? 0);
        if ($kdvli > 0 || $kdvsiz > 0):
            $birimFiyat = $kdvsiz > 0 ? $kdvsiz : ($kdvli > 0 ? round($kdvli / 1.20, 4) : 0);
            $kdvOrani   = ($kdvsiz > 0 && $kdvli > 0) ? round((($kdvli - $kdvsiz) / $kdvsiz) * 100) : 20;
        ?>
        var ilkKalem = form.querySelector('.satis-kalem-item');
        if (ilkKalem) {
            var bp = ilkKalem.querySelector('input[name="birim_fiyat[]"]');
            var mq = ilkKalem.querySelector('input[name="miktar[]"]');
            var kv = ilkKalem.querySelector('input[name="kdv_orani[]"]');
            if (bp) bp.value = '<?= $birimFiyat ?>';
            if (mq) mq.value = '1';
            if (kv) kv.value = '<?= $kdvOrani ?>';
        }
        <?php endif; ?>
    }

    var modal = document.getElementById('createSatisModal');
    if (modal) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }
});
</script>
<script>
function faturaListeFiltrele(inputId, tableClass) {
    var q    = document.getElementById(inputId).value.trim().toLowerCase();
    var rows = document.querySelectorAll('.' + tableClass + ' tbody tr');

    rows.forEach(function(tr) {
        var metin = tr.textContent.toLowerCase();
        tr.style.display = (!q || metin.includes(q)) ? '' : 'none';
    });
}
</script>
<?php endif; ?>
<?php require __DIR__ . '/partials/create-modal.php'; ?>
<?php require __DIR__ . '/partials/edit-modal.php'; ?>
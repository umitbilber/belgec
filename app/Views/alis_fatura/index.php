<?php
$fatura_js_prefix = 'alis';
$fatura_item_class = 'alis-kalem-item';
$fatura_row_class = 'alis-kalem-row';
$fatura_handle_class = 'alis-drag-handle';
$fatura_content_class = 'alis-kalem-icerik';
$fatura_delete_button_class = 'alis-kalem-sil';
$fatura_edit_form_id = 'editAlisForm';
$fatura_edit_modal_id = 'editAlisModal';
$fatura_edit_container_id = 'duzenle-kalemler';
$fatura_parse_error_message = 'Alış faturası verisi çözümlenemedi:';

require BASE_PATH . '/app/Views/partials/scripts/fatura-form.php';
?>
<?php
$fatura_style_prefix = 'alis';
require BASE_PATH . '/app/Views/partials/styles/fatura-page.php';
?>
<?php
$title = 'Alış Faturaları';
$description = 'Alış faturalarını kaydet, stok girişlerini ve cari bakiye etkilerini otomatik şekilde işle.';
$badgeText = 'Toplam Alış Faturası: ' . count($faturalar);
$headerClass = 'alis-page-header';
$headerLeftClass = 'alis-page-header-left';
$headerRightClass = 'alis-page-header-right';
$badgeClass = 'liste-ozet alis-stat-badge';
$actionHtml = '<button type="button" class="btn btn-ana" data-modal-open="createAlisModal">+ Yeni Alış Faturası</button>';

require BASE_PATH . '/app/Views/partials/page-header.php';
?>

<?php
$sectionClass = 'alis-list-panel';
$sectionTitle = 'Alış Faturası Listesi';
$sectionDescription = 'Kayıtlı alış faturalarını görüntüleyebilir, düzenleyebilir veya silebilirsin.';

require BASE_PATH . '/app/Views/partials/section-card.php';
?>


<div style="margin-bottom:12px;">
    <input
        type="text"
        id="alisFaturaAra"
        placeholder="Fatura no, cari veya tutar ara…"
        oninput="faturaListeFiltrele('alisFaturaAra', 'alis-table')"
        style="max-width:340px;"
    >
</div>
<div style="background:#fff;border:1px solid #e6edf5;border-radius:16px;padding:18px 20px;margin-bottom:16px;">
    <form method="GET" action="<?= e(url('alis-faturalari')) ?>">
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
            <a href="<?= e(url('alis-faturalari')) ?>" class="btn btn-gri">Sıfırla</a>
        </div>
    </form>
</div>
    <div class="table-wrap">
        <table class="alis-table">
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
                            <div class="satir-ikincil">Alış Faturası</div>
                        </td>
                        <td>
    <div><?= e($row['tarih']) ?></div>
    <div class="satir-ikincil">Vade: <?= e($row['vade_tarihi'] ?? '-') ?></div>
</td>
                        <td><?= e($row['cari_adi'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-mavi alis-total-badge">
                                <?= number_format((float) $row['genel_toplam'], 2, ',', '.') ?> TL
                            </span>
                        </td>
                        <td>
                            <div class="aksiyonlar alis-actions">
                                <button
    type="button"
    class="btn btn-sari"
    onclick="alisFaturaDuzenleAc(this)"
    data-fatura='<?= e(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
>
    Düzenle
</button>
                                <form method="POST" action="<?= e(url('alis-faturalari/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu alış faturasını silmek istediğinize emin misiniz?');">
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
                        <td colspan="5">Henüz kayıtlı alış faturası bulunmuyor.</td>
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
    var form = document.getElementById('createAlisForm');
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
        var ilkKalem = form.querySelector('.alis-kalem-item');
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

    var modal = document.getElementById('createAlisModal');
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
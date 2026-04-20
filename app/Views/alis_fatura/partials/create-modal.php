<?php
ob_start();
?>
<div class="form-aciklama-kutu">
    Alış faturası kaydedildiğinde stok miktarı artar ve cari bakiyesi alış tutarı kadar azalır.
</div>

<form method="POST" action="<?= e(url('alis-faturalari/ekle')) ?>" class="app-form-stack" id="createAlisForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <div class="form-grid-3">
        <div>
            <label>Cari Adı</label>
            <input type="text" name="cari_adi" list="cariListesi" class="cari-autocomplete" required data-modal-autofocus>
        </div>
        <div>
            <label>Fatura No</label>
            <input type="text" name="fatura_no" required>
        </div>
        <div>
            <label>Tarih</label>
            <input type="date" name="tarih" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div>
    <label>Vade Tarihi</label>
    <input type="date" name="vade_tarihi" value="<?= date('Y-m-d') ?>">
</div>
    </div>

    <div class="panel-divider"></div>

    <?php
$formSectionTitle = 'Fatura Kalemleri';
$formSectionDescription = 'Stok kodu, ürün adı, miktar, birim fiyat ve KDV oranını gir.';

require BASE_PATH . '/app/Views/partials/form-section-header.php';
?>

    <div id="yeni-kalemler" class="alis-kalemler-wrap sortable-list" data-sort-container>
        <div class="kalem-blok sortable-item alis-kalem-item" data-sort-item="1" draggable="true">
            <div class="alis-kalem-row">
                <button type="button" class="drag-handle alis-drag-handle" title="Sürükleyerek taşı">⋮⋮</button>

                <div class="alis-kalem-icerik">
                    <div class="kalem-grid-fatura">
                        <input type="text" name="stok_kodu[]" placeholder="Stok kodu" list="stokKodlariListesi" class="stok-kodu-autocomplete">
                        <input type="text" name="urun_adi[]" placeholder="Ürün adı" list="stokUrunleriListesi" class="stok-adi-autocomplete">
                        <input type="number" step="0.0001" name="miktar[]" placeholder="Miktar">
                        <input type="number" step="0.0001" name="birim_fiyat[]" placeholder="Birim fiyat">
                        <input type="number" name="kdv_orani[]" placeholder="KDV" value="20">
                    </div>

                    <input type="hidden" name="kalem_sira[]" value="1" data-sort-order-input>
                </div>

                <button type="button" class="btn btn-kirmizi alis-kalem-sil" onclick="alisKalemSil(this)">Sil</button>
                <button type="button" class="fiyat-gecmis-btn" title="Fiyat Geçmişi" onclick="fiyatGecmisiAc(this)" style="width:32px;height:32px;min-width:32px;padding:0;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">📈</button>
            </div>
        </div>
    </div>

    <div class="panel-divider"></div>

    <div class="alis-form-actions">
        <button type="button" class="btn btn-gri" onclick="yeniAlisKalemEkle('yeni-kalemler')">Kalem Ekle</button>
    </div>

    <div class="panel-divider"></div>

<div id="alisTutarOzet" style="background:#f8fafc;border:1px solid #e6edf5;border-radius:10px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;gap:24px;flex-wrap:wrap;">
        <div>
            <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">KDV'siz Toplam</div>
            <div id="alisKdvsizToplam" style="font-size:16px;font-weight:700;color:#1e293b;">0,00 ₺</div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">KDV Tutarı</div>
            <div id="alisKdvTutari" style="font-size:16px;font-weight:700;color:#64748b;">0,00 ₺</div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Genel Toplam</div>
            <div id="alisGenelToplam" style="font-size:18px;font-weight:800;color:#2563eb;">0,00 ₺</div>
        </div>
    </div>
</div>

<div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Alış Faturasını Kaydet</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'createAlisModal';
$modalTitle = 'Yeni Alış Faturası';
$modalDescription = 'Cari, tarih ve fatura kalemlerini tek form üzerinden kaydedin.';
$modalSize = 'xl';
$modalClass = 'app-modal-alis';

require __DIR__ . '/../../partials/modal.php';
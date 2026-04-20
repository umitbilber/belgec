<?php
ob_start();
?>
<form method="POST" action="<?= e(url('satis-faturalari/guncelle')) ?>" class="app-form-stack" id="editSatisForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="fatura_id" value="">

    <div class="form-grid-3">
        <div>
            <label>Cari Adı</label>
            <input type="text" name="cari_adi" list="cariListesi" class="cari-autocomplete" required>
        </div>
        <div>
            <label>Fatura No</label>
            <input type="text" name="fatura_no" required>
        </div>
        <div>
            <label>Tarih</label>
            <input type="date" name="tarih" required>
        </div>
        <div>
    <label>Vade Tarihi</label>
    <input type="date" name="vade_tarihi">
</div>
    </div>

    <div class="panel-divider"></div>

    <?php
$formSectionTitle = 'Fatura Kalemleri';
$formSectionDescription = 'Mevcut kalemleri güncelle, sürükleyerek sırala veya yeni satır ekle.';

require BASE_PATH . '/app/Views/partials/form-section-header.php';
?>

    <div id="duzenle-kalemler" class="satis-duzenle-kalemler-wrap sortable-list" data-sort-container>
        <div class="kalem-blok sortable-item satis-kalem-item" data-sort-item="1" draggable="true">
            <div class="satis-kalem-row">
                <button type="button" class="drag-handle satis-drag-handle" title="Sürükleyerek taşı">⋮⋮</button>

                <div class="satis-kalem-icerik">
                    <div class="kalem-grid-fatura">
                        <input type="text" name="stok_kodu[]" placeholder="Stok kodu" list="stokKodlariListesi" class="stok-kodu-autocomplete">
                        <input type="text" name="urun_adi[]" placeholder="Ürün adı" list="stokUrunleriListesi" class="stok-adi-autocomplete">
                        <input type="number" step="0.0001" name="miktar[]" placeholder="Miktar">
                        <input type="number" step="0.0001" name="birim_fiyat[]" placeholder="Birim fiyat">
                        <input type="number" name="kdv_orani[]" value="20" placeholder="KDV">
                    </div>

                    <input type="hidden" name="kalem_sira[]" value="1" data-sort-order-input>
                </div>

                <button type="button" class="btn btn-kirmizi satis-kalem-sil" onclick="satisKalemSil(this)">Sil</button>
            </div>
        </div>
    </div>

    <div class="panel-divider"></div>

    <div class="satis-modal-actions">
        <button type="button" class="btn btn-gri" onclick="yeniSatisKalemEkle('duzenle-kalemler')">Kalem Ekle</button>
    </div>

    <div class="panel-divider"></div>

<div style="background:#f8fafc;border:1px solid #e6edf5;border-radius:10px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;gap:24px;flex-wrap:wrap;">
        <div>
            <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">KDV'siz Toplam</div>
            <div id="satisEditKdvsizToplam" style="font-size:16px;font-weight:700;color:#1e293b;">0,00 ₺</div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">KDV Tutarı</div>
            <div id="satisEditKdvTutari" style="font-size:16px;font-weight:700;color:#64748b;">0,00 ₺</div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Genel Toplam</div>
            <div id="satisEditGenelToplam" style="font-size:18px;font-weight:800;color:#2563eb;">0,00 ₺</div>
        </div>
    </div>
</div>

<div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Güncelle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'editSatisModal';
$modalTitle = 'Satış Faturası Düzenle';
$modalDescription = 'Fatura bilgilerini ve satış kalemlerini güncelleyin.';
$modalSize = 'xl';
$modalClass = 'app-modal-satis';

require __DIR__ . '/../../partials/modal.php';
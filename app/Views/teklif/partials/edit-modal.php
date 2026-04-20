<?php
ob_start();
?>
<form method="POST" action="<?= e(url('teklifler/guncelle')) ?>" class="app-form-stack" id="editTeklifForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="teklif_id" value="">

    <div class="form-grid-3">
        <div>
            <label>Müşteri / Cari Adı</label>
            <input type="text" name="cari_adi" value="" list="cariListesi" class="cari-autocomplete" required>
        </div>
        <div>
            <label>Teklif No</label>
            <input type="text" name="teklif_no" value="" required>
        </div>
        <div>
            <label>Tarih</label>
            <input type="date" name="tarih" value="" required>
        </div>
    </div>

    <div class="panel-divider"></div>

    <?php
$formSectionTitle = 'Teklif Kalemleri';
$formSectionDescription = 'Kalemleri güncelle, sürükleyerek sırala veya yeni satır ekle.';

require BASE_PATH . '/app/Views/partials/form-section-header.php';
?>

    <div id="duzenle-kalemler" class="teklif-duzenle-kalemler-wrap sortable-list" data-sort-container>
        <div class="kalem-blok sortable-item teklif-kalem-item" data-sort-item="1" draggable="true">
            <div class="teklif-kalem-row">
                <button type="button" class="drag-handle teklif-drag-handle" title="Sürükleyerek taşı">⋮⋮</button>

                <div class="teklif-kalem-icerik">
                    <div class="kalem-grid-teklif">
    <input type="text" name="urun_adi[]" placeholder="Ürün adı" list="stokUrunleriListesi" class="stok-adi-autocomplete">
    <input type="text" name="marka[]" placeholder="Marka">
    <input type="number" step="0.0001" name="miktar[]" placeholder="Miktar" oninput="teklifSatirHesapla(this)">
    <input type="number" step="0.0001" name="birim_fiyat[]" placeholder="Birim fiyat" oninput="teklifBirimFiyatDegisti(this)">
    <input type="number" step="0.0001" name="satir_toplam[]" placeholder="Toplam fiyat" oninput="teklifSatirToplamiElleDegisti(this)">
    <input type="text" name="termin[]" placeholder="Termin">
    <select name="kalem_para_birimi[]">
        <option value="TL">₺</option>
        <option value="USD">$</option>
        <option value="EUR">€</option>
    </select>
</div>

                    <input type="hidden" name="kalem_sira[]" value="1" data-sort-order-input>
                </div>

                <button type="button" class="btn btn-kirmizi teklif-kalem-sil" onclick="teklifKalemSil(this)">
                    Sil
                </button>
            </div>
        </div>
    </div>

    <div class="teklif-modal-actions">
        <button type="button" class="btn btn-gri" onclick="yeniKalemEkle('duzenle-kalemler')">Kalem Ekle</button>
    </div>

    <label>Teklif Şartları</label>
        <textarea class="teklif-notlar teklif-notlar-yuksek" name="teklif_notlari"></textarea>

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Güncelle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'editTeklifModal';
$modalTitle = 'Teklifi Düzenle';
$modalDescription = 'Kalemleri güncelle, sürükleyerek sırala veya yeni satır ekle.';
$modalSize = 'xl';
$modalClass = 'app-modal-teklif';

require __DIR__ . '/../../partials/modal.php';
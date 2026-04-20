<?php
ob_start();
?>
<div class="bilgi-kutusu" id="cariWolvoxBilgiKutusu">
    <strong>Cari Unvanı:</strong> <span data-cari-wolvox-unvan>-</span>
</div>

<div class="bilgi-kutusu" style="margin-top:10px;">
    Bu araç seçilen cariye ait mevcut fatura ve cari hareket geçmişini temizleyip Wolvox ekstresine göre yeniden oluşturur.
    Önce önizleme oluşturulur, onaydan sonra gerçek aktarım yapılır.
</div>

<form
    method="POST"
    action="<?= e(url('cariler/wolvox/onizleme')) ?>"
    enctype="multipart/form-data"
    class="app-form-stack"
    id="cariWolvoxImportForm"
    style="margin-top:14px;"
>
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="cari_id" value="">
	
	<div class="form-group" style="margin-top:12px;">
    <label for="aktarim_profili">Aktarım Profili</label>
    <select name="aktarim_profili" id="aktarim_profili" class="form-control" required>
        <option value="musteri">Müşteri Ekstresi</option>
        <option value="tedarikci">Tedarikçi Ekstresi</option>
    </select>
    <small style="display:block; margin-top:6px; color:#6b7280;">
        Müşteri ekstresinde borç satış faturası, alacak tahsilat olarak; tedarikçi ekstresinde borç tediye, alacak alış faturası olarak yorumlanır.
    </small>
</div>

    <label>Ekstre Dosyası (.xlsx veya .pdf)</label>
<input type="file" name="ekstre_dosyasi" accept=".xlsx,.pdf,application/pdf" required data-modal-autofocus>

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Önizleme Oluştur ve Kuyruğa Ekle</button>
    </div>
</form>

<div id="cariWolvoxPreviewWrap" style="display:none; margin-top:18px;">
    <div class="panel-divider"></div>

    <div class="ozet-kutular" style="margin-top:14px; margin-bottom:14px;">
        <div class="ozet-kutu">
            <div class="ozet-etiket">Dosya</div>
            <div class="ozet-deger" id="wolvoxPreviewDosya" style="font-size:13px;">-</div>
        </div>

        <div class="ozet-kutu">
            <div class="ozet-etiket">Satış Faturası</div>
            <div class="ozet-deger" id="wolvoxPreviewSatis">0</div>
        </div>

        <div class="ozet-kutu">
            <div class="ozet-etiket">Tahsilat</div>
            <div class="ozet-deger" id="wolvoxPreviewTahsilat">0</div>
        </div>

        <div class="ozet-kutu">
            <div class="ozet-etiket">Belirsiz</div>
            <div class="ozet-deger" id="wolvoxPreviewBelirsiz">0</div>
        </div>

        <div class="ozet-kutu">
            <div class="ozet-etiket">Beklenen Bakiye</div>
            <div class="ozet-deger" id="wolvoxPreviewBakiye">0,00 TL</div>
        </div>
    </div>

    <div class="bilgi-kutusu" style="margin-bottom:12px; border-color:#fecaca; background:#fff5f5; color:#991b1b;">
        Dikkat: Gerçek aktarım adımında seçilen cariye ait mevcut alış/satış faturaları ve cari hareketler silinip ekstreye göre yeniden oluşturulacak.
        Bu ekranda henüz veritabanına kayıt yapılmadı.
    </div>

    <div class="table-wrap">
        <table class="cari-table">
            <thead>
                <tr>
                    <th>Satır</th>
                    <th>Tarih</th>
                    <th>Borç</th>
                    <th>Alacak</th>
                    <th>Yorum</th>
                </tr>
            </thead>
            <tbody id="cariWolvoxPreviewBody">
                <tr>
                    <td colspan="5">Önizleme verisi bulunamadı.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="app-modal-footer" style="margin-top:14px;">
        <button type="button" class="btn btn-gri" data-modal-close>Kapat</button>

        <form
            method="POST"
            action="<?= e(url('cariler/wolvox/aktar')) ?>"
            id="cariWolvoxExecuteForm"
            style="display:inline-flex;"
            onsubmit="return confirm('Bu cariye ait mevcut faturalar ve cari hareketler silinip Wolvox ekstresine göre yeniden oluşturulacak. Devam edilsin mi?');"
        >
            <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
            <input type="hidden" name="cari_id" value="<?= (int) (($wolvox_preview['cari_id'] ?? 0)) ?>">
            <button type="submit" class="btn btn-ana">İçeri Aktar</button>
        </form>
    </div>
</div>
<?php
$modalContent = ob_get_clean();

$modalId = 'cariWolvoxImportModal';
$modalTitle = 'Wolvox Ekstresi Aktar';
$modalDescription = 'Seçilen cariye ait Wolvox ekstresini .xlsx veya .pdf olarak yükleyin. Önizleme bu modal içinde gösterilir.';
$modalSize = 'xl';

require __DIR__ . '/../../partials/modal.php';
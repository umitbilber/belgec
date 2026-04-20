<?php
ob_start();
?>
<div class="bilgi-kutusu" id="cariMutabakatBilgiKutusu">
    <strong>Cari Unvanı:</strong> <span data-cari-mutabakat-unvan>-</span><br>
    <strong>E-Posta:</strong> <span data-cari-mutabakat-eposta>-</span><br>
    <strong>Güncel Bakiye:</strong> <span data-cari-mutabakat-bakiye style="font-weight:bold;">-</span>
</div>

<form method="POST" action="<?= e(url('mutabakat/gonder')) ?>" class="app-form-stack" id="cariMutabakatForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="cari_id" value="">

    <div class="form-aciklama-kutu">
        Bu işlem seçili cariye mutabakat e-postası gönderir. Aşağıdaki metni göndermeden önce düzenleyebilirsiniz.
    </div>

    <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
            Mail Metni
            <span style="font-size:10px;font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;margin-left:6px;">
    metne <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;font-size:10px;">{cari_adi}</code> veya <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;font-size:10px;">{bakiye}</code> yazarsan otomatik doldurulur
</span>
        </label>
        <textarea
            name="mutabakat_metni"
            id="mutabakatMetniAlani"
            rows="8"
            style="width:100%;font-family:inherit;font-size:13px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;resize:vertical;line-height:1.6;box-sizing:border-box;color:#1e293b;"
        ></textarea>
        <div style="margin-top:6px;display:flex;justify-content:flex-end;">
            <button type="button" onclick="mutabakatVarsayilanaRestore()" style="font-size:11px;color:#64748b;background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;">
                Varsayılana sıfırla
            </button>
        </div>
    </div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Mutabakat Gönder</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId          = 'cariMutabakatModal';
$modalTitle       = 'Mutabakat Gönder';
$modalDescription = 'Seçili cariye bakiye mutabakat e-postası gönderin.';
$modalSize        = 'lg';

require __DIR__ . '/../../partials/modal.php';
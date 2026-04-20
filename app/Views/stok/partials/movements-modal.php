<?php
ob_start();
?>
<div class="bilgi-kutusu stok-hareket-bilgi" id="stokHareketBilgiKutusu">
    <strong>Stok Kodu:</strong> <span data-stok-hareket-kodu>-</span><br>
    <strong>Ürün Adı:</strong> <span data-stok-hareket-urun>-</span><br>
    <strong>Mevcut Miktar:</strong> <span data-stok-hareket-miktar>-</span>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Tarih</th>
                <th>İşlem Tipi</th>
                <th>Firma</th>
                <th>Fatura No</th>
                <th>Miktar</th>
                <th>Açıklama</th>
            </tr>
        </thead>
        <tbody id="stokHareketlerBody">
            <tr>
                <td colspan="6">Bu stok kartına ait hareket bulunmuyor.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="panel-divider"></div>

<div class="app-modal-footer">
    <button type="button" class="btn btn-gri" data-modal-close>Kapat</button>
</div>
<?php
$modalContent = ob_get_clean();

$modalId = 'stokMovementsModal';
$modalTitle = 'Stok Hareketleri';
$modalDescription = 'Seçili stok kartının hareket geçmişini görüntüleyin.';
$modalSize = 'lg';

require __DIR__ . '/../../partials/modal.php';
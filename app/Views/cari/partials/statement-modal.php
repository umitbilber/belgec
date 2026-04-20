<?php
ob_start();
?>
<div class="bilgi-kutusu">
    <strong>Cari Unvanı:</strong> <span data-cari-ekstre-unvan>-</span><br>
    <strong>Güncel Bakiye:</strong>
    <span data-cari-ekstre-bakiye style="font-weight: bold;">-</span>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Tarih</th>
                <th>İşlem Tipi</th>
                <th>Fatura No</th>
                <th>Açıklama</th>
                <th>Tutar</th>
            </tr>
        </thead>
        <tbody id="cariEkstreBody">
            <tr>
                <td colspan="5">Bu cariye ait hareket bulunmuyor.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="panel-divider"></div>

<div class="app-modal-footer">
    <a class="btn btn-gri" id="cariEkstreYazdirLink" href="#" target="_blank">Yazdır</a>
    <button type="button" class="btn btn-gri" data-modal-close>Kapat</button>
</div>
<?php
$modalContent = ob_get_clean();

$modalId = 'cariStatementModal';
$modalTitle = 'Cari Ekstresi';
$modalDescription = 'Cari hareket geçmişini ve güncel bakiyeyi görüntüleyin.';
$modalSize = 'lg';

require __DIR__ . '/../../partials/modal.php';
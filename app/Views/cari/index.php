<?php require BASE_PATH . '/app/Views/partials/scripts/cari-page.php'; ?>
<?php require BASE_PATH . '/app/Views/partials/styles/cari-page.php'; ?>

<?php
$title = 'Cari Yönetimi';
$description = 'Cari kartlarını yönetin, tahsilat ve tediye işlemlerini kaydedin, mutabakat gönderin ve hesap ekstrelerini görüntüleyin.';
$badgeText = 'Toplam Cari: ' . count($cariler);
$headerClass = 'cari-page-header';
$headerLeftClass = 'cari-page-header-left';
$headerRightClass = 'cari-page-header-right';
$badgeClass = 'istatistik-rozet cari-stat-badge';
$actionHtml = '<button type="button" class="btn btn-ana" data-modal-open="createCariModal">+ Yeni Cari</button>';

require BASE_PATH . '/app/Views/partials/page-header.php';
?>


    <?php
$sectionClass = 'cari-list-panel';
$sectionTitle = 'Cari Listesi';
$sectionDescription = 'Kayıtlı cariler arasında arama yapabilir ve işlemlere hızlı erişebilirsin.';

require BASE_PATH . '/app/Views/partials/section-card.php';
?>

        <div class="arama">
            <input type="text" id="cariAramaInput" onkeyup="cariFiltrele()" placeholder="Cari adı, e-posta veya telefon ile ara...">
        </div>

        <div class="table-wrap">
            <table id="cariTablosuGosterimi" class="cari-table">
                <thead>
                    <tr>
                        <th>Cari Unvanı</th>
                        <th>İletişim</th>
                        <th>Bakiye</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cariler as $cari): ?>
                        <tr>
                            <td class="cari-name-cell">
                                <div class="veri-vurgu"><?= e($cari['ad_soyad']) ?></div>
                                <div class="alt-metin">Vergi No: <?= e($cari['vergi_no'] ?? '-') ?></div>
                                <div class="alt-metin">Varsayılan Vade: <?= (int) ($cari['varsayilan_vade_gun'] ?? 0) ?> gün</div>
                            </td>
                            <td class="cari-contact-cell">
                                <div><?= e($cari['telefon'] ?? '-') ?></div>
                                <div class="alt-metin"><?= e($cari['eposta'] ?? '-') ?></div>
                            </td>
                            <td>
                                <?php
                                $bakiye = (float) ($cari['bakiye'] ?? 0);
                                $etiket = '';
                                $renkSinifi = 'badge-gri';

                                if ($bakiye > 0) {
                                    $etiket = 'B';
                                    $renkSinifi = 'badge-yesil';
                                } elseif ($bakiye < 0) {
                                    $etiket = 'A';
                                    $renkSinifi = 'badge-kirmizi';
                                }
                                ?>
                                <span class="badge cari-balance-badge <?= e($renkSinifi) ?>">
                                    <?= number_format(abs($bakiye), 2, ',', '.') ?> TL<?= $etiket !== '' ? ' (' . e($etiket) . ')' : '' ?>
                                </span>
                            </td>
                            <td>
                                <div class="aksiyonlar cari-actions">
    <button
        type="button"
        class="btn btn-gri"
        onclick="cariEkstreAc(this)"
        data-cari='<?= e(json_encode($cari, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
    >
        Ekstre
    </button>
    <button
        type="button"
        class="btn btn-yesil"
        onclick="cariHareketAc(this, 'tahsilat')"
        data-cari='<?= e(json_encode($cari, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
    >
        Tahsilat
    </button>

    <button
        type="button"
        class="btn btn-turuncu"
        onclick="cariHareketAc(this, 'tediye')"
        data-cari='<?= e(json_encode($cari, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
    >
        Tediye
    </button>

    <button
    type="button"
    class="btn btn-ana"
    onclick="cariMutabakatAc(this)"
    data-cari='<?= e(json_encode($cari, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
>
    Mutabakat
</button>

    <button
        type="button"
        class="btn btn-sari"
        onclick="cariDuzenleAc(this)"
        data-cari='<?= e(json_encode($cari, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
    >
        Düzenle
    </button>

    <form method="POST" action="<?= e(url('cariler/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu cariyi silmek istediğinize emin misiniz?');">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="id" value="<?= (int)$cari['id'] ?>">
    <button type="submit" class="btn btn-kirmizi">Sil</button>
</form>
</div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($cariler)): ?>
                        <tr>
                            <td colspan="4">Henüz kayıtlı cari bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
	<script>
window.mutabakatVarsayilanMetin = <?= json_encode($ayarlar['varsayilan_mutabakat_metni'] ?? "Sayın {cari_adi},\n\nSistemimizde cari hesabınıza ait güncel bakiye {bakiye} olarak görünmektedir.\n\nBu bakiye ile mutabık iseniz veya değilseniz lütfen aşağıdaki butonlardan yanıt veriniz.\n\nİyi çalışmalar dileriz.", JSON_UNESCAPED_UNICODE) ?>;
</script>
	<?php require __DIR__ . '/partials/create-modal.php'; ?>
<?php require __DIR__ . '/partials/edit-modal.php'; ?>
<?php require __DIR__ . '/partials/movement-modal.php'; ?>
<?php require __DIR__ . '/partials/statement-modal.php'; ?>
<?php require __DIR__ . '/partials/reconciliation-modal.php'; ?>
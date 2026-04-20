<div class="kutu">
    <h3>Mutabakat Gönder</h3>

    <?php if (!empty($cari)): ?>
        <div class="bilgi-kutusu">
            <strong>Cari:</strong> <?= e($cari['ad_soyad']) ?><br>
            <strong>E-Posta:</strong> <?= e($cari['eposta'] ?? '-') ?><br>
            <td>
    <?php
    $bakiye = (float) ($cari['bakiye'] ?? 0);
    $etiket = '';
    $renk = '#444';

    if ($bakiye > 0) {
        $etiket = 'B';
        $renk = '#16a34a';
    } elseif ($bakiye < 0) {
        $etiket = 'A';
        $renk = '#d61f1f';
    }
    ?>
    <strong style="color: <?= e($renk) ?>;">
        <?= number_format(abs($bakiye), 2, ',', '.') ?> TL<?= $etiket !== '' ? ' (' . e($etiket) . ')' : '' ?>
    </strong>
</td>
        </div>

        <form method="POST" action="<?= e(url('mutabakat/gonder')) ?>">
            <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
            <input type="hidden" name="cari_id" value="<?= (int) $cari['id'] ?>">

            <p>
                Bu işlem seçili cariye mutabakat e-postası gönderir.
                E-postadaki bağlantılar üzerinden müşteri “Evet” veya “Hayır” yanıtı verebilir.
            </p>

            <button type="submit" class="btn btn-ana">Mutabakat Gönder</button>
        </form>
    <?php else: ?>
        <div class="hata-kutusu">
            Geçerli bir cari seçilmedi. Cari ekranından bir kayıt seçerek gelmelisin.
        </div>
    <?php endif; ?>
</div>
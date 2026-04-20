<style>
.anasayfa-wrap {
    max-width: 1200px;
    margin: 0 auto;
}
.anasayfa-hosgeldin {
    margin-bottom: 32px;
}
.anasayfa-hosgeldin h2 {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px 0;
}
.anasayfa-hosgeldin p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}
.anasayfa-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 18px;
}
.anasayfa-kart {
    background: #fff;
    border: 1px solid #e6edf5;
    border-radius: 18px;
    padding: 24px 20px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 2px 12px rgba(15,23,42,.05);
    transition: transform .15s ease, box-shadow .15s ease;
    text-decoration: none;
    color: inherit;
}
.anasayfa-kart:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(15,23,42,.10);
}
.anasayfa-kart-ikon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    flex-shrink: 0;
}
.anasayfa-kart-baslik {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.anasayfa-kart-aciklama {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
    flex: 1;
}
.anasayfa-kart-buton {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    transition: opacity .15s;
    align-self: flex-start;
}
.anasayfa-kart-buton:hover {
    opacity: .88;
}
@media (max-width: 640px) {
    .anasayfa-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .anasayfa-hosgeldin h2 { font-size: 20px; }
}
@media (max-width: 400px) {
    .anasayfa-grid { grid-template-columns: 1fr; }
}
</style>

<div class="anasayfa-wrap">
    <div class="anasayfa-hosgeldin">
        <?php
$kullaniciAd = $_SESSION['kullanici_ad'] ?? 'Yönetici';
$firmaAdi    = $ayarlar['sirket_adi'] ?? 'Belgeç';
?>
<h2>Hoş geldin, <?= e($kullaniciAd) ?></h2>
<p><?= e($firmaAdi) ?> &mdash; Aşağıdan bir modül seçerek işlemlerine başlayabilirsin.</p>
    </div>

    <div class="anasayfa-grid">
        <?php foreach ($moduller as $modul): ?>
        <a href="<?= e($modul['url']) ?>" class="anasayfa-kart">
            <div class="anasayfa-kart-ikon" style="background:<?= e($modul['renk']) ?>18;color:<?= e($modul['renk']) ?>;">
                <?= e(mb_substr($modul['baslik'], 0, 1, 'UTF-8')) ?>
            </div>
            <div>
                <p class="anasayfa-kart-baslik"><?= e($modul['baslik']) ?></p>
                <p class="anasayfa-kart-aciklama"><?= e($modul['aciklama']) ?></p>
            </div>
            <span class="anasayfa-kart-buton" style="background:<?= e($modul['renk']) ?>;">
                <?= e($modul['buton']) ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
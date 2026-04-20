<?php
$title = 'Raporlar';
$description = 'Finansal analizler, satış istatistikleri ve cari bakiye raporları.';
$badgeText = '';
$headerClass = 'rapor-page-header';
$headerLeftClass = 'rapor-page-header-left';
$headerRightClass = 'rapor-page-header-right';
$badgeClass = 'istatistik-rozet';
$actionHtml = '';
require BASE_PATH . '/app/Views/partials/page-header.php';

function raporPara(float $val): string {
    return number_format($val, 2, ',', '.') . ' ₺';
}

function ayEtiket(string $ay): string {
    $aylar = ['01'=>'Oca','02'=>'Şub','03'=>'Mar','04'=>'Nis','05'=>'May','06'=>'Haz',
               '07'=>'Tem','08'=>'Ağu','09'=>'Eyl','10'=>'Eki','11'=>'Kas','12'=>'Ara'];
    [$yil, $no] = explode('-', $ay);
    return ($aylar[$no] ?? $no) . ' ' . $yil;
}
?>
<style>
.rapor-filtre {
    background:#fff;border:1px solid #e6edf5;border-radius:16px;
    padding:18px 20px;margin-bottom:20px;
    display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;
}
.rapor-filtre label { display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;margin-top:0; }
.rapor-grid { display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px; }
.rapor-kart { background:#fff;border:1px solid #e6edf5;border-radius:16px;overflow:hidden; }
.rapor-kart-baslik { padding:16px 20px 0;font-size:15px;font-weight:800;color:#0f172a;margin-bottom:12px; }
.rapor-kart-icerik { padding:0 20px 20px; overflow-x:auto; }
.rapor-tablo { width:100%;border-collapse:collapse;font-size:13px;min-width:400px; }
.rapor-tablo thead th { background:#f8fafc;color:#475569;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;padding:9px 12px;text-align:left;border-bottom:1px solid #e6edf5; }
.rapor-tablo tbody tr { border-bottom:1px solid #f1f5f9; }
.rapor-tablo tbody tr:last-child { border-bottom:0; }
.rapor-tablo tbody tr:hover { background:#f8fbff; }
.rapor-tablo td { padding:9px 12px;vertical-align:middle; }
.rapor-bos { text-align:center;padding:32px;color:#94a3b8;font-size:13px; }
.rapor-bar-wrap { display:flex;align-items:center;gap:8px; }
.rapor-bar { height:8px;border-radius:999px;flex-shrink:0; }
.alis-renk { color:#16a34a; }
.satis-renk { color:#2563eb; }
.borc-renk { color:#dc2626; }
.alacak-renk { color:#16a34a; }
@media(max-width:900px) { .rapor-grid { grid-template-columns:1fr; } }
@media(max-width:640px) {
    .rapor-filtre { flex-direction:column; }
    .rapor-filtre > div { width:100%; }
    .rapor-filtre .btn { width:100%; }
}
</style>

<!-- FİLTRE -->
<div class="rapor-filtre">
    <form method="GET" action="<?= e(url('raporlar')) ?>" style="display:contents;">
        <div>
            <label>Başlangıç</label>
            <input type="date" name="baslangic" value="<?= e($baslangic) ?>">
        </div>
        <div>
            <label>Bitiş</label>
            <input type="date" name="bitis" value="<?= e($bitis) ?>">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-ana">Filtrele</button>
            <a href="<?= e(url('raporlar')) ?>" class="btn btn-gri">Sıfırla</a>
        </div>
    </form>
</div>

<div class="rapor-grid">

    <!-- AYLIK ALIŞ/SATIŞ ÖZETİ -->
    <div class="rapor-kart">
        <div class="rapor-kart-baslik">Aylık Alış / Satış Özeti</div>
        <div class="rapor-kart-icerik">
            <?php if (empty($aylik_ozet)): ?>
                <div class="rapor-bos">Bu tarih aralığında veri yok.</div>
            <?php else: ?>
                <?php
                $maxTutar = max(array_map(fn($r) => max($r['alis'], $r['satis']), $aylik_ozet)) ?: 1;
                ?>
                <table class="rapor-tablo">
                    <thead>
                        <tr>
                            <th>Ay</th>
                            <th>Alış</th>
                            <th>Satış</th>
                            <th>Fark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aylik_ozet as $row): ?>
                        <tr>
                            <td style="font-weight:600;"><?= e(ayEtiket($row['ay'])) ?></td>
                            <td>
                                <div class="rapor-bar-wrap">
                                    <div class="rapor-bar" style="width:<?= round($row['alis'] / $maxTutar * 80) ?>px;background:#bbf7d0;"></div>
                                    <span class="alis-renk" style="font-weight:700;font-size:12px;"><?= raporPara($row['alis']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="rapor-bar-wrap">
                                    <div class="rapor-bar" style="width:<?= round($row['satis'] / $maxTutar * 80) ?>px;background:#bfdbfe;"></div>
                                    <span class="satis-renk" style="font-weight:700;font-size:12px;"><?= raporPara($row['satis']) ?></span>
                                </div>
                            </td>
                            <td style="font-weight:700;font-size:12px;<?= $row['satis'] >= $row['alis'] ? 'color:#2563eb;' : 'color:#dc2626;' ?>">
                                <?= raporPara($row['satis'] - $row['alis']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- EN ÇOK SATILAN ÜRÜNLER -->
    <div class="rapor-kart">
        <div class="rapor-kart-baslik">En Çok Satılan Ürünler</div>
        <div class="rapor-kart-icerik">
            <?php if (empty($en_cok_satilan)): ?>
                <div class="rapor-bos">Bu tarih aralığında satış yok.</div>
            <?php else: ?>
                <?php $maxMiktar = max(array_column($en_cok_satilan, 'toplam_miktar')) ?: 1; ?>
                <table class="rapor-tablo">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ürün</th>
                            <th style="text-align:right;">Miktar</th>
                            <th style="text-align:right;">Ciro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($en_cok_satilan as $i => $row): ?>
                        <tr>
                            <td style="color:#94a3b8;font-size:12px;"><?= $i + 1 ?></td>
                            <td>
                                <div style="font-weight:600;"><?= e($row['urun_adi']) ?></div>
                                <?php if ($row['stok_kodu']): ?>
                                <div style="font-size:11px;color:#94a3b8;font-family:monospace;"><?= e($row['stok_kodu']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <div class="rapor-bar-wrap" style="justify-content:flex-end;">
                                    <span style="font-weight:700;"><?= number_format((float)$row['toplam_miktar'], 2, ',', '.') ?></span>
                                </div>
                            </td>
                            <td style="text-align:right;font-weight:700;color:#2563eb;font-size:12px;"><?= raporPara((float)$row['toplam_ciro']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- CARİ ALIŞ/SATIŞ ÖZETİ -->
    <div class="rapor-kart">
        <div class="rapor-kart-baslik">Cari Alış / Satış Özeti</div>
        <div class="rapor-kart-icerik">
            <?php if (empty($cari_ozet)): ?>
                <div class="rapor-bos">Bu tarih aralığında veri yok.</div>
            <?php else: ?>
                <table class="rapor-tablo">
                    <thead>
                        <tr>
                            <th>Cari</th>
                            <th style="text-align:right;">Alış</th>
                            <th style="text-align:right;">Satış</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($cari_ozet, 0, 15) as $row): ?>
                        <tr>
                            <td style="font-weight:600;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($row['cari_adi']) ?>"><?= e($row['cari_adi']) ?></td>
                            <td style="text-align:right;font-size:12px;" class="alis-renk"><?= $row['alis'] > 0 ? raporPara($row['alis']) : '-' ?></td>
                            <td style="text-align:right;font-size:12px;" class="satis-renk"><?= $row['satis'] > 0 ? raporPara($row['satis']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- EN YÜKSEK BAKİYELİ CARİLER -->
    <div class="rapor-kart">
        <div class="rapor-kart-baslik">En Yüksek Bakiyeli Cariler</div>
        <div class="rapor-kart-icerik">
            <?php if (empty($en_yuksek_bakiye)): ?>
                <div class="rapor-bos">Bakiyeli cari bulunamadı.</div>
            <?php else: ?>
                <table class="rapor-tablo">
                    <thead>
                        <tr>
                            <th>Cari</th>
                            <th style="text-align:right;">Bakiye</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($en_yuksek_bakiye as $row):
                            $bakiye = (float) $row['bakiye'];
                        ?>
                        <tr>
                            <td style="font-weight:600;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($row['ad_soyad']) ?>"><?= e($row['ad_soyad']) ?></td>
                            <td style="text-align:right;font-weight:700;font-size:13px;<?= $bakiye > 0 ? 'color:#16a34a;' : 'color:#dc2626;' ?>">
                                <?= raporPara(abs($bakiye)) ?>
                            </td>
                            <td>
                                <?php if ($bakiye > 0): ?>
                                    <span class="badge badge-yesil" style="font-size:11px;">Alacak</span>
                                <?php else: ?>
                                    <span class="badge badge-kirmizi" style="font-size:11px;">Borç</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>
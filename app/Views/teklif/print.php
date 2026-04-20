<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($teklif['cari_adi'] ?? 'Teklif') . ' - ' . date('d.m.Y', strtotime((string) ($teklif['tarih'] ?? 'now')))) ?></title>
    <style>
        body {
    font-family: Arial, sans-serif;
    color: #333;
    margin: 0;
    padding: 18px 0;
    background: #efefef;
    font-size: 13px;
}

        .ekran-butonlari {
            max-width: 980px;
            margin: 0 auto 16px auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            background: #1d94e8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn-gri {
            background: #6b7280;
        }

        .sayfa {
    max-width: 920px;
    margin: 0 auto;
    background: #fff;
    padding: 28px 30px;
    box-sizing: border-box;
}

        .ust {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 28px;
        }

        .sol-logo {
            max-width: 320px;
        }

        .sol-logo img {
    max-width: 130px;
    max-height: 80px;
    display: block;
    margin-bottom: 8px;
}

        .sol-logo .firma-isim {
    font-size: 20px;
    line-height: 1.2;
    font-weight: bold;
    color: #1d94e8;
}

        .sag-ust {
    text-align: right;
    line-height: 1.55;
    font-size: 13px;
    color: #666;
}

.sag-ust .baslik {
    font-size: 18px;
    font-weight: bold;
    color: #444;
    margin-bottom: 6px;
    text-transform: uppercase;
}

        .mavi-cizgi {
            height: 4px;
            background: #1d94e8;
            margin: 24px 0 36px 0;
        }

        .musteri-kutusu {
            background: #f3f3f3;
            border-left: 6px solid #1d94e8;
            padding: 22px 24px;
            margin-bottom: 40px;
        }

        .musteri-kutusu .etiket {
    font-size: 15px;
    font-weight: bold;
    color: #444;
    margin-bottom: 6px;
}
        .musteri-kutusu .firma {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-bottom: 4px;
}

        .musteri-kutusu .eposta {
            font-size: 16px;
            color: #666;
        }

        table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    page-break-inside: auto;
}
thead {
    display: table-header-group;
}

tfoot {
    display: table-footer-group;
}

tr {
    page-break-inside: avoid;
    break-inside: avoid;
}

td, th {
    page-break-inside: avoid;
    break-inside: avoid;
}

        thead th {
    background: #1d94e8;
    color: #fff;
    font-size: 13px;
    padding: 10px 8px;
    border: 1px solid #d7d7d7;
    text-align: left;
}

        tbody td {
    background: #f5f5f5;
    border: 1px solid #d7d7d7;
    padding: 9px 8px;
    font-size: 13px;
    color: #444;
}

        tbody td strong {
            color: #333;
        }

        .sira {
            width: 52px;
            text-align: center;
        }

        .sag {
            text-align: right;
        }

        .toplamlar {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    margin-bottom: 32px;
    page-break-inside: avoid;
    break-inside: avoid;
}

        .toplam-satiri {
    min-width: 320px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #d7d7d7;
    background: #f8f8f8;
}

        .toplam-sol {
    padding: 12px 14px;
    font-size: 15px;
    font-weight: bold;
    color: #444;
}

        .toplam-sag {
    padding: 12px 14px;
    font-size: 18px;
    font-weight: bold;
    color: #1d94e8;
    border-left: 1px solid #d7d7d7;
    min-width: 110px;
    text-align: right;
}
        .sartlar-kutusu {
    background: #f3f3f3;
    border-left: 6px solid #1d94e8;
    padding: 20px 24px;
    margin-bottom: 40px;
    page-break-inside: avoid;
    break-inside: avoid;
}

        .sartlar-kutusu .baslik {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #444;
}

        .sartlar-kutusu .icerik {
    white-space: pre-line;
    line-height: 1.6;
    font-size: 13px;
    color: #555;
}

        .imza-alani {
    margin-top: 40px;
    padding-top: 32px;
    border-top: 2px dashed #ddd;
    display: flex;
    justify-content: space-between;
    gap: 60px;
    page-break-inside: avoid;
    break-inside: avoid;
}

        .imza-kutu {
    flex: 1;
    text-align: center;
    min-height: 140px;
}

        .imza-kutu .isim {
    font-size: 16px;
    font-weight: bold;
    color: #444;
    margin-bottom: 42px;
}

        .imza-kutu .alt {
    font-size: 13px;
    color: #888;
}

        @media print {
    @page {
        size: A4;
        margin: 14mm;
    }

    body {
        background: white;
        padding: 0;
    }

    .ekran-butonlari {
        display: none;
    }

    .sayfa {
        max-width: none;
        margin: 0;
        box-shadow: none;
        padding: 0;
    }

    .ust,
    .musteri-kutusu,
    .toplamlar,
    .sartlar-kutusu,
    .imza-alani {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }
}
    </style>
</head>
<body>
    <div class="ekran-butonlari">
        <button class="btn" onclick="window.print()">Yazdır / PDF Al</button>
        <a href="<?= e(url('teklifler')) ?>" class="btn btn-gri">Tekliflere Dön</a>
    </div>

    <div class="sayfa">
        <div class="ust">
            <div class="sol-logo">
                <?php if (!empty($ayarlar['logo_url'])): ?>
                    <img src="<?= e($ayarlar['logo_url']) ?>" alt="Logo">
                <?php endif; ?>

                <div class="firma-isim">
                    <?= nl2br(e($ayarlar['sirket_adi'] ?? '')) ?>
                </div>
            </div>

            <div class="sag-ust">
                <div class="baslik">Fiyat Teklifi</div>
                <div>Tarih: <?= e(date('d.m.Y', strtotime((string) ($teklif['tarih'] ?? 'now')))) ?></div>
                <div>E-posta: <?= e($ayarlar['eposta'] ?? '') ?></div>
                <div>Telefon: <?= e($ayarlar['telefon'] ?? '') ?></div>
                <div>Adres: <?= e($ayarlar['adres'] ?? '') ?></div>
                <?php if (!empty($ayarlar['vergi_no'])): ?>
                    <div>VKN: <?= e($ayarlar['vergi_no']) ?></div>
                <?php endif; ?>
                <?php if (!empty($ayarlar['vergi_dairesi'])): ?>
                    <div>Vergi Dairesi: <?= e($ayarlar['vergi_dairesi']) ?></div>
                <?php endif; ?>
                <div>İnternet Sitesi: <?= e($ayarlar['web_sitesi'] ?? '') ?></div>
            </div>
        </div>

        <div class="mavi-cizgi"></div>

        <div class="musteri-kutusu">
            <div class="etiket">FİRMA ADI:</div>
            <div class="firma"><?= e($teklif['cari_adi'] ?? '') ?></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="sira">Sıra</th>
                    <th>Ürün Adı</th>
                    <th>Marka</th>
                    <th>Miktar</th>
                    <th>Birim Fiyat</th>
                    <th>Tutar</th>
                    <th>Termin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kalemler as $index => $kalem): ?>
                    <?php
                    $pb = (string) ($kalem['para_birimi'] ?? 'TL');
                    $simge = $pb === 'USD' ? '$' : ($pb === 'EUR' ? '€' : '₺');
                    $satirToplam = (float) ($kalem['satir_toplam'] ?? (((float) ($kalem['miktar'] ?? 0)) * ((float) ($kalem['birim_fiyat'] ?? 0))));
                    ?>
                    <tr>
                        <td class="sira"><?= (int) $index + 1 ?></td>
                        <td><strong><?= e($kalem['urun_adi'] ?? '') ?></strong></td>
                        <td><?= e($kalem['marka'] ?? '') ?></td>
                        <td><?= number_format((float) ($kalem['miktar'] ?? 0), 1, ',', '.') ?></td>
                        <td class="sag"><?= number_format((float) ($kalem['birim_fiyat'] ?? 0), 2, ',', '.') . ' ' . e($simge) ?></td>
                        <td class="sag"><strong><?= number_format($satirToplam, 2, ',', '.') . ' ' . e($simge) ?></strong></td>
                        <td><?= e($kalem['termin'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="toplamlar">
    <?php if (!empty($toplamlar)): ?>
        <?php foreach ($toplamlar as $pb => $tutar): ?>
            <?php $simge = $pb === 'USD' ? '$' : ($pb === 'EUR' ? '€' : '₺'); ?>
            <div class="toplam-satiri">
                <div class="toplam-sol">GENEL TOPLAM (KDV Hariç)</div>
                <div class="toplam-sag"><?= number_format((float) $tutar, 2, ',', '.') . ' ' . e($simge) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php
        $pb = (string) ($teklif['para_birimi'] ?? 'TL');
        $simge = $pb === 'USD' ? '$' : ($pb === 'EUR' ? '€' : '₺');
        ?>
        <div class="toplam-satiri">
            <div class="toplam-sol">GENEL TOPLAM (KDV Hariç)</div>
            <div class="toplam-sag"><?= number_format((float) ($teklif['genel_toplam'] ?? 0), 2, ',', '.') . ' ' . e($simge) ?></div>
        </div>
    <?php endif; ?>
</div>

        <div class="sartlar-kutusu">
            <div class="baslik">Teklif Şartları:</div>
            <div class="icerik"><?= e($teklif['teklif_notlari'] ?? ($ayarlar['varsayilan_teklif_sartlari'] ?? '')) ?></div>
        </div>

        <div class="imza-alani">
            <div class="imza-kutu">
                <div class="isim"><?= e($ayarlar['sirket_adi'] ?? '') ?></div>
                <div class="alt">Kaşe ve İmza</div>
            </div>

            <div class="imza-kutu">
                <div class="isim"><?= e($teklif['cari_adi'] ?? '') ?></div>
                <div class="alt">Tarih / Kaşe / İmza</div>
            </div>
        </div>
    </div>
</body>
</html>
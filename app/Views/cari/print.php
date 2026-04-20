<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($cari['ad_soyad'] ?? 'Cari') . '_Hesap_Ekstresi') ?></title>
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
            max-width: 980px;
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
            margin-bottom: 24px;
        }

        .sol-logo {
            max-width: 360px;
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

        .sag-ust strong {
            color: #444;
        }

        .mavi-cizgi {
            height: 4px;
            background: #1d94e8;
            margin: 22px 0 28px 0;
        }

        .musteri-kutusu {
            background: #f3f3f3;
            border-left: 6px solid #1d94e8;
            padding: 18px 22px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: center;
        }

        .musteri-sol .baslik {
            font-size: 15px;
            font-weight: bold;
            color: #444;
            margin-bottom: 6px;
        }

        .musteri-sol .firma {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .musteri-sol .iletisim {
            font-size: 13px;
            color: #666;
        }

        .musteri-sag {
            font-size: 14px;
            color: #555;
            text-align: right;
            min-width: 180px;
        }

        .baslik2 {
            font-size: 18px;
            font-weight: bold;
            color: #444;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        thead th {
            background: #f3f3f3;
            color: #444;
            font-size: 13px;
            padding: 12px 10px;
            border: 1px solid #d7d7d7;
            text-align: left;
        }

        tbody td {
            background: #fafafa;
            border: 1px solid #d7d7d7;
            padding: 12px 10px;
            font-size: 13px;
            color: #444;
        }

        .sag {
            text-align: right;
            font-weight: bold;
        }

        .bakiye-kutusu {
            margin-top: 16px;
            background: #f8f8f8;
            border: 1px solid #d7d7d7;
            border-radius: 10px;
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .bakiye-sol {
            font-size: 15px;
            color: #666;
            font-style: italic;
        }

        .bakiye-sag {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
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
        }
    </style>
</head>
<body>
    <div class="ekran-butonlari">
        <button class="btn" onclick="window.print()">Yazdır / PDF Al</button>
        <a href="<?= e(url('cariler')) ?>" class="btn btn-gri">Carilere Dön</a>
    </div>

    <div class="sayfa">
        <div class="ust">
            <div class="sol-logo">
                <?php if (!empty($ayarlar['logo_url'])): ?>
                    <img src="<?= e($ayarlar['logo_url']) ?>" alt="Logo">
                <?php endif; ?>

                <div class="firma-isim"><?= e($ayarlar['sirket_adi'] ?? '') ?></div>
            </div>

            <div class="sag-ust">
                <div><strong>E-Posta:</strong> <?= e($ayarlar['eposta'] ?? '') ?></div>
                <div><strong>Telefon:</strong> <?= e($ayarlar['telefon'] ?? '') ?></div>
                <div><strong>İnternet Sitesi:</strong> <?= e($ayarlar['web_sitesi'] ?? '') ?></div>
                <div><strong>Adres:</strong> <?= e($ayarlar['adres'] ?? '') ?></div>
                <?php if (!empty($ayarlar['vergi_no'])): ?>
                    <div><strong>VKN:</strong> <?= e($ayarlar['vergi_no']) ?></div>
                <?php endif; ?>
                <?php if (!empty($ayarlar['vergi_dairesi'])): ?>
                    <div><strong>Vergi Dairesi:</strong> <?= e($ayarlar['vergi_dairesi']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mavi-cizgi"></div>

        <div class="musteri-kutusu">
            <div class="musteri-sol">
                <div class="baslik">SAYIN:</div>
                <div class="firma"><?= e($cari['ad_soyad'] ?? '') ?></div>
                <div class="iletisim">
                    <strong>İletişim:</strong>
                    <?= e($cari['eposta'] ?? ($cari['telefon'] ?? '-')) ?>
                </div>
            </div>

            <div class="musteri-sag">
                <strong>Belge Tarihi:</strong> <?= e(date('d.m.Y')) ?>
            </div>
        </div>

        <div class="baslik2">Hesap Hareketleri</div>

        <table>
            <thead>
    <tr>
        <th style="width: 140px;">Tarih</th>
        <th style="width: 160px;">İşlem Tipi</th>
        <th>Açıklama</th>
        <th style="width: 170px;" class="sag">Tutar (₺)</th>
    </tr>
</thead>
            <tbody>
                <?php foreach ($hareketler as $hareket): ?>
    <tr>
        <td><?= e(date('d.m.Y', strtotime((string) ($hareket['tarih'] ?? 'now')))) ?></td>
        <td><?= e($print_service->formatIslemTipi((string) ($hareket['islem_tipi'] ?? ''))) ?></td>
        <td><?= e($hareket['aciklama'] ?? '') ?></td>
        <td class="sag"><?= number_format((float) ($hareket['tutar'] ?? 0), 2, ',', '.') ?></td>
    </tr>
<?php endforeach; ?>

<?php if (empty($hareketler)): ?>
    <tr>
        <td colspan="4">Bu cariye ait hareket bulunmuyor.</td>
    </tr>
<?php endif; ?>
            </tbody>
        </table>

        <div class="bakiye-kutusu">
            <div class="bakiye-sol"><?= e($bakiye_durumu['metin'] ?? '') ?></div>
            <div class="bakiye-sag" style="color: <?= e($bakiye_durumu['renk'] ?? '#444') ?>;">
                Güncel Bakiye:
                <?= number_format(abs((float) ($cari['bakiye'] ?? 0)), 2, ',', '.') ?> ₺
                <?php if (!empty($bakiye_durumu['etiket'])): ?>
                    (<?= e($bakiye_durumu['etiket']) ?>)
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
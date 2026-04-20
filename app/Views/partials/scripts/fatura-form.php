<?php
$prefix = $fatura_js_prefix ?? 'fatura';
$itemClass = $fatura_item_class ?? 'fatura-kalem-item';
$rowClass = $fatura_row_class ?? 'fatura-kalem-row';
$handleClass = $fatura_handle_class ?? 'fatura-drag-handle';
$contentClass = $fatura_content_class ?? 'fatura-kalem-icerik';
$deleteButtonClass = $fatura_delete_button_class ?? 'fatura-kalem-sil';
$editFormId = $fatura_edit_form_id ?? 'editFaturaForm';
$editModalId = $fatura_edit_modal_id ?? 'editFaturaModal';
$editContainerId = $fatura_edit_container_id ?? 'duzenle-kalemler';
$parseErrorMessage = $fatura_parse_error_message ?? 'Fatura verisi çözümlenemedi:';

$prefixJs = json_encode($prefix, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$itemClassJs = json_encode($itemClass, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$rowClassJs = json_encode($rowClass, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$handleClassJs = json_encode($handleClass, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$contentClassJs = json_encode($contentClass, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$deleteButtonClassJs = json_encode($deleteButtonClass, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$editFormIdJs = json_encode($editFormId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$editModalIdJs = json_encode($editModalId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$editContainerIdJs = json_encode($editContainerId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$parseErrorMessageJs = json_encode($parseErrorMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<script>
    const stokListesiData = <?= json_encode(array_values(array_map(static function ($stok) {
        return [
            'id' => $stok['id'] ?? null,
            'stok_kodu' => $stok['stok_kodu'] ?? '',
            'urun_adi' => $stok['urun_adi'] ?? '',
            'birim' => $stok['birim'] ?? 'Adet',
        ];
    }, $stoklar ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    
    const cariListesiData = <?= json_encode(array_values(array_map(static function ($cari) {
    return [
        'id' => $cari['id'] ?? null,
        'ad_soyad' => $cari['ad_soyad'] ?? '',
        'varsayilan_vade_gun' => (int) ($cari['varsayilan_vade_gun'] ?? 0),
    ];
}, $cariler ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const faturaFormConfig = {
        prefix: <?= $prefixJs ?>,
        itemClass: <?= $itemClassJs ?>,
        rowClass: <?= $rowClassJs ?>,
        handleClass: <?= $handleClassJs ?>,
        contentClass: <?= $contentClassJs ?>,
        deleteButtonClass: <?= $deleteButtonClassJs ?>,
        editFormId: <?= $editFormIdJs ?>,
        editModalId: <?= $editModalIdJs ?>,
        editContainerId: <?= $editContainerIdJs ?>,
        parseErrorMessage: <?= $parseErrorMessageJs ?>
    };

    function faturaEscapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return String(text).replace(/[&<>"']/g, function (char) {
            return map[char];
        });
    }

    function faturaKalemHtml(kalem = {}) {
        const stokKodu = kalem.stok_kodu ?? '';
        const urunAdi = kalem.urun_adi ?? '';
        const miktar = kalem.miktar ?? '';
        const birimFiyat = kalem.birim_fiyat ?? '';
        const kdvOrani = kalem.kdv_orani ?? 20;
        const sira = kalem.sira ?? 1;
        const silFonksiyonu = `${faturaFormConfig.prefix}KalemSil`;

        return `
            <div class="kalem-blok sortable-item ${faturaFormConfig.itemClass}" data-sort-item="" draggable="true">
                <div class="${faturaFormConfig.rowClass}">
                    <button type="button" class="drag-handle ${faturaFormConfig.handleClass}" title="Sürükleyerek taşı">⋮⋮</button>

                    <div class="${faturaFormConfig.contentClass}">
                        <div class="kalem-grid-fatura">
                            <input type="text" name="stok_kodu[]" placeholder="Stok kodu" list="stokKodlariListesi" class="stok-kodu-autocomplete" value="${faturaEscapeHtml(String(stokKodu))}">
                            <input type="text" name="urun_adi[]" placeholder="Ürün adı" list="stokUrunleriListesi" class="stok-adi-autocomplete" value="${faturaEscapeHtml(String(urunAdi))}">
                            <input type="number" step="0.0001" name="miktar[]" placeholder="Miktar" value="${faturaEscapeHtml(String(miktar))}">
                            <input type="number" step="0.0001" name="birim_fiyat[]" placeholder="Birim fiyat" value="${faturaEscapeHtml(String(birimFiyat))}">
                            <input type="number" name="kdv_orani[]" placeholder="KDV" value="${faturaEscapeHtml(String(kdvOrani))}">
                        </div>

                        <input type="hidden" name="kalem_sira[]" value="${faturaEscapeHtml(String(sira))}" data-sort-order-input>
                    </div>

                    <button type="button" class="btn btn-kirmizi ${faturaFormConfig.deleteButtonClass}" onclick="${silFonksiyonu}(this)">Sil</button>
<button type="button" class="fiyat-gecmis-btn" title="Fiyat Geçmişi" onclick="fiyatGecmisiAc(this)" style="width:32px;height:32px;min-width:32px;padding:0;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">📈</button>
                </div>
            </div>
        `;
    }

    function faturaYeniKalemEkle(containerId, kalemData = null) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = faturaKalemHtml(kalemData || {});

        const yeniKalem = wrapper.firstElementChild;
        container.appendChild(yeniKalem);

        if (window.appDragSort) {
            window.appDragSort.init(container);
            window.appDragSort.update(container);
        }

        faturaAutocompleteAlanlariniBagla(yeniKalem);
    }

    function faturaKalemSil(button) {
        const item = button.closest('[data-sort-item]');
        const container = item ? item.parentElement : null;

        if (!item || !container) return;

        item.remove();

        if (!container.querySelector('[data-sort-item]')) {
            faturaYeniKalemEkle(container.id);
            return;
        }

        if (window.appDragSort) {
            window.appDragSort.update(container);
        }
    }

    function faturaDuzenleAc(button) {
        const rawData = button.getAttribute('data-fatura');
        if (!rawData) return;

        let fatura = null;

        try {
            fatura = JSON.parse(rawData);
        } catch (error) {
            console.error(faturaFormConfig.parseErrorMessage, error);
            return;
        }

        const form = document.getElementById(faturaFormConfig.editFormId);
        if (!form) return;

        form.querySelector('[name="fatura_id"]').value = fatura.id ?? '';
        form.querySelector('[name="cari_adi"]').value = fatura.cari_adi ?? '';
        form.querySelector('[name="fatura_no"]').value = fatura.fatura_no ?? '';
        form.querySelector('[name="tarih"]').value = fatura.tarih ?? '';
        
        
        const editVadeInput = form.querySelector('[name="vade_tarihi"]');
if (editVadeInput) {
    editVadeInput.value = fatura.vade_tarihi ?? '';
    editVadeInput.dataset.manualOverride = 'true';
}

        const kalemContainer = document.getElementById(faturaFormConfig.editContainerId);
        if (!kalemContainer) return;

        kalemContainer.innerHTML = '';

        const kalemler = Array.isArray(fatura.kalemler) ? fatura.kalemler : [];

        if (kalemler.length) {
            kalemler.forEach(function (kalem, index) {
                faturaYeniKalemEkle(faturaFormConfig.editContainerId, {
                    stok_kodu: kalem.stok_kodu ?? '',
                    urun_adi: kalem.urun_adi ?? '',
                    miktar: kalem.miktar ?? '',
                    birim_fiyat: kalem.birim_fiyat ?? '',
                    kdv_orani: kalem.kdv_orani ?? 20,
                    sira: index + 1
                });
            });
        } else {
            faturaYeniKalemEkle(faturaFormConfig.editContainerId);
        }

        if (window.appDragSort) {
            window.appDragSort.init(kalemContainer);
            window.appDragSort.update(kalemContainer);
        }
        
        faturaVadeAlanlariniBagla(form);

        if (window.appModal) {
            window.appModal.open(faturaFormConfig.editModalId);
        }
    }

    function faturaAutocompleteAlanlariniBagla(scope) {
        const root = scope || document;

        root.querySelectorAll('.stok-kodu-autocomplete').forEach(function (input) {
            if (input.dataset.autocompleteReady === 'true') return;
            input.dataset.autocompleteReady = 'true';

            input.addEventListener('change', function () {
                faturaStokAlanlariniEsle(this, 'kod');
            });

            input.addEventListener('blur', function () {
                faturaStokAlanlariniEsle(this, 'kod');
            });
        });

        root.querySelectorAll('.stok-adi-autocomplete').forEach(function (input) {
            if (input.dataset.autocompleteReady === 'true') return;
            input.dataset.autocompleteReady = 'true';

            input.addEventListener('change', function () {
                faturaStokAlanlariniEsle(this, 'ad');
            });

            input.addEventListener('blur', function () {
                faturaStokAlanlariniEsle(this, 'ad');
            });
        });
    }

    function faturaStokAlanlariniEsle(input, tip) {
        const row = input.closest('.kalem-blok');
        if (!row) return;

        const stokKoduInput = row.querySelector('.stok-kodu-autocomplete');
        const urunAdiInput = row.querySelector('.stok-adi-autocomplete');

        if (!stokKoduInput || !urunAdiInput) return;

        let kayit = null;

        if (tip === 'kod') {
            const kod = (stokKoduInput.value || '').trim().toLowerCase();
            kayit = stokListesiData.find(function (item) {
                return String(item.stok_kodu || '').trim().toLowerCase() === kod;
            });
        } else {
            const ad = (urunAdiInput.value || '').trim().toLowerCase();
            kayit = stokListesiData.find(function (item) {
                return String(item.urun_adi || '').trim().toLowerCase() === ad;
            });
        }

        if (!kayit) return;

        stokKoduInput.value = kayit.stok_kodu || '';
        urunAdiInput.value = kayit.urun_adi || '';
    }
    
    function faturaCariBulByAd(adSoyad) {
    const aranan = String(adSoyad || '').trim().toLowerCase();

    return cariListesiData.find(function (item) {
        return String(item.ad_soyad || '').trim().toLowerCase() === aranan;
    }) || null;
}

function faturaVadeOtomatikDoldur(form) {
    if (!form) return;

    const cariInput = form.querySelector('[name="cari_adi"]');
    const tarihInput = form.querySelector('[name="tarih"]');
    const vadeInput = form.querySelector('[name="vade_tarihi"]');

    if (!cariInput || !tarihInput || !vadeInput) return;

    if (vadeInput.dataset.manualOverride === 'true') return;

    const tarih = (tarihInput.value || '').trim();
    if (!tarih) return;

    const cari = faturaCariBulByAd(cariInput.value || '');
    const vadeGun = Math.max(0, Number(cari?.varsayilan_vade_gun || 0));

    const baseDate = new Date(tarih + 'T00:00:00');
    if (Number.isNaN(baseDate.getTime())) return;

    baseDate.setDate(baseDate.getDate() + vadeGun);

    const yil = baseDate.getFullYear();
    const ay = String(baseDate.getMonth() + 1).padStart(2, '0');
    const gun = String(baseDate.getDate()).padStart(2, '0');

    vadeInput.value = `${yil}-${ay}-${gun}`;
}

function faturaVadeAlanlariniBagla(form) {
    if (!form || form.dataset.vadeBindingReady === 'true') return;
    form.dataset.vadeBindingReady = 'true';

    const cariInput = form.querySelector('[name="cari_adi"]');
    const tarihInput = form.querySelector('[name="tarih"]');
    const vadeInput = form.querySelector('[name="vade_tarihi"]');

    if (!cariInput || !tarihInput || !vadeInput) return;

    const otomatikHesapla = function () {
        faturaVadeOtomatikDoldur(form);
    };

    cariInput.addEventListener('change', otomatikHesapla);
    cariInput.addEventListener('blur', otomatikHesapla);
    tarihInput.addEventListener('change', otomatikHesapla);

    vadeInput.addEventListener('input', function () {
        this.dataset.manualOverride = 'true';
    });

    if (!vadeInput.value) {
        vadeInput.dataset.manualOverride = 'false';
        otomatikHesapla();
    }
}

    function faturaInit() {
    ['yeni-kalemler', 'duzenle-kalemler'].forEach(function (containerId) {
        const container = document.getElementById(containerId);
        if (!container || !window.appDragSort) return;

        window.appDragSort.init(container);
        window.appDragSort.update(container);
    });

    faturaAutocompleteAlanlariniBagla(document);
    
    faturaVadeAlanlariniBagla(document.getElementById('create' + faturaFormConfig.prefix.charAt(0).toUpperCase() + faturaFormConfig.prefix.slice(1) + 'Form'));
faturaVadeAlanlariniBagla(document.getElementById(faturaFormConfig.editFormId));

    const params = new URLSearchParams(window.location.search);
    const duzenleId = params.get('duzenle');

    if (!duzenleId) {
        return;
    }

    const satir = document.getElementById(`fatura-${duzenleId}`);
    if (!satir) {
        return;
    }

    const duzenleButonu = satir.querySelector('[data-fatura]');
    if (!duzenleButonu) {
        return;
    }

    faturaDuzenleAc(duzenleButonu);

    const yeniUrl = new URL(window.location.href);
    yeniUrl.searchParams.delete('duzenle');
    window.history.replaceState({}, document.title, yeniUrl.toString());
}

    window[`${faturaFormConfig.prefix}KalemSil`] = faturaKalemSil;
    window[`${faturaFormConfig.prefix}FaturaDuzenleAc`] = faturaDuzenleAc;
    window[`yeni${faturaFormConfig.prefix.charAt(0).toUpperCase() + faturaFormConfig.prefix.slice(1)}KalemEkle`] = faturaYeniKalemEkle;

    // Fiyat geçmişi popup
var _fgPopup = null;

function fiyatGecmisiAc(btn) {
    var row = btn.closest('.kalem-blok');
    if (!row) return;

    var kodInput = row.querySelector('input[name="stok_kodu[]"]');
    var adInput  = row.querySelector('input[name="urun_adi[]"]');
    var stokKodu = kodInput ? kodInput.value.trim() : '';
    var urunAdi  = adInput  ? adInput.value.trim()  : '';

    if (!stokKodu && !urunAdi) return;

    if (_fgPopup) _fgPopup.remove();

    var popup = document.createElement('div');
    popup.id = 'fiyatGecmisPopup';
    popup.style.cssText = [
        'position:fixed',
        'z-index:99999',
        'background:#fff',
        'border:1px solid #e2e8f0',
        'border-radius:14px',
        'box-shadow:0 12px 40px rgba(15,23,42,.18)',
        'width:480px',
        'max-width:94vw',
        'max-height:360px',
        'overflow:hidden',
        'display:flex',
        'flex-direction:column',
        'font-size:13px',
    ].join(';');

    popup.innerHTML = [
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">',
            '<span style="font-weight:700;color:#0f172a;">Fiyat Geçmişi — <span style="color:#64748b;font-weight:600;">' + (stokKodu || urunAdi) + '</span></span>',
            '<button onclick="document.getElementById(\'fiyatGecmisPopup\').remove()" style="border:0;background:#f1f5f9;width:28px;height:28px;border-radius:999px;cursor:pointer;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>',
        '</div>',
        '<div id="fgIcerik" style="overflow-y:auto;flex:1;padding:12px 16px;">',
            '<div style="color:#94a3b8;text-align:center;padding:20px;">Yükleniyor…</div>',
        '</div>',
    ].join('');

    document.body.appendChild(popup);
    _fgPopup = popup;

    // Konumlandır
    var rect = btn.getBoundingClientRect();
    var top  = rect.bottom + 8;
    var left = rect.left;
    if (left + 480 > window.innerWidth) left = window.innerWidth - 490;
    if (top + 360 > window.innerHeight) top = rect.top - 368;
    popup.style.top  = top  + 'px';
    popup.style.left = left + 'px';

    // Fetch
    var url = '<?= e(url('stoklar/fiyat-gecmisi')) ?>?stok_kodu=' + encodeURIComponent(stokKodu) + '&urun_adi=' + encodeURIComponent(urunAdi);

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var icerik = document.getElementById('fgIcerik');
            if (!icerik) return;

            if (!data.ok || !data.satirlar || !data.satirlar.length) {
                icerik.innerHTML = '<div style="color:#94a3b8;text-align:center;padding:20px;">Kayıt bulunamadı.</div>';
                return;
            }

            var html = '<table style="width:100%;border-collapse:collapse;">';
            html += '<thead><tr style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">';
            html += '<th style="padding:6px 8px;text-align:left;border-bottom:1px solid #f1f5f9;">Tarih</th>';
            html += '<th style="padding:6px 8px;text-align:left;border-bottom:1px solid #f1f5f9;">İşlem</th>';
            html += '<th style="padding:6px 8px;text-align:left;border-bottom:1px solid #f1f5f9;">Cari</th>';
            html += '<th style="padding:6px 8px;text-align:right;border-bottom:1px solid #f1f5f9;">Miktar</th>';
            html += '<th style="padding:6px 8px;text-align:right;border-bottom:1px solid #f1f5f9;">Birim Fiyat</th>';
            html += '</tr></thead><tbody>';

            data.satirlar.forEach(function(s) {
                var tarih = s.tarih ? s.tarih.substring(0, 10).split('-').reverse().join('.') : '-';
                var tip   = s.tip === 'alis' ? '<span style="color:#16a34a;font-weight:700;">Alış</span>' : '<span style="color:#dc2626;font-weight:700;">Satış</span>';
                var fiyat = parseFloat(s.birim_fiyat || 0).toLocaleString('tr-TR', {minimumFractionDigits:2, maximumFractionDigits:4});
                html += '<tr style="border-bottom:1px solid #f8fafc;">';
                html += '<td style="padding:7px 8px;white-space:nowrap;">' + tarih + '</td>';
                html += '<td style="padding:7px 8px;">' + tip + '</td>';
                html += '<td style="padding:7px 8px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (s.cari_adi || '-') + '</td>';
                html += '<td style="padding:7px 8px;text-align:right;">' + parseFloat(s.miktar || 0).toLocaleString('tr-TR', {maximumFractionDigits:4}) + '</td>';
                html += '<td style="padding:7px 8px;text-align:right;font-weight:700;color:#0f172a;">' + fiyat + ' ₺</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            icerik.innerHTML = html;
        })
        .catch(function() {
            var icerik = document.getElementById('fgIcerik');
            if (icerik) icerik.innerHTML = '<div style="color:#b91c1c;text-align:center;padding:20px;">Veri alınamadı.</div>';
        });
}

document.addEventListener('click', function(e) {
    if (_fgPopup && !_fgPopup.contains(e.target) && !e.target.classList.contains('fiyat-gecmis-btn')) {
        _fgPopup.remove();
        _fgPopup = null;
    }
});
    document.addEventListener('DOMContentLoaded', faturaInit);
</script>
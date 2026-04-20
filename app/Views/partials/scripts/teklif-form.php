<script>
    function teklifEscapeHtml(text) {
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

    function teklifKalemHtml(kalem = {}) {
    const urunAdi = kalem.urun_adi ?? '';
    const marka = kalem.marka ?? '';
    const miktar = kalem.miktar ?? '';
    const birimFiyat = kalem.birim_fiyat ?? '';
    const satirToplam = kalem.satir_toplam ?? '';
    const termin = kalem.termin ?? '';
    const paraBirimi = kalem.para_birimi ?? 'TL';
    const sira = kalem.sira ?? 1;

    return `
        <div class="kalem-blok sortable-item teklif-kalem-item" data-sort-item="" draggable="true">
            <div class="teklif-kalem-row">
                <button type="button" class="drag-handle teklif-drag-handle" title="Sürükleyerek taşı">⋮⋮</button>

                <div class="teklif-kalem-icerik">
                    <div class="kalem-grid-teklif">
                        <input type="text" name="urun_adi[]" placeholder="Ürün adı" value="${teklifEscapeHtml(String(urunAdi))}">
                        <input type="text" name="marka[]" placeholder="Marka" value="${teklifEscapeHtml(String(marka))}">
                        <input type="number" step="0.0001" name="miktar[]" placeholder="Miktar" value="${teklifEscapeHtml(String(miktar))}" oninput="teklifSatirHesapla(this)">
                        <input type="number" step="0.0001" name="birim_fiyat[]" placeholder="Birim fiyat" value="${teklifEscapeHtml(String(birimFiyat))}" oninput="teklifBirimFiyatDegisti(this)">
                        <input type="number" step="0.0001" name="satir_toplam[]" placeholder="Toplam fiyat" value="${teklifEscapeHtml(String(satirToplam))}" oninput="teklifSatirToplamiElleDegisti(this)">
                        <input type="text" name="termin[]" placeholder="Termin" value="${teklifEscapeHtml(String(termin))}">
                        <select name="kalem_para_birimi[]">
                            <option value="TL" ${paraBirimi === 'TL' ? 'selected' : ''}>₺</option>
                            <option value="USD" ${paraBirimi === 'USD' ? 'selected' : ''}>$</option>
                            <option value="EUR" ${paraBirimi === 'EUR' ? 'selected' : ''}>€</option>
                        </select>
                    </div>

                    <input type="hidden" name="kalem_sira[]" value="${teklifEscapeHtml(String(sira))}" data-sort-order-input>
                </div>

                <button type="button" class="btn btn-kirmizi teklif-kalem-sil" onclick="teklifKalemSil(this)">
                    Sil
                </button>
            </div>
        </div>
    `;
}

function teklifSatirHesapla(input) {
    const row = input.closest('.kalem-grid-teklif');
    if (!row) return;

    const miktarInput = row.querySelector('[name="miktar[]"]');
    const birimFiyatInput = row.querySelector('[name="birim_fiyat[]"]');
    const satirToplamInput = row.querySelector('[name="satir_toplam[]"]');

    if (!miktarInput || !birimFiyatInput || !satirToplamInput) return;

    const miktar = parseFloat(miktarInput.value || '0');
    const birimFiyatRaw = (birimFiyatInput.value || '').trim();

    if (birimFiyatRaw === '') {
        return;
    }

    const birimFiyat = parseFloat(birimFiyatRaw || '0');
    satirToplamInput.value = (miktar * birimFiyat).toFixed(4);
}

function teklifBirimFiyatDegisti(input) {
    teklifSatirHesapla(input);
}

function teklifSatirToplamiElleDegisti(input) {
    const row = input.closest('.kalem-grid-teklif');
    if (!row) return;

    const birimFiyatInput = row.querySelector('[name="birim_fiyat[]"]');
    if (!birimFiyatInput) return;

    if ((birimFiyatInput.value || '').trim() === '') {
        return;
    }
}

    function teklifAutocompleteAlanlariniBagla(scope) {
        const root = scope || document;

        root.querySelectorAll('.stok-adi-autocomplete').forEach(function (input) {
            if (input.dataset.autocompleteReady === 'true') return;
            input.dataset.autocompleteReady = 'true';
        });
    }

    function yeniKalemEkle(containerId, kalemData = null) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = teklifKalemHtml(kalemData || {});

        const yeniKalem = wrapper.firstElementChild;
        container.appendChild(yeniKalem);

        if (window.appDragSort) {
            window.appDragSort.init(container);
            window.appDragSort.update(container);
        }

        teklifAutocompleteAlanlariniBagla(yeniKalem);
    }

    function teklifKalemSil(button) {
        const item = button.closest('[data-sort-item]');
        const container = item ? item.parentElement : null;

        if (!item || !container) return;

        item.remove();

        if (!container.querySelector('[data-sort-item]')) {
            yeniKalemEkle(container.id);
            return;
        }

        if (window.appDragSort) {
            window.appDragSort.update(container);
        }
    }

    function teklifDuzenleFormunuDoldur(teklif) {
        const form = document.getElementById('editTeklifForm');
        if (!form) return;

        form.querySelector('[name="teklif_id"]').value = teklif.id ?? '';
        form.querySelector('[name="cari_adi"]').value = teklif.cari_adi ?? '';
        form.querySelector('[name="teklif_no"]').value = teklif.teklif_no ?? '';
        form.querySelector('[name="tarih"]').value = teklif.tarih ?? '';
        form.querySelector('[name="teklif_notlari"]').value = teklif.teklif_notlari ?? '';

        const kalemContainer = document.getElementById('duzenle-kalemler');
        if (!kalemContainer) return;

        kalemContainer.innerHTML = '';

        const kalemler = Array.isArray(teklif.kalemler) ? teklif.kalemler : [];

        if (kalemler.length) {
            kalemler.forEach(function (kalem, index) {
                yeniKalemEkle('duzenle-kalemler', {
    urun_adi: kalem.urun_adi ?? '',
    marka: kalem.marka ?? '',
    miktar: kalem.miktar ?? '',
    birim_fiyat: kalem.birim_fiyat ?? '',
    satir_toplam: kalem.satir_toplam ?? '',
    termin: kalem.termin ?? '',
    para_birimi: kalem.para_birimi ?? 'TL',
    sira: index + 1
});
            });
        } else {
            yeniKalemEkle('duzenle-kalemler');
        }

        if (window.appDragSort) {
            window.appDragSort.init(kalemContainer);
            window.appDragSort.update(kalemContainer);
        }
    }

    function teklifDuzenleAc(button) {
        const rawData = button.getAttribute('data-teklif');
        if (!rawData) return;

        let teklif = null;

        try {
            teklif = JSON.parse(rawData);
        } catch (error) {
            console.error('Teklif verisi çözümlenemedi:', error);
            return;
        }

        teklifDuzenleFormunuDoldur(teklif);

        if (window.appModal) {
            window.appModal.open('editTeklifModal');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['yeni-kalemler', 'duzenle-kalemler'].forEach(function (containerId) {
            const container = document.getElementById(containerId);
            if (!container || !window.appDragSort) return;

            window.appDragSort.init(container);
            window.appDragSort.update(container);
        });

        teklifAutocompleteAlanlariniBagla(document);
    });
</script>
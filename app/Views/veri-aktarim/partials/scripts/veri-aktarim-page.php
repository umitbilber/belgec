<script>
    function cariEscapeHtml(text) {
        var map = {
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

    function cariWolvoxFormatMoney(value) {
        return Number(value || 0).toLocaleString('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' TL';
    }

    function veriAktarimWolvoxAc() {
        var cariIdInput = document.getElementById('veriAktarimCariId');
if (!cariIdInput || !cariIdInput.value) {
    alert('Lütfen önce bir cari seçin.');
    return;
}

var cari = (window.veriAktarimCariler || []).find(function (item) {
    return Number(item.id) === Number(cariIdInput.value);
}) || null;

if (!cari) {
    alert('Seçilen cari bulunamadı.');
    return;
}

        var form = document.getElementById('cariWolvoxImportForm');
        if (!form) return;

        var importCariInput = form.querySelector('[name="cari_id"]');
        if (importCariInput) {
            importCariInput.value = cari.id ?? '';
        }

        var executeForm = document.getElementById('cariWolvoxExecuteForm');
        if (executeForm) {
            var executeCariInput = executeForm.querySelector('[name="cari_id"]');
            if (executeCariInput) {
                executeCariInput.value = cari.id ?? '';
            }
        }

        var dosyaInput = form.querySelector('[name="ekstre_dosyasi"]');
        if (dosyaInput) {
            dosyaInput.value = '';
        }

        var unvanEl = document.querySelector('[data-cari-wolvox-unvan]');
        if (unvanEl) {
            unvanEl.textContent = cari.ad_soyad ?? '-';
        }

        if (window.appModal) {
            window.appModal.open('cariWolvoxImportModal');
        }
    }

    function cariWolvoxRenderPreview(preview) {
        var previewWrap = document.getElementById('cariWolvoxPreviewWrap');
        var previewBody = document.getElementById('cariWolvoxPreviewBody');

        if (!previewWrap || !previewBody) return;

        if (!preview || !Array.isArray(preview.satirlar)) {
            previewWrap.style.display = 'none';
            previewBody.innerHTML = '<tr><td colspan="5">Önizleme verisi bulunamadı.</td></tr>';
            return;
        }

        var dosyaEl = document.getElementById('wolvoxPreviewDosya');
        var satisEl = document.getElementById('wolvoxPreviewSatis');
        var tahsilatEl = document.getElementById('wolvoxPreviewTahsilat');
        var belirsizEl = document.getElementById('wolvoxPreviewBelirsiz');
        var bakiyeEl = document.getElementById('wolvoxPreviewBakiye');

        if (dosyaEl) dosyaEl.textContent = preview.dosya_adi ?? '-';
        if (satisEl) satisEl.textContent = String(preview.ozet?.satis_sayisi ?? 0);
        if (tahsilatEl) tahsilatEl.textContent = String(preview.ozet?.tahsilat_sayisi ?? 0);
        if (belirsizEl) belirsizEl.textContent = String(preview.ozet?.belirsiz_sayisi ?? 0);
        if (bakiyeEl) bakiyeEl.textContent = cariWolvoxFormatMoney(preview.ozet?.beklenen_bakiye ?? 0);

        var rows = Array.isArray(preview.satirlar) ? preview.satirlar.slice(0, 30) : [];

        if (!rows.length) {
            previewBody.innerHTML = '<tr><td colspan="5">Önizleme satırı bulunamadı.</td></tr>';
            previewWrap.style.display = 'block';
            return;
        }

        previewBody.innerHTML = rows.map(function (row) {
            return `
                <tr>
                    <td>${cariEscapeHtml(String(row.satir_no ?? ''))}</td>
                    <td>${cariEscapeHtml(String(row.tarih ?? '-'))}</td>
                    <td>${cariEscapeHtml(cariWolvoxFormatMoney(row.borc ?? 0))}</td>
                    <td>${cariEscapeHtml(cariWolvoxFormatMoney(row.alacak ?? 0))}</td>
                    <td>${cariEscapeHtml(String(row.yorum ?? '-'))}</td>
                </tr>
            `;
        }).join('');

        previewWrap.style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function () {
		        initCariAutocomplete({
            inputId: 'veriAktarimCariInput',
            hiddenId: 'veriAktarimCariId',
            resultsId: 'veriAktarimCariResults',
            items: window.veriAktarimCariler || [],
            emptyValue: ''
        });
        if (window.wolvoxPreviewData && window.appModal) {
    cariWolvoxRenderPreview(window.wolvoxPreviewData);
    window.appModal.open('cariWolvoxImportModal');
}
    });
</script>
<?php require BASE_PATH . '/app/Views/partials/scripts/cari-autocomplete.php'; ?>
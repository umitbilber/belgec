<script>
    function stokFiltrele() {
        var input = document.getElementById('stokAramaInput');
        var filter = input.value.toUpperCase();
        var table = document.getElementById('stokTablosuGosterimi');
        var tr = table.getElementsByTagName('tr');

        for (var i = 0; i < tr.length; i++) {
            var td1 = tr[i].getElementsByTagName('td')[0];
            var td2 = tr[i].getElementsByTagName('td')[1];

            if (td1 || td2) {
                var txtValue1 = td1 ? (td1.textContent || td1.innerText) : '';
                var txtValue2 = td2 ? (td2.textContent || td2.innerText) : '';

                if (txtValue1.toUpperCase().indexOf(filter) > -1 || txtValue2.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }
    }

    function stokDuzenleAc(button) {
        var rawData = button.getAttribute('data-stok');
        if (!rawData) return;

        var stok = null;

        try {
            stok = JSON.parse(rawData);
        } catch (error) {
            console.error('Stok verisi çözümlenemedi:', error);
            return;
        }

        var form = document.getElementById('editStokForm');
        if (!form) return;

        form.querySelector('[name="stok_id"]').value = stok.id ?? '';
        form.querySelector('[name="stok_kodu"]').value = stok.stok_kodu ?? '';
        form.querySelector('[name="urun_adi"]').value = stok.urun_adi ?? '';
        form.querySelector('[name="stok_miktari"]').value = stok.stok_miktari ?? '';
        form.querySelector('[name="birim"]').value = stok.birim ?? 'Adet';

        if (window.appModal) {
            window.appModal.open('editStokModal');
        }
    }

    function stokHareketleriAc(button) {
        var rawData = button.getAttribute('data-stok');
        if (!rawData) return;

        var stok = null;

        try {
            stok = JSON.parse(rawData);
        } catch (error) {
            console.error('Stok hareket verisi çözümlenemedi:', error);
            return;
        }

        var kodEl = document.querySelector('[data-stok-hareket-kodu]');
        var urunEl = document.querySelector('[data-stok-hareket-urun]');
        var miktarEl = document.querySelector('[data-stok-hareket-miktar]');
        var tbody = document.getElementById('stokHareketlerBody');

        if (kodEl) kodEl.textContent = stok.stok_kodu ?? '-';
        if (urunEl) urunEl.textContent = stok.urun_adi ?? '-';
        if (miktarEl) {
            var miktar = stok.stok_miktari ?? 0;
            var birim = stok.birim ?? 'Adet';
            miktarEl.textContent = Number(miktar).toLocaleString('tr-TR', {
                minimumFractionDigits: 4,
                maximumFractionDigits: 4
            }) + ' ' + birim;
        }

                if (tbody) {
            tbody.innerHTML = '';

            var hareketler = Array.isArray(stok.hareketler) ? stok.hareketler : [];

            if (!hareketler.length) {
                tbody.innerHTML = '<tr><td colspan="6">Bu stok kartına ait hareket bulunmuyor.</td></tr>';
            } else {
                hareketler.forEach(function (hareket) {
                    var tr = document.createElement('tr');

                    var tarih = hareket.hareket_tarihi ?? hareket.tarih ?? '';
                    var islemTipi = hareket.islem_tipi ?? '';
                    var cariAdi = hareket.cari_adi ?? '-';
                    var faturaNo = hareket.fatura_no ?? '-';
                    var faturaTipi = hareket.fatura_tipi ?? '';
                    var faturaId = hareket.fatura_id ?? '';
                    var miktar = Number(hareket.miktar ?? 0).toLocaleString('tr-TR', {
                        minimumFractionDigits: 4,
                        maximumFractionDigits: 4
                    });
                    var aciklama = hareket.aciklama ?? '';

                    var faturaLinkHtml = '-';

                    if (faturaId && faturaTipi) {
    var hedefUrl = '';

    if (String(faturaTipi) === 'alis') {
        hedefUrl = '<?= e(url('alis-faturalari')) ?>?duzenle=' + encodeURIComponent(String(faturaId));
    } else if (String(faturaTipi) === 'satis') {
        hedefUrl = '<?= e(url('satis-faturalari')) ?>?duzenle=' + encodeURIComponent(String(faturaId));
    }

    if (hedefUrl) {
        faturaLinkHtml = '<a href="' + stokEscapeHtml(hedefUrl) + '">' + stokEscapeHtml(String(faturaNo)) + '</a>';
    } else {
        faturaLinkHtml = stokEscapeHtml(String(faturaNo));
    }
}

                    tr.innerHTML = `
                        <td>${stokEscapeHtml(String(tarih))}</td>
                        <td>${stokEscapeHtml(stokUcFirst(String(islemTipi)))}</td>
                        <td>${stokEscapeHtml(String(cariAdi))}</td>
                        <td>${faturaLinkHtml}</td>
                        <td>${stokEscapeHtml(String(miktar))}</td>
                        <td>${stokEscapeHtml(String(aciklama))}</td>
                    `;

                    tbody.appendChild(tr);
                });
            }
        }

        if (window.appModal) {
            window.appModal.open('stokMovementsModal');
        }
    }

    function stokUcFirst(text) {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    function stokEscapeHtml(text) {
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
</script>
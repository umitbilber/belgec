<script>
    function cariFiltrele() {
        var input = document.getElementById('cariAramaInput');
        var filter = input.value.toUpperCase();
        var table = document.getElementById('cariTablosuGosterimi');
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

    function cariUcFirst(text) {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    function cariDuzenleAc(button) {
        var rawData = button.getAttribute('data-cari');
        if (!rawData) return;

        var cari = null;

        try {
            cari = JSON.parse(rawData);
        } catch (error) {
            console.error('Cari verisi çözümlenemedi:', error);
            return;
        }

        var form = document.getElementById('editCariForm');
        if (!form) return;

        form.querySelector('[name="cari_id"]').value = cari.id ?? '';
        form.querySelector('[name="ad_soyad"]').value = cari.ad_soyad ?? '';
        form.querySelector('[name="telefon"]').value = cari.telefon ?? '';
        form.querySelector('[name="eposta"]').value = cari.eposta ?? '';
        form.querySelector('[name="adres"]').value = cari.adres ?? '';
        form.querySelector('[name="vergi_no"]').value = cari.vergi_no ?? '';
        form.querySelector('[name="varsayilan_vade_gun"]').value = cari.varsayilan_vade_gun ?? 0;
        form.querySelector('[name="duzeltilmis_bakiye"]').value = cari.bakiye ?? 0;

        if (window.appModal) {
            window.appModal.open('editCariModal');
        }
    }

    function cariHareketAc(button, hareketTipi) {
        var rawData = button.getAttribute('data-cari');
        if (!rawData) return;

        var cari = null;

        try {
            cari = JSON.parse(rawData);
        } catch (error) {
            console.error('Cari hareket verisi çözümlenemedi:', error);
            return;
        }

        var form = document.getElementById('cariMovementForm');
        if (!form) return;

        form.querySelector('[name="cari_id"]').value = cari.id ?? '';
        form.querySelector('[name="islem_tipi"]').value = hareketTipi;
        form.querySelector('[name="tarih"]').value = new Date().toISOString().split('T')[0];
        form.querySelector('[name="tutar"]').value = '';
        form.querySelector('[name="aciklama"]').value = '';

        var unvanEl = document.querySelector('[data-cari-hareket-unvan]');
        if (unvanEl) {
            unvanEl.textContent = cari.ad_soyad ?? '-';
        }

        var modalTitle = document.getElementById('cariMovementModalTitle');
        if (modalTitle) {
            modalTitle.textContent = hareketTipi === 'tahsilat' ? 'Tahsilat Al' : 'Tediye Yap';
        }

        var submitButton = document.getElementById('cariMovementSubmitButton');
        if (submitButton) {
            submitButton.className = hareketTipi === 'tahsilat' ? 'btn btn-yesil' : 'btn btn-turuncu';
            submitButton.textContent = 'Kaydet';
        }

        if (window.appModal) {
            window.appModal.open('cariMovementModal');
        }
    }

    function cariEkstreAc(button) {
        var rawData = button.getAttribute('data-cari');
        if (!rawData) return;

        var cari = null;

        try {
            cari = JSON.parse(rawData);
        } catch (error) {
            console.error('Cari ekstre verisi çözümlenemedi:', error);
            return;
        }

        var unvanEl = document.querySelector('[data-cari-ekstre-unvan]');
        var bakiyeEl = document.querySelector('[data-cari-ekstre-bakiye]');
        var tbody = document.getElementById('cariEkstreBody');
        var yazdirLink = document.getElementById('cariEkstreYazdirLink');

        if (unvanEl) {
            unvanEl.textContent = cari.ad_soyad ?? '-';
        }

        if (bakiyeEl) {
            var bakiye = Number(cari.bakiye ?? 0);
            var etiket = '';
            var renk = '#444';

            if (bakiye > 0) {
                etiket = ' (B)';
                renk = '#16a34a';
            } else if (bakiye < 0) {
                etiket = ' (A)';
                renk = '#d61f1f';
            }

            bakiyeEl.textContent = Math.abs(bakiye).toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' TL' + etiket;
            bakiyeEl.style.color = renk;
        }

        if (yazdirLink) {
            yazdirLink.href = '<?= e(url('cariler/yazdir?id=')) ?>' + encodeURIComponent(String(cari.id ?? ''));
        }

                if (tbody) {
            tbody.innerHTML = '';

            var hareketler = Array.isArray(cari.hareketler) ? cari.hareketler : [];

            if (!hareketler.length) {
                tbody.innerHTML = '<tr><td colspan="5">Bu cariye ait hareket bulunmuyor.</td></tr>';
            } else {
                hareketler.forEach(function (hareket) {
                    var tr = document.createElement('tr');

                    var tarih = hareket.hareket_tarihi ?? hareket.tarih ?? '';
                    var islemTipi = hareket.islem_tipi ?? '';
                    var aciklama = hareket.aciklama ?? '';
                    var tutar = Number(hareket.tutar ?? 0).toLocaleString('tr-TR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' TL';

                    var faturaNo = hareket.fatura_no ?? '';
                    var faturaTipi = hareket.fatura_tipi ?? '';
                    var faturaId = hareket.fatura_id ?? '';
                    var faturaLinkHtml = '-';

                    if (faturaId && faturaTipi && faturaNo) {
    var hedefUrl = '';

    if (String(faturaTipi) === 'alis') {
        hedefUrl = '<?= e(url('alis-faturalari')) ?>?duzenle=' + encodeURIComponent(String(faturaId));
    } else if (String(faturaTipi) === 'satis') {
        hedefUrl = '<?= e(url('satis-faturalari')) ?>?duzenle=' + encodeURIComponent(String(faturaId));
    }

    if (hedefUrl) {
        faturaLinkHtml = '<a href="' + cariEscapeHtml(hedefUrl) + '">' + cariEscapeHtml(String(faturaNo)) + '</a>';
    } else {
        faturaLinkHtml = cariEscapeHtml(String(faturaNo));
    }
}

                    tr.innerHTML = `
                        <td>${cariEscapeHtml(String(tarih))}</td>
                        <td>${cariEscapeHtml(cariUcFirst(String(islemTipi)))}</td>
                        <td>${faturaLinkHtml}</td>
                        <td>${cariEscapeHtml(String(aciklama))}</td>
                        <td>${cariEscapeHtml(String(tutar))}</td>
                    `;

                    tbody.appendChild(tr);
                });
            }
        }

        if (window.appModal) {
            window.appModal.open('cariStatementModal');
        }
    }

    function cariMutabakatAc(button) {
        var rawData = button.getAttribute('data-cari');
        if (!rawData) return;

        var cari = null;

        try {
            cari = JSON.parse(rawData);
        } catch (error) {
            console.error('Cari mutabakat verisi çözümlenemedi:', error);
            return;
        }

        var form = document.getElementById('cariMutabakatForm');
        if (form) {
            form.querySelector('[name="cari_id"]').value = cari.id ?? '';
        }

        var unvanEl = document.querySelector('[data-cari-mutabakat-unvan]');
        var epostaEl = document.querySelector('[data-cari-mutabakat-eposta]');
        var bakiyeEl = document.querySelector('[data-cari-mutabakat-bakiye]');

        if (unvanEl) {
            unvanEl.textContent = cari.ad_soyad ?? '-';
        }

        if (epostaEl) {
            epostaEl.textContent = cari.eposta ?? '-';
        }

        if (bakiyeEl) {
            var bakiye = Number(cari.bakiye ?? 0);
            var etiket = '';
            var renk = '#444';

            if (bakiye > 0) {
                etiket = '(B)';
                renk = '#16a34a';
            } else if (bakiye < 0) {
                etiket = '(A)';
                renk = '#d61f1f';
            }

            bakiyeEl.textContent = Math.abs(bakiye).toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' TL ' + etiket;
            bakiyeEl.style.color = renk;
        }
        
        // Varsayılan metni textarea'ya yükle
var textarea = document.getElementById('mutabakatMetniAlani');
if (textarea) {
    var varsayilan = (window.mutabakatVarsayilanMetin || '');
    textarea.value = varsayilan;
    textarea._varsayilan = varsayilan;
}

        if (window.appModal) {
            window.appModal.open('cariMutabakatModal');
        }
    }
    function mutabakatVarsayilanaRestore() {
    var textarea = document.getElementById('mutabakatMetniAlani');
    if (textarea && textarea._varsayilan !== undefined) {
        textarea.value = textarea._varsayilan;
    }
}
</script>
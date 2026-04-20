<script>
    function initCariAutocomplete(config) {
        var input = document.getElementById(config.inputId);
        var hidden = document.getElementById(config.hiddenId);
        var results = document.getElementById(config.resultsId);
        var items = Array.isArray(config.items) ? config.items : [];
        var emptyValue = config.emptyValue ?? '0';

        if (!input || !hidden || !results) {
            return;
        }

        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return String(text ?? '').replace(/[&<>"']/g, function (char) {
                return map[char];
            });
        }

        function clearResults() {
            results.innerHTML = '';
            results.style.display = 'none';
        }

        function setSelection(item) {
            input.value = item ? (item.ad_soyad ?? '') : '';
            hidden.value = item ? String(item.id ?? '') : String(emptyValue);
            clearResults();
        }

        function renderResults(keyword) {
            var q = String(keyword ?? '').trim().toLocaleLowerCase('tr-TR');

            if (!q) {
                hidden.value = String(emptyValue);
                clearResults();
                return;
            }

            var filtered = items.filter(function (item) {
                var name = String(item.ad_soyad ?? '').toLocaleLowerCase('tr-TR');
                var phone = String(item.telefon ?? '').toLocaleLowerCase('tr-TR');
                var mail = String(item.eposta ?? '').toLocaleLowerCase('tr-TR');
                return name.indexOf(q) !== -1 || phone.indexOf(q) !== -1 || mail.indexOf(q) !== -1;
            }).slice(0, 8);

            if (!filtered.length) {
                results.innerHTML = '<button type="button" class="cari-autocomplete-item disabled" tabindex="-1">Eşleşen cari bulunamadı.</button>';
                results.style.display = 'block';
                hidden.value = String(emptyValue);
                return;
            }

            results.innerHTML = filtered.map(function (item) {
                return ''
                    + '<button type="button" class="cari-autocomplete-item" data-id="' + escapeHtml(item.id) + '">'
                    + '  <span class="cari-autocomplete-name">' + escapeHtml(item.ad_soyad ?? '-') + '</span>'
                    + '  <span class="cari-autocomplete-meta">' + escapeHtml(item.telefon ?? item.eposta ?? '') + '</span>'
                    + '</button>';
            }).join('');

            results.style.display = 'block';

            Array.prototype.forEach.call(results.querySelectorAll('.cari-autocomplete-item:not(.disabled)'), function (button) {
                button.addEventListener('click', function () {
                    var id = Number(button.getAttribute('data-id') || 0);
                    var selected = items.find(function (item) {
                        return Number(item.id) === id;
                    }) || null;
                    setSelection(selected);
                });
            });
        }

        input.addEventListener('input', function () {
            hidden.value = String(emptyValue);
            renderResults(input.value);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim()) {
                renderResults(input.value);
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                clearResults();
            }
        });

        document.addEventListener('click', function (event) {
            if (!results.contains(event.target) && event.target !== input) {
                clearResults();
            }
        });
    }
</script>
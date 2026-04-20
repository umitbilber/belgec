<style>
    .edm-zil-wrap {
        position: relative;
    }

    .edm-zil-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 44px;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.08);
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        position: relative;
    }

    .edm-zil-btn:hover {
        transform: translateY(-1px);
        background: rgba(255,255,255,.14);
        border-color: rgba(255,255,255,.28);
    }

    .edm-zil-btn-ikon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
    }

    .edm-zil-btn-ikon svg {
        width: 18px;
        height: 18px;
        display: block;
    }

    #edmZilBadge {
        display: none;
        position: absolute;
        top: 6px;
        right: 6px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        min-width: 16px;
        height: 16px;
        border-radius: 20px;
        padding: 0 4px;
        line-height: 16px;
        text-align: center;
        border: 2px solid rgba(255,255,255,.2);
    }

    .edm-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 280px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(15,23,42,.18), 0 2px 8px rgba(15,23,42,.08);
        z-index: 9999;
        overflow: hidden;
        animation: edmDropAc .15s ease;
    }

    .edm-dropdown.acik {
        display: block;
    }

    @keyframes edmDropAc {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .edm-drop-baslik {
        padding: 12px 16px 10px;
        font-size: 11px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid #f1f5f9;
    }

    .edm-drop-icerik {
        padding: 12px 16px;
    }

    .edm-drop-bos {
        color: #94a3b8;
        font-size: 13px;
        text-align: center;
        padding: 8px 0;
    }

    .edm-drop-yeni-satir {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .edm-drop-yeni-satir:last-child {
        border-bottom: 0;
    }

    .edm-drop-nokta {
        width: 8px;
        height: 8px;
        background: #2563eb;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .edm-drop-yeni-metin {
        flex: 1;
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    .edm-drop-yeni-sayi {
        font-size: 12px;
        font-weight: 800;
        color: #2563eb;
        background: #eff6ff;
        border-radius: 20px;
        padding: 2px 8px;
    }

    .edm-drop-git-btn {
        display: block;
        margin-top: 10px;
        text-align: center;
        background: #2563eb;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 8px;
        border-radius: 8px;
        text-decoration: none;
        transition: background .15s;
    }

    .edm-drop-git-btn:hover {
        background: #1d4ed8;
    }
    .ust-bar-kur {
    font-size: 11px;
    font-weight: 700;
    color: rgba(255,255,255,.75);
    letter-spacing: .02em;
    margin-top: 2px;
    min-height: 14px;
}
.ust-bar-kur-deger {
    color: rgba(255,255,255,.95);
}
</style>

<div class="ust-bar" id="ustBar">
    <div class="ust-bar-sol">
        <div class="ust-bar-baslik">
            <?= e($pageTitle ?? 'Belgeç') ?>
        </div>
        <div class="ust-bar-alt">
    <?= e($ayarlar['sirket_adi'] ?? 'Belgeç') ?>
    <?php if (!empty($_SESSION['kullanici_ad'])): ?>
        &nbsp;·&nbsp; <?= e($_SESSION['kullanici_ad']) ?>
    <?php endif; ?>
</div>
        
    </div>

    <button
        type="button"
        class="ust-bar-menu-toggle"
        id="ustBarMenuToggle"
        aria-label="Menüyü aç"
        aria-expanded="false"
        aria-controls="ustBarMenu"
    >
        <span class="ust-bar-menu-toggle-ikon" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>

    <div class="ust-bar-sag" id="ustBarMenu">

        <a href="<?= e(url('anasayfa')) ?>" class="ust-bar-btn" title="Ana Sayfa">
            <span class="ust-bar-btn-ikon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5L12 3L21 10.5V20A1 1 0 0 1 20 21H15V14H9V21H4A1 1 0 0 1 3 20V10.5Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span>Ana Sayfa</span>
        </a>

        <a href="#" class="ust-bar-btn" title="Yenile" onclick="window.location.reload(); return false;">
            <span class="ust-bar-btn-ikon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M20 11A8 8 0 1 0 18.3 16" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 5V11H14" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span>Yenile</span>
        </a>

        <?php if (!empty($ayarlar['edm_aktif'])): ?>
        <div class="edm-zil-wrap">
            <button type="button" class="edm-zil-btn" id="edmZilBtn" onclick="edmDropdownToggle(event)" title="EDM Faturalar">
                <span class="edm-zil-btn-ikon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Bildirimler</span>
                <span id="edmZilBadge"></span>
            </button>

            <div class="edm-dropdown" id="edmDropdown">
                <div class="edm-drop-baslik">Bildirimler</div>
                <div class="edm-drop-icerik" id="edmDropIcerik">
                    <div class="edm-drop-bos">Yükleniyor…</div>
                </div>
            </div>
        </div>
        <div class="edm-zil-wrap" id="yedekBildirimWrap" style="display:none;">
    <button type="button" class="edm-zil-btn" id="yedekBildirimBtn" onclick="yedekBildirimKapat()" title="Otomatik Yedek">
        <span class="edm-zil-btn-ikon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span id="yedekBildirimMetin">Yedek Alındı</span>
    </button>
</div>
        <?php endif; ?>

        <a href="<?= e(url('ayarlar')) ?>" class="ust-bar-btn" title="Ayarlar">
            <span class="ust-bar-btn-ikon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z" stroke="currentColor" stroke-width="1.9"/>
                    <path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.79 16.88A2 2 0 1 1 16.96 19.71L16.9 19.65A1.65 1.65 0 0 0 15.08 19.32A1.65 1.65 0 0 0 14 20.85V21A2 2 0 1 1 10 21V20.91A1.65 1.65 0 0 0 8.92 19.39A1.65 1.65 0 0 0 7.1 19.72L7.04 19.78A2 2 0 1 1 4.21 16.95L4.27 16.89A1.65 1.65 0 0 0 4.6 15.07A1.65 1.65 0 0 0 3.07 14H3A2 2 0 1 1 3 10H3.09A1.65 1.65 0 0 0 4.61 8.92A1.65 1.65 0 0 0 4.28 7.1L4.22 7.04A2 2 0 1 1 7.05 4.21L7.11 4.27A1.65 1.65 0 0 0 8.93 4.6H9A1.65 1.65 0 0 0 10 3.07V3A2 2 0 1 1 14 3V3.09A1.65 1.65 0 0 0 15.08 4.61A1.65 1.65 0 0 0 16.9 4.28L16.96 4.22A2 2 0 1 1 19.79 7.05L19.73 7.11A1.65 1.65 0 0 0 19.4 8.93V9A1.65 1.65 0 0 0 20.93 10H21A2 2 0 1 1 21 14H20.91A1.65 1.65 0 0 0 19.39 15.08Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span>Ayarlar</span>
        </a>

        <a href="<?= e(url('logout')) ?>" class="ust-bar-btn ust-bar-btn-cikis" title="Çıkış">
            <span class="ust-bar-btn-ikon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 12H9" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span>Çıkış</span>
        </a>
        <div id="ustBarKur" style="width:100%;text-align:right;font-size:11px;font-weight:700;color:rgba(255,255,255,.75);padding:2px 2px 0;letter-spacing:.02em;"></div>
    </div>
</div>
<script>
(function () {
    var CACHE_KEY = 'belgec_kur';
    var CACHE_TTL = 30 * 60 * 1000;

    function kurGoster(usd, eur) {
    var el = document.getElementById('ustBarKur');
    if (!el) return;

    function saatGuncelle() {
        var now = new Date();
        var gun = String(now.getDate()).padStart(2, '0');
        var ay  = String(now.getMonth() + 1).padStart(2, '0');
        var yil = now.getFullYear();
        var ss  = String(now.getHours()).padStart(2, '0');
        var dk  = String(now.getMinutes()).padStart(2, '0');
        var sn  = String(now.getSeconds()).padStart(2, '0');
        var tarihSaat = gun + '.' + ay + '.' + yil + ' ' + ss + ':' + dk + ':' + sn;

        var parca = ['<span class="ust-bar-kur-deger">' + tarihSaat + '</span>'];
        if (usd) parca.push('$ <span class="ust-bar-kur-deger">' + usd + '</span>');
        if (eur) parca.push('€ <span class="ust-bar-kur-deger">' + eur + '</span>');
        el.innerHTML = parca.join(' &nbsp;·&nbsp; ');
    }

    saatGuncelle();
    setInterval(saatGuncelle, 1000);
}

    function kurYukle() {
        var el = document.getElementById('ustBarKur');
        if (!el) return;

        try {
            var cached = JSON.parse(sessionStorage.getItem(CACHE_KEY) || 'null');
            if (cached && (Date.now() - cached.ts) < CACHE_TTL) {
                kurGoster(cached.usd, cached.eur, cached.tarih);
                return;
            }
        } catch (e) {}

        fetch('<?= e(url('ayarlar/kur-bilgisi')) ?>')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) return;
                try {
                    sessionStorage.setItem(CACHE_KEY, JSON.stringify({
                        usd: data.usd, eur: data.eur, tarih: data.tarih, ts: Date.now()
                    }));
                } catch (e) {}
                kurGoster(data.usd, data.eur, data.tarih);
            })
            .catch(function () {});
    }
    setInterval(function () {
    try { sessionStorage.removeItem(CACHE_KEY); } catch (e) {}
    kurYukle();
}, 30 * 60 * 1000);

    document.addEventListener('DOMContentLoaded', kurYukle);
})();
function yedekBildirimKontrol() {
    fetch('<?= e(url('ayarlar/yedek-bildirim')) ?>')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok || !data.var) return;
            var wrap = document.getElementById('yedekBildirimWrap');
            var metin = document.getElementById('yedekBildirimMetin');
            if (wrap) wrap.style.display = 'block';
            if (metin) metin.textContent = 'Yedek Alındı — ' + data.zaman;
        })
        .catch(function() {});
}

function yedekBildirimKapat() {
    fetch('<?= e(url('ayarlar/yedek-bildirim-oku')) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf_token=' + encodeURIComponent(document.querySelector('[name="_csrf_token"]') ? document.querySelector('[name="_csrf_token"]').value : '')
    }).catch(function() {});

    var wrap = document.getElementById('yedekBildirimWrap');
    if (wrap) wrap.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', yedekBildirimKontrol);
</script>
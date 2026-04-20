<?php require BASE_PATH . '/app/Views/partials/styles/dashboard-page.php'; ?>

<div class="kutu dashboard-page-wrap">
            <div class="dashboard-header-left">
            <h2 class="dashboard-page-title">Genel Durum</h2>
            <p class="dashboard-page-desc">
                Bugün gerçekleşen finansal hareketlerin özeti aşağıdadır.
            </p>
        </div>

    </div>

    <div class="dashboard-stats-grid">
                <div class="dashboard-stat-card tahsilat">
            <div class="dashboard-stat-label tahsilat">Günlük Tahsilat</div>
            <div class="dashboard-stat-value tahsilat">
                <?= number_format((float) $ozet['gunluk_tahsilat'], 2, ',', '.') ?> ₺
            </div>
        </div>

                       <div class="dashboard-stat-card tediye">
            <div class="dashboard-stat-label tediye">Günlük Tediye</div>
            <div class="dashboard-stat-value tediye">
                <?= number_format((float) $ozet['gunluk_tediye'], 2, ',', '.') ?> ₺
            </div>
        </div>

                <div class="dashboard-stat-card alis">
            <div class="dashboard-stat-label alis">Günlük Alış</div>
            <div class="dashboard-stat-value alis">
                <?= number_format((float) $ozet['gunluk_alis'], 2, ',', '.') ?> ₺
            </div>
        </div>

                <div class="dashboard-stat-card alis">
            <div class="dashboard-stat-label alis">Günlük Satış</div>
            <div class="dashboard-stat-value alis">
                <?= number_format((float) $ozet['gunluk_satis'], 2, ',', '.') ?> ₺
            </div>
        </div>
    </div>
</div>
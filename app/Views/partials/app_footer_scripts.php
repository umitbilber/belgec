<?php if (!empty($include_modal_js)): ?>
    <script src="<?= e(url('assets/js/modal.js')) ?>"></script>
<?php endif; ?>

<?php if (!empty($include_drag_sort_js)): ?>
    <script src="<?= e(url('assets/js/drag-sort.js')) ?>"></script>
<?php endif; ?>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?= e(url('pwa/sw.js')) ?>')
                .catch(function (error) {
                    console.log('Service Worker kayıt hatası:', error);
                });
        });
    }
</script>
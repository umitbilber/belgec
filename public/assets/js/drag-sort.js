(function () {
    function initDragSort(container) {
        if (!container) return;

        var draggedItem = null;

        container.addEventListener('dragstart', function (event) {
            var item = event.target.closest('[data-sort-item]');
            if (!item) return;

            draggedItem = item;
            item.classList.add('is-dragging');

            try {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', item.dataset.sortItem || '');
            } catch (e) {}
        });

        container.addEventListener('dragend', function (event) {
            var item = event.target.closest('[data-sort-item]');
            if (item) {
                item.classList.remove('is-dragging');
            }

            container.querySelectorAll('[data-sort-item]').forEach(function (el) {
                el.classList.remove('drag-over-top', 'drag-over-bottom');
            });

            updateSortIndexes(container);
            draggedItem = null;
        });

        container.addEventListener('dragover', function (event) {
            event.preventDefault();

            var target = event.target.closest('[data-sort-item]');
            if (!target || !draggedItem || target === draggedItem) return;

            container.querySelectorAll('[data-sort-item]').forEach(function (el) {
                el.classList.remove('drag-over-top', 'drag-over-bottom');
            });

            var rect = target.getBoundingClientRect();
            var offset = event.clientY - rect.top;
            var halfway = rect.height / 2;

            if (offset < halfway) {
                target.classList.add('drag-over-top');
                container.insertBefore(draggedItem, target);
            } else {
                target.classList.add('drag-over-bottom');
                container.insertBefore(draggedItem, target.nextSibling);
            }
        });

        container.addEventListener('drop', function () {
            updateSortIndexes(container);
        });

        updateSortIndexes(container);
    }

    function updateSortIndexes(container) {
        var items = container.querySelectorAll('[data-sort-item]');
        items.forEach(function (item, index) {
            item.setAttribute('data-sort-index', String(index));

            var orderInput = item.querySelector('[data-sort-order-input]');
            if (orderInput) {
                orderInput.value = index + 1;
            }
        });
    }

    function initAllDragSorts() {
        document.querySelectorAll('[data-sort-container]').forEach(function (container) {
            if (container.dataset.dragSortReady === 'true') return;
            container.dataset.dragSortReady = 'true';
            initDragSort(container);
        });
    }

    document.addEventListener('DOMContentLoaded', initAllDragSorts);

    window.appDragSort = {
        init: initDragSort,
        initAll: initAllDragSorts,
        update: updateSortIndexes
    };
})();
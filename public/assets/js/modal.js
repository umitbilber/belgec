(function () {
    function lockBodyScroll() {
        document.body.classList.add('modal-open');
    }

    function unlockBodyScrollIfNeeded() {
        var openModal = document.querySelector('.app-modal.is-open');
        if (!openModal) {
            document.body.classList.remove('modal-open');
        }
    }

    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        lockBodyScroll();

        var autofocusTarget = modal.querySelector('[data-modal-autofocus], input, textarea, select, button');
        if (autofocusTarget) {
            setTimeout(function () {
                autofocusTarget.focus();
            }, 30);
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBodyScrollIfNeeded();
    }

    function closeModalElement(modal) {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBodyScrollIfNeeded();
    }

    document.addEventListener('click', function (event) {
        var openTrigger = event.target.closest('[data-modal-open]');
        if (openTrigger) {
            var modalId = openTrigger.getAttribute('data-modal-open');
            openModal(modalId);
            return;
        }

        var closeTrigger = event.target.closest('[data-modal-close]');
        if (closeTrigger) {
            var modal = closeTrigger.closest('.app-modal');
            if (modal) {
                closeModalElement(modal);
            }
            return;
        }

        var overlay = event.target.closest('.app-modal-backdrop');
        if (overlay) {
            var modalFromOverlay = overlay.closest('.app-modal');
            if (modalFromOverlay) {
                closeModalElement(modalFromOverlay);
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        var openModals = document.querySelectorAll('.app-modal.is-open');
        if (!openModals.length) return;

        var lastOpenModal = openModals[openModals.length - 1];
        closeModalElement(lastOpenModal);
    });

    window.appModal = {
        open: openModal,
        close: closeModal
    };
})();
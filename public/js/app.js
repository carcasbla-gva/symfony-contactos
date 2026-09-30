/**
 * App Interactivity: Toasts, Modals, and Client-Side Search
 */
document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initConfirmModal();
    initLiveSearch();
});

// Notificaciones
function initToasts() {
    const toasts = document.querySelectorAll('.toast');

    toasts.forEach((toast) => {
        const duration = 4500; // 4.5 seconds

        const dismiss = () => {
            if (toast.classList.contains('toast-hiding')) return;
            toast.classList.add('toast-hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        };

        // Close button click
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', dismiss);
        }

        // Auto dismiss after timer
        const timer = setTimeout(dismiss, duration);

        // Pause on mouse hover
        toast.addEventListener('mouseenter', () => clearTimeout(timer));
    });
}

// Ventana Modal
let activeDeleteForm = null;

function initConfirmModal() {
    const modalBackdrop = document.getElementById('confirm-modal');
    if (!modalBackdrop) return;

    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const confirmBtn = document.getElementById('modal-btn-confirm');
    const cancelBtns = modalBackdrop.querySelectorAll('[data-dismiss="modal"]');

    const openModal = (title, message, onConfirmCallback) => {
        if (modalTitle && title) modalTitle.textContent = title;
        if (modalMessage && message) modalMessage.textContent = message;
        
        modalBackdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        // Set one-time confirm handler
        const handleConfirm = () => {
            closeModal();
            if (typeof onConfirmCallback === 'function') {
                onConfirmCallback();
            }
        };

        confirmBtn.onclick = handleConfirm;
    };

    const closeModal = () => {
        modalBackdrop.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    // Close on cancel buttons or backdrop click
    cancelBtns.forEach(btn => btn.addEventListener('click', closeModal));
    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) {
            closeModal();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.classList.contains('is-open')) {
            closeModal();
        }
    });

    // Listen for elements with [data-confirm-delete]
    document.querySelectorAll('[data-confirm-delete]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const contactName = btn.getAttribute('data-contact-name') || 'este contacto';
            const form = btn.closest('form');
            const actionName = btn.getAttribute('name');
            const actionValue = btn.getAttribute('value');

            openModal(
                '¿Eliminar contacto?',
                `¿Estás seguro de que deseas eliminar a "${contactName}"? Esta acción no se puede deshacer.`,
                () => {
                    if (form) {
                        // Append hidden input if button was carrying action name/value
                        if (actionName && actionValue) {
                            let input = form.querySelector(`input[name="${actionName}"]`);
                            if (!input) {
                                input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = actionName;
                                form.appendChild(input);
                            }
                            input.value = actionValue;
                        }
                        form.submit();
                    }
                }
            );
        });
    });
}

//Buscador en tiempo real
function initLiveSearch() {
    const searchInput = document.getElementById('contact-search');
    const contactCards = document.querySelectorAll('.contact-card');
    const emptyNotice = document.getElementById('no-search-results');

    if (!searchInput || contactCards.length === 0) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        let matchCount = 0;

        contactCards.forEach((card) => {
            const name = card.getAttribute('data-name')?.toLowerCase() || '';
            const phone = card.getAttribute('data-phone')?.toLowerCase() || '';
            const email = card.getAttribute('data-email')?.toLowerCase() || '';

            if (name.includes(query) || phone.includes(query) || email.includes(query)) {
                card.style.display = '';
                matchCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (emptyNotice) {
            emptyNotice.style.display = matchCount === 0 ? 'block' : 'none';
        }
    });
}

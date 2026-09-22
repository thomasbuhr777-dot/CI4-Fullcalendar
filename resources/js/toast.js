import { Toast } from 'bootstrap';

let container = null;

const CONFIG = {
    success: {
        icon: 'bi-check-circle-fill',
        title: 'Erfolg',
        class: 'success'
    },
    danger: {
        icon: 'bi-x-circle-fill',
        title: 'Fehler',
        class: 'danger'
    },
    warning: {
        icon: 'bi-exclamation-triangle-fill',
        title: 'Hinweis',
        class: 'warning'
    },
    info: {
        icon: 'bi-info-circle-fill',
        title: 'Information',
        class: 'info'
    }
};

function getContainer() {

    if (container) return container;

    container = document.getElementById('toastContainer');

    if (!container) {

        container = document.createElement('div');
        container.id = 'toastContainer';

        container.className =
            'toast-container position-fixed bottom-0 end-0 p-3';

        container.style.zIndex = 2000;

        document.body.appendChild(container);

    }

    return container;

}

export function showToast(message, type = 'success', title = null) {

    const cfg = CONFIG[type] || CONFIG.info;

    const wrapper = document.createElement('div');

    wrapper.innerHTML = `
        <div class="toast border-0 shadow-lg rounded-4 mb-2" role="alert">

            <div class="toast-header bg-${cfg.class} text-white rounded-top-4">

                <i class="bi ${cfg.icon} me-2"></i>

                <strong class="me-auto">
                    ${title || cfg.title}
                </strong>

                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="toast">
                </button>

            </div>

            <div class="toast-body py-3">

                ${message}

            </div>

        </div>
    `;

    const toastElement = wrapper.firstElementChild;

    getContainer().appendChild(toastElement);

    const toast = new Toast(toastElement, {
        delay: 3000,
        autohide: true
    });

    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });

}
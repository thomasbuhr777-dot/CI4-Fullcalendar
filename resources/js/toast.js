import { Toast } from 'bootstrap';

let container = null;

function getContainer() {
    if (container) return container;

    container = document.getElementById('toastContainer');

    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = 1080;

        document.body.appendChild(container);
    }

    return container;
}

export function showToast(message, type = 'success') {

    const wrapper = document.createElement('div');

    wrapper.innerHTML = `
        <div class="toast align-items-center text-bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast">
                </button>
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
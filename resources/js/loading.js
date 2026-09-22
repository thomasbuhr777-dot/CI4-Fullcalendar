let overlay = null;

export function showLoading(text = 'Speichere...') {

    if (!overlay) {

        overlay = document.createElement('div');

        overlay.id = 'loadingOverlay';

        overlay.innerHTML = `
            DIL1
        `;

        document.body.appendChild(overlay);

    }

    overlay.style.display = 'flex';

}

export function hideLoading() {

    if (overlay) {
        overlay.style.display = 'none';
    }

}
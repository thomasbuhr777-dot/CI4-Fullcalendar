// Bootstrap CSS
import 'bootstrap/dist/css/bootstrap.min.css';

// Bootstrap JavaScript (inkl. Popper)
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Eigene globale Styles (wenn vorhanden)
import '../css/app.css';

const offlineBanner = document.getElementById('offlineBanner');

function updateConnectionState() {
    const isOffline = !navigator.onLine;
    offlineBanner?.classList.toggle('d-none', !isOffline);
    document.body.classList.toggle('is-offline', isOffline);
}

window.addEventListener('online', updateConnectionState);
window.addEventListener('offline', updateConnectionState);
updateConnectionState();

if ('serviceWorker' in navigator && (window.isSecureContext || location.hostname === 'localhost')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.error('Service Worker konnte nicht registriert werden.', error);
        });
    });
}

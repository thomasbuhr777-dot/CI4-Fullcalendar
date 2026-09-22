// =============================================================
// Tommy Edition v0.8
// FullCalendar + Bootstrap + CodeIgniter 4
// Referenzdatei
// =============================================================

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';
import { Modal } from 'bootstrap';
import { showToast } from './toast';

// -------------------------------------------------------------
// Kalender vorhanden?
// -------------------------------------------------------------

const calendarEl = document.getElementById('calendar');

if (!calendarEl) {
    throw new Error('Kalender-Element #calendar wurde nicht gefunden.');
}

// -------------------------------------------------------------
// Bootstrap Modal
// -------------------------------------------------------------

const modal = new Modal(document.getElementById('eventModal'));

const ui = {
    form: document.getElementById('eventForm'),
    id: document.getElementById('eventId'),
    title: document.getElementById('eventTitle'),
    description: document.getElementById('eventDescription'),
    start: document.getElementById('eventStart'),
    end: document.getElementById('eventEnd'),
    allDay: document.getElementById('eventAllDay'),
    deleteButton: document.getElementById('deleteEvent'),
    modalTitle: document.querySelector('#eventModal .modal-title')
};

// -------------------------------------------------------------
// Konstanten
// -------------------------------------------------------------

const DEFAULT_START_HOUR = 9;
const DEFAULT_DURATION_MINUTES = 60;

// -------------------------------------------------------------
// Datums-Helfer
// -------------------------------------------------------------

const pad = (n) => String(n).padStart(2, '0');

function toInput(date) {
    return (
        `${date.getFullYear()}-` +
        `${pad(date.getMonth() + 1)}-` +
        `${pad(date.getDate())}T` +
        `${pad(date.getHours())}:` +
        `${pad(date.getMinutes())}`
    );
}

function fromApi(value) {
    return value ? value.substring(0, 16) : '';
}

function formatTime(date) {
    return date.toLocaleTimeString('de-DE', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// -------------------------------------------------------------
// Ganztägig umschalten
// -------------------------------------------------------------

let rememberedStartTime = '09:00';
let rememberedEndTime = '10:00';

function toggleAllDayMode() {

    const isAllDay = ui.allDay.checked;

    if (isAllDay) {

        rememberedStartTime = ui.start.value.substring(11, 16) || '09:00';
        rememberedEndTime = ui.end.value.substring(11, 16) || '10:00';

        ui.start.disabled = true;
        ui.end.disabled = true;

    } else {

        ui.start.disabled = false;
        ui.end.disabled = false;

    }

}

ui.allDay.addEventListener('change', toggleAllDayMode);

// -------------------------------------------------------------
// API-Helfer
// -------------------------------------------------------------

const api = {

    async get(id) {
        return fetch(`/api/events/${id}`).then(r => r.json());
    },

    async save(id, data) {

        const url = id
            ? `/api/events/${id}`
            : '/api/events';

        return fetch(url, {
            method: 'POST',
            body: data
        }).then(r => r.json());

    },

    async remove(id) {

        return fetch(`/api/events/${id}`, {
            method: 'DELETE'
        }).then(r => r.json());

    },

    async move(id, start, end) {

        const data = new FormData();

        data.append('start', start);

        if (end) {
            data.append('end', end);
        }

        return fetch(`/api/events/${id}/move`, {
            method: 'POST',
            body: data
        }).then(r => r.json());

    }

};

// -------------------------------------------------------------
// FullCalendar
// -------------------------------------------------------------

const calendar = new Calendar(calendarEl, {

    plugins: [
        dayGridPlugin,
        timeGridPlugin,
        interactionPlugin
    ],

    locale: deLocale,

    initialView: 'dayGridMonth',

    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },

    buttonText: {
        today: 'Heute',
        month: 'Monat',
        week: 'Woche',
        day: 'Tag'
    },

    displayEventTime: true,

    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    },

    // Monatsansicht: 09:00–10:00 Titel
    eventContent(arg) {

        if (arg.view.type === 'dayGridMonth' && !arg.event.allDay) {

            const start = formatTime(arg.event.start);
            const end = arg.event.end
                ? formatTime(arg.event.end)
                : '';

            return {
                html: `
                    <div class="fc-event-main-frame">
                        <span class="fc-time-range">
                            ${start}${end ? `–${end}` : ''}
                        </span>
                        <span class="fc-title">
                            ${arg.event.title}
                        </span>
                    </div>
                `
            };

        }

        return true;

    },

    selectable: true,
    editable: true,

    // ---------------------------------------------------------
    // Termine laden
    // ---------------------------------------------------------

    events: {
        url: '/api/events',
        method: 'GET',

        failure(error) {
            console.error(error);
            showToast('Termine konnten nicht geladen werden.', 'danger');
        }
    },

    // ---------------------------------------------------------
    // Neuer Termin
    // ---------------------------------------------------------

    dateClick(info) {

        ui.form.reset();

        ui.id.value = '';

        ui.modalTitle.textContent = 'Neuer Termin';

        ui.deleteButton.style.display = 'none';

        ui.start.disabled = false;
        ui.end.disabled = false;

        const start = new Date(info.date);
        const end = new Date(info.date);

        if (info.allDay) {

            start.setHours(DEFAULT_START_HOUR, 0, 0, 0);

            end.setHours(DEFAULT_START_HOUR + 1, 0, 0, 0);

            ui.allDay.checked = true;

        } else {

            end.setMinutes(end.getMinutes() + DEFAULT_DURATION_MINUTES);

            ui.allDay.checked = false;

        }

        ui.start.value = toInput(start);
        ui.end.value = toInput(end);

        toggleAllDayMode();

        modal.show();

    },

    // ---------------------------------------------------------
    // Termin bearbeiten
    // ---------------------------------------------------------

    eventClick(info) {

        api.get(info.event.id)
            .then(event => {

                ui.modalTitle.textContent = 'Termin bearbeiten';

                ui.id.value = event.id;
                ui.title.value = event.title;
                ui.description.value = event.description ?? '';

                ui.start.disabled = false;
                ui.end.disabled = false;

                ui.start.value = fromApi(event.start);
                ui.end.value = fromApi(event.end);

                ui.allDay.checked = Boolean(Number(event.all_day));

                toggleAllDayMode();

                ui.deleteButton.style.display = '';

                modal.show();

            })
            .catch(error => {

                console.error(error);

                showToast('Termin konnte nicht geladen werden.', 'danger');

            });

    },

    // ---------------------------------------------------------
    // Drag & Drop
    // ---------------------------------------------------------

    eventDrop(info) {
        saveMove(info);
    },

    // ---------------------------------------------------------
    // Resize
    // ---------------------------------------------------------

    eventResize(info) {
        saveMove(info);
    }

});

// Kalender anzeigen
calendar.render();

// -------------------------------------------------------------
// Drag & Drop speichern
// -------------------------------------------------------------

async function saveMove(info) {

    try {

        const json = await api.move(
            info.event.id,
            toInput(info.event.start),
            info.event.end ? toInput(info.event.end) : ''
        );

        if (!json.success) {

            info.revert();

            showToast('Termin konnte nicht gespeichert werden.', 'danger');

        }

    } catch (error) {

        console.error(error);

        info.revert();

        showToast('Serverfehler beim Speichern.', 'danger');

    }

}

// -------------------------------------------------------------
// Formular speichern
// -------------------------------------------------------------

ui.form.addEventListener('submit', async (e) => {

    e.preventDefault();

    const data = new FormData(ui.form);

    if (ui.start.disabled) {
        data.set('start', ui.start.value);
    }

    if (ui.end.disabled) {
        data.set('end', ui.end.value);
    }

    try {

        const json = await api.save(ui.id.value, data);

        if (json.success) {
            modal.hide();
            calendar.refetchEvents();
            showToast('Termin gespeichert.');

        } else {

            showToast('Termin konnte nicht gespeichert werden.', 'danger');

        }

    } catch (error) {

        console.error(error);

        showToast('Serverfehler beim Speichern.', 'danger');

    }

});

// -------------------------------------------------------------
// Termin löschen
// -------------------------------------------------------------

ui.deleteButton.addEventListener('click', async () => {

    if (!ui.id.value) return;

    if (!confirm('Termin wirklich löschen?')) return;

    try {

        const json = await api.remove(ui.id.value);

        if (json.success) {

            modal.hide();

            calendar.refetchEvents();
            showToast('Termin gelöscht.');

        } else {

            showToast('Termin konnte nicht gelöscht werden.', 'danger');

        }

    } catch (error) {

        console.error(error);

        showToast('Serverfehler beim Löschen.', 'danger');

    }

});
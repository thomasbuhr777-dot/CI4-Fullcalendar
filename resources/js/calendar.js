// =============================================================
// Tommy Edition v0.9 Foundation
// FullCalendar + Bootstrap + CodeIgniter 4
//
// Referenzdatei für die Tommy Edition.
//
// Sections
// 01 Imports
// 02 UI & Modal
// 03 Utilities
// 04 API Service
// 05 FullCalendar
// 06 CRUD
// 07 Drag & Drop & Resize
// 08 Initialisierung
// =============================================================

// -------------------------------------------------------------
// 01 Imports
// -------------------------------------------------------------

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';

import { Modal } from 'bootstrap';
import { showToast } from './toast';

// -------------------------------------------------------------
// 02 UI & Modal
// -------------------------------------------------------------

const calendarEl = document.getElementById('calendar');

if (!calendarEl) {
    throw new Error('Kalender-Element #calendar wurde nicht gefunden.');
}

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
// Konfiguration
// -------------------------------------------------------------

const CONFIG = {
    DEFAULT_START_HOUR: 9,
    DEFAULT_DURATION_MINUTES: 60
};

// -------------------------------------------------------------
// 03 Utilities
// -------------------------------------------------------------

const pad = (value) => String(value).padStart(2, '0');

export function toInput(date) {
    return (
        `${date.getFullYear()}-` +
        `${pad(date.getMonth() + 1)}-` +
        `${pad(date.getDate())}T` +
        `${pad(date.getHours())}:` +
        `${pad(date.getMinutes())}`
    );
}

export function fromApi(value) {
    return value ? value.substring(0, 16) : '';
}

export function formatTime(date) {
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

        return;
    }

    ui.start.disabled = false;
    ui.end.disabled = false;

    if (ui.start.value.length === 16) {
        rememberedStartTime = ui.start.value.substring(11, 16);
    }

    if (ui.end.value.length === 16) {
        rememberedEndTime = ui.end.value.substring(11, 16);
    }
}

ui.allDay.addEventListener('change', toggleAllDayMode);

// -------------------------------------------------------------
// Floating Action Button
// -------------------------------------------------------------

const fab = document.getElementById('newEventFab');

if (fab) {

    fab.addEventListener('click', () => {

        ui.form.reset();

        ui.id.value = '';
        ui.modalTitle.textContent = 'Neuer Termin';
        ui.deleteButton.style.display = 'none';

        const start = new Date();
        start.setHours(CONFIG.DEFAULT_START_HOUR, 0, 0, 0);

        const end = new Date(start);
        end.setMinutes(
            end.getMinutes() + CONFIG.DEFAULT_DURATION_MINUTES
        );

        ui.start.disabled = false;
        ui.end.disabled = false;

        ui.start.value = toInput(start);
        ui.end.value = toInput(end);

        ui.allDay.checked = true;
        toggleAllDayMode();

        modal.show();
    });
}

// -------------------------------------------------------------
// 04 API Service
// (Teil B beginnt genau hier)
// -------------------------------------------------------------

// -------------------------------------------------------------
// 04 API Service
// -------------------------------------------------------------

const api = {

    async get(id) {
        const response = await fetch(`/api/events/${id}`);
        return response.json();
    },

    async save(id, data) {

        const url = id
            ? `/api/events/${id}`
            : '/api/events';

        const response = await fetch(url, {
            method: 'POST',
            body: data
        });

        const text = await response.text();

        if (!response.ok) {
            throw new Error(text);
        }

        return JSON.parse(text);
    },

    async remove(id) {

        const response = await fetch(`/api/events/${id}`, {
            method: 'DELETE'
        });

        return response.json();
    },

    async move(id, start, end) {

        const data = new FormData();

        data.append('start', start);

        if (end) {
            data.append('end', end);
        }

        const response = await fetch(`/api/events/${id}/move`, {
            method: 'POST',
            body: data
        });

        return response.json();
    }

};

// -------------------------------------------------------------
// 05 FullCalendar
// -------------------------------------------------------------

const calendar = new Calendar(calendarEl, {

    plugins: [
        dayGridPlugin,
        timeGridPlugin,
        interactionPlugin
    ],

    locale: deLocale,

    initialView: 'dayGridMonth',

    selectable: true,
    editable: true,

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

    // ---------------------------------------------------------
    // Monatsansicht: Uhrzeit + Titel
    // ---------------------------------------------------------

    eventContent(info) {

        const wrapper = document.createElement('div');
        wrapper.className = 'fc-tommy-event';

        const time = document.createElement('div');
        time.className = 'fc-tommy-time';

        if (!info.event.allDay) {

            const start = formatTime(info.event.start);

            const end = info.event.end
                ? formatTime(info.event.end)
                : '';

            time.textContent = end
                ? `${start}–${end}`
                : start;

        } else {

            time.textContent = 'Ganztägig';

        }

        const title = document.createElement('div');
        title.className = 'fc-tommy-title';
        title.textContent = info.event.title;

        wrapper.append(time, title);

        return {
            domNodes: [wrapper]
        };

    },

    // ---------------------------------------------------------
    // Termine laden
    // ---------------------------------------------------------

    events: {
        url: '/api/events',
        method: 'GET',

        failure(error) {
            console.error(error);
            showToast(
                'Termine konnten nicht geladen werden.',
                'danger'
            );
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

            start.setHours(
                CONFIG.DEFAULT_START_HOUR,
                0,
                0,
                0
            );

            end.setHours(
                CONFIG.DEFAULT_START_HOUR + 1,
                0,
                0,
                0
            );

            ui.allDay.checked = true;

        } else {

            end.setMinutes(
                end.getMinutes() +
                CONFIG.DEFAULT_DURATION_MINUTES
            );

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
            .then((event) => {

                ui.modalTitle.textContent =
                    'Termin bearbeiten';

                ui.id.value = event.id;
                ui.title.value = event.title;
                ui.description.value =
                    event.description ?? '';

                ui.start.disabled = false;
                ui.end.disabled = false;

                ui.start.value = fromApi(event.start);
                ui.end.value = fromApi(event.end);

                ui.allDay.checked =
                    Boolean(Number(event.all_day));

                toggleAllDayMode();

                ui.deleteButton.style.display = '';

                modal.show();
            })
            .catch((error) => {

                console.error(error);

                showToast(
                    'Termin konnte nicht geladen werden.',
                    'danger'
                );

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

// -------------------------------------------------------------
// Kalender rendern
// -------------------------------------------------------------

calendar.render();

// -------------------------------------------------------------
// 06 CRUD
// (Teil C beginnt hier.)
// -------------------------------------------------------------

// -------------------------------------------------------------
// 06 CRUD
// -------------------------------------------------------------

async function saveMove(info) {

    try {

        const json = await api.move(
            info.event.id,
            toInput(info.event.start),
            info.event.end
                ? toInput(info.event.end)
                : ''
        );

        if (!json.success) {

            info.revert();

            showToast(
                'Termin konnte nicht gespeichert werden.',
                'danger'
            );
        }

    } catch (error) {

        console.error(error);

        info.revert();

        showToast(
            'Serverfehler beim Speichern.',
            'danger'
        );
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

        if (!json.success) {

            showToast(
                'Termin konnte nicht gespeichert werden.',
                'danger'
            );

            return;
        }

        modal.hide();

        calendar.refetchEvents();

        showToast('Termin gespeichert.');

    } catch (error) {

        console.error(error);

        showToast(
            'Serverfehler beim Speichern.',
            'danger'
        );

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

        if (!json.success) {

            showToast(
                'Termin konnte nicht gelöscht werden.',
                'danger'
            );

            return;
        }

        modal.hide();

        calendar.refetchEvents();

        showToast('Termin gelöscht.');

    } catch (error) {

        console.error(error);

        showToast(
            'Serverfehler beim Löschen.',
            'danger'
        );

    }

});

// -------------------------------------------------------------
// 07 Drag & Drop & Resize
// -------------------------------------------------------------

// Wird von eventDrop() und eventResize() verwendet.
// Logik zentral an einer Stelle.
// -------------------------------------------------------------
// 08 Initialisierung
// Tommy Edition Foundation
// -------------------------------------------------------------

// Kalender beim ersten Laden anzeigen.
calendar.render();

// Modal beim Schließen zurücksetzen.
const modalElement = document.getElementById('eventModal');

modalElement.addEventListener('hidden.bs.modal', () => {

    ui.form.reset();

    ui.id.value = '';

    ui.deleteButton.style.display = 'none';

    ui.start.disabled = false;
    ui.end.disabled = false;

    ui.allDay.checked = true;

    toggleAllDayMode();

});

// Tommy Edition Ready
console.info('🚀 Tommy Edition Calendar v0.9 Foundation loaded.');
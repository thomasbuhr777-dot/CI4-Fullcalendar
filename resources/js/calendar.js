import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';
import { Modal } from 'bootstrap';
import { showToast } from './toast';

const calendarEl = document.getElementById('calendar');
const workspace = document.getElementById('calendarWorkspace');

if (!calendarEl || !workspace) {
    throw new Error('Die Kalenderoberfläche wurde nicht vollständig geladen.');
}

const eventModal = new Modal(document.getElementById('eventModal'));
const calendarModal = new Modal(document.getElementById('calendarModal'));
const filterStorageKey = workspace.dataset.filterKey;

const ui = {
    form: document.getElementById('eventForm'),
    id: document.getElementById('eventId'),
    title: document.getElementById('eventTitle'),
    description: document.getElementById('eventDescription'),
    calendar: document.getElementById('eventCalendar'),
    start: document.getElementById('eventStart'),
    end: document.getElementById('eventEnd'),
    allDay: document.getElementById('eventAllDay'),
    deleteButton: document.getElementById('deleteEvent'),
    modalTitle: document.querySelector('#eventModal .modal-title'),
    filters: document.getElementById('calendarFilters'),
    empty: document.getElementById('calendarEmpty'),
    newCalendar: document.getElementById('newCalendarButton'),
    saveCalendar: document.getElementById('saveCalendarButton'),
    calendarName: document.getElementById('calendarName'),
    calendarColor: document.getElementById('calendarColor'),
};

const pad = (value) => String(value).padStart(2, '0');

function toInput(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function fromApi(value) {
    return value ? value.substring(0, 16) : '';
}

function formatTime(date) {
    return date.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
}

function messageFrom(error, fallback) {
    return error instanceof Error && error.message ? error.message : fallback;
}

async function request(url, options = {}, write = false) {
    if (write && !navigator.onLine) {
        throw new Error('Ohne Internetverbindung können Änderungen nicht gespeichert werden.');
    }

    const response = await fetch(url, options);
    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(body.message || 'Die Anfrage ist fehlgeschlagen.');
    }

    return body;
}

const api = {
    get: (id) => request(`/api/events/${id}`),
    save: (id, data) => request(id ? `/api/events/${id}` : '/api/events', { method: 'POST', body: data }, true),
    remove: (id) => request(`/api/events/${id}`, { method: 'DELETE' }, true),
    move(id, start, end) {
        const data = new FormData();
        data.append('start', start);
        if (end) data.append('end', end);
        return request(`/api/events/${id}/move`, { method: 'POST', body: data }, true);
    },
    createCalendar(data) {
        return request('/api/calendars', { method: 'POST', body: data }, true);
    },
};

function allCalendarIds() {
    return [...ui.filters.querySelectorAll('.calendar-filter')].map((checkbox) => String(checkbox.value));
}

function storedSelection() {
    const all = allCalendarIds();
    let stored = null;

    try {
        stored = localStorage.getItem(filterStorageKey);
    } catch {
        return all;
    }

    if (stored === null) return all;

    try {
        const parsed = JSON.parse(stored);
        return Array.isArray(parsed) ? parsed.map(String).filter((id) => all.includes(id)) : all;
    } catch {
        return all;
    }
}

let selectedCalendarIds = storedSelection();

function persistSelection() {
    try {
        localStorage.setItem(filterStorageKey, JSON.stringify(selectedCalendarIds));
    } catch {
        // Die Filter funktionieren weiterhin für die aktuelle Sitzung.
    }
}

function syncFilterControls() {
    ui.filters.querySelectorAll('.calendar-filter').forEach((checkbox) => {
        checkbox.checked = selectedCalendarIds.includes(String(checkbox.value));
    });
}

function firstVisibleCalendarId() {
    return selectedCalendarIds.find((id) => allCalendarIds().includes(id)) || allCalendarIds()[0] || '';
}

function prepareNewEvent(start = new Date(), allDay = true) {
    if (allCalendarIds().length === 0) {
        showToast('Lege zuerst einen Kalender an.', 'info');
        return;
    }

    ui.form.reset();
    ui.id.value = '';
    ui.modalTitle.textContent = 'Neuer Termin';
    ui.deleteButton.classList.add('d-none');
    ui.calendar.value = firstVisibleCalendarId();

    const eventStart = new Date(start);
    if (allDay) eventStart.setHours(9, 0, 0, 0);
    const eventEnd = new Date(eventStart);
    eventEnd.setHours(eventEnd.getHours() + 1);

    ui.start.value = toInput(eventStart);
    ui.end.value = toInput(eventEnd);
    ui.allDay.checked = allDay;
    eventModal.show();
}

async function saveMove(info) {
    try {
        const result = await api.move(
            info.event.id,
            toInput(info.event.start),
            info.event.end ? toInput(info.event.end) : ''
        );
        if (!result.success) throw new Error('Termin konnte nicht gespeichert werden.');
    } catch (error) {
        info.revert();
        showToast(messageFrom(error, 'Termin konnte nicht gespeichert werden.'), 'danger');
    }
}

const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locale: deLocale,
    initialView: 'dayGridMonth',
    selectable: true,
    editable: true,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    buttonText: { today: 'Heute', month: 'Monat', week: 'Woche', day: 'Tag' },
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    eventContent(info) {
        const wrapper = document.createElement('div');
        wrapper.className = 'fc-tommy-event';
        const time = document.createElement('div');
        time.className = 'fc-tommy-time';
        time.textContent = info.event.allDay
            ? 'Ganztägig'
            : `${formatTime(info.event.start)}${info.event.end ? `–${formatTime(info.event.end)}` : ''}`;
        const title = document.createElement('div');
        title.className = 'fc-tommy-title';
        title.textContent = info.event.title;
        wrapper.append(time, title);
        return { domNodes: [wrapper] };
    },
    events(info, success, failure) {
        const params = new URLSearchParams({
            start: info.startStr,
            end: info.endStr,
            calendar_ids: selectedCalendarIds.join(','),
        });
        request(`/api/events?${params}`)
            .then(success)
            .catch((error) => {
                failure(error);
                showToast(messageFrom(error, 'Termine konnten nicht geladen werden.'), 'danger');
            });
    },
    dateClick(info) {
        prepareNewEvent(info.date, info.allDay);
    },
    async eventClick(info) {
        try {
            const event = await api.get(info.event.id);
            ui.form.reset();
            ui.modalTitle.textContent = 'Termin bearbeiten';
            ui.id.value = event.id;
            ui.title.value = event.title;
            ui.description.value = event.description ?? '';
            ui.calendar.value = String(event.calendar_id);
            ui.start.value = fromApi(event.start);
            ui.end.value = fromApi(event.end);
            ui.allDay.checked = Boolean(Number(event.all_day));
            ui.deleteButton.classList.remove('d-none');
            eventModal.show();
        } catch (error) {
            showToast(messageFrom(error, 'Termin konnte nicht geladen werden.'), 'danger');
        }
    },
    eventDrop: saveMove,
    eventResize: saveMove,
});

ui.form.addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        const result = await api.save(ui.id.value, new FormData(ui.form));
        if (!result.success) throw new Error('Termin konnte nicht gespeichert werden.');
        eventModal.hide();
        calendar.refetchEvents();
        showToast('Termin gespeichert.');
    } catch (error) {
        showToast(messageFrom(error, 'Termin konnte nicht gespeichert werden.'), 'danger');
    }
});

ui.deleteButton.addEventListener('click', async () => {
    if (!ui.id.value || !confirm('Termin wirklich löschen?')) return;
    try {
        const result = await api.remove(ui.id.value);
        if (!result.success) throw new Error('Termin konnte nicht gelöscht werden.');
        eventModal.hide();
        calendar.refetchEvents();
        showToast('Termin gelöscht.');
    } catch (error) {
        showToast(messageFrom(error, 'Termin konnte nicht gelöscht werden.'), 'danger');
    }
});

ui.filters.addEventListener('change', (event) => {
    if (!event.target.matches('.calendar-filter')) return;
    selectedCalendarIds = [...ui.filters.querySelectorAll('.calendar-filter:checked')]
        .map((checkbox) => String(checkbox.value));
    persistSelection();
    calendar.refetchEvents();
});

function appendCalendar(item) {
    const wrapper = document.createElement('div');
    wrapper.className = 'calendar-item';
    wrapper.dataset.calendarId = item.id;

    const label = document.createElement('label');
    label.className = 'calendar-switch flex-grow-1';
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'calendar-filter form-check-input';
    checkbox.value = item.id;
    checkbox.checked = true;
    const color = document.createElement('span');
    color.className = 'calendar-color';
    color.style.background = item.color;
    const name = document.createElement('span');
    name.className = 'calendar-name';
    name.textContent = item.name;
    label.append(checkbox, color, name);
    wrapper.append(label);
    ui.filters.append(wrapper);

    const option = document.createElement('option');
    option.value = item.id;
    option.dataset.color = item.color;
    option.textContent = `${item.name} (${item.color})`;
    ui.calendar.append(option);
    ui.empty.classList.add('d-none');
}

ui.newCalendar.addEventListener('click', () => {
    ui.calendarName.value = '';
    ui.calendarColor.value = '#16A34A';
    calendarModal.show();
});

ui.saveCalendar.addEventListener('click', async () => {
    const data = new FormData();
    data.append('name', ui.calendarName.value);
    data.append('color', ui.calendarColor.value);

    try {
        const result = await api.createCalendar(data);
        appendCalendar(result.calendar);
        selectedCalendarIds.push(String(result.calendar.id));
        selectedCalendarIds = [...new Set(selectedCalendarIds)];
        persistSelection();
        calendarModal.hide();
        calendar.refetchEvents();
        showToast('Kalender angelegt.');
    } catch (error) {
        showToast(messageFrom(error, 'Kalender konnte nicht angelegt werden.'), 'danger');
    }
});

document.getElementById('newEventFab')?.addEventListener('click', () => prepareNewEvent());
syncFilterControls();
calendar.render();

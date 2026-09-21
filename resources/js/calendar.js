import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';

import { Modal } from 'bootstrap';

const calendarEl = document.getElementById('calendar');
const modalElement = document.getElementById('eventModal');
const eventModal = modalElement ? new Modal(modalElement) : null;
const form = document.getElementById('eventForm');

if (calendarEl) {

    const eventModal = new Modal(
        document.getElementById('eventModal')
    );

    const form = document.getElementById('eventForm');

const calendar = new Calendar(calendarEl, {
    plugins: [
        dayGridPlugin,
        timeGridPlugin,
        interactionPlugin,
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

    selectable: true,
    editable: true,

    events: {
        url: '/api/events',
        method: 'GET',

        failure(error) {
            console.error('API-Fehler:', error);
            alert('Termine konnten nicht geladen werden.');
        }
    },

dateClick(info) {
    document.getElementById('eventDate').value = info.dateStr + ' 09:00:00';
    document.getElementById('eventTitle').value = '';
    document.getElementById('eventDescription').value = '';

    eventModal.show();
}
});

calendar.render();

form.addEventListener('submit', async (e) => {

    e.preventDefault();

    const data = new FormData(form);

    const response = await fetch('/api/events', {
        method: 'POST',
        body: data
    });

    const json = await response.json();

    if (json.success) {

        eventModal.hide();

        calendar.refetchEvents();

    } else {

        alert('Fehler beim Speichern.');

    }

});
}
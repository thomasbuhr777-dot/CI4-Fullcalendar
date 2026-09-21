import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';

const calendarEl = document.getElementById('calendar');

if (calendarEl) {
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
        console.log('Datum:', info.dateStr);
    }
});

calendar.render();
}
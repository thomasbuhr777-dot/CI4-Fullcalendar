import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';
import { Modal } from 'bootstrap';

const calendarEl = document.getElementById('calendar');

if (calendarEl) {

    // ------------------------------------------------------
    // Bootstrap Modal
    // ------------------------------------------------------

    const modal = new Modal(document.getElementById('eventModal'));

    const form = document.getElementById('eventForm');

    const eventId = document.getElementById('eventId');
    const eventTitle = document.getElementById('eventTitle');
    const eventDescription = document.getElementById('eventDescription');
    const eventStart = document.getElementById('eventStart');
    const eventEnd = document.getElementById('eventEnd');
    const eventAllDay = document.getElementById('eventAllDay');
    const deleteButton = document.getElementById('deleteEvent');
    const modalTitle = document.querySelector('#eventModal .modal-title');

    // ------------------------------------------------------
    // Hilfsfunktionen
    // ------------------------------------------------------

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
        if (!value) return '';
        return value.substring(0, 16);
    }

    // Uhrzeiten merken, wenn auf Ganztägig gewechselt wird.
    let rememberedStartTime = '09:00';
    let rememberedEndTime = '10:00';

    function toggleAllDayMode() {

        const isAllDay = eventAllDay.checked;

        if (isAllDay) {

            rememberedStartTime = eventStart.value.substring(11, 16) || '09:00';
            rememberedEndTime = eventEnd.value.substring(11, 16) || '10:00';

            eventStart.value = eventStart.value.substring(0, 10) + 'T00:00';
            eventEnd.value = eventEnd.value.substring(0, 10) + 'T23:59';

            eventStart.disabled = true;
            eventEnd.disabled = true;

        } else {

            eventStart.disabled = false;
            eventEnd.disabled = false;

            eventStart.value =
                eventStart.value.substring(0, 10) + 'T' + rememberedStartTime;

            eventEnd.value =
                eventEnd.value.substring(0, 10) + 'T' + rememberedEndTime;
        }
    }

    eventAllDay.addEventListener('change', toggleAllDayMode);

    // ------------------------------------------------------
    // FullCalendar
    // ------------------------------------------------------

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
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },

        buttonText: {
            today: 'Heute',
            month: 'Monat',
            week: 'Woche',
            day: 'Tag',
        },

        selectable: true,
        editable: true,

        events: {
            url: '/api/events',
            method: 'GET',

            failure(error) {
                console.error(error);
                alert('Termine konnten nicht geladen werden.');
            },
        },

        // --------------------------------------------------
        // Neuer Termin
        // --------------------------------------------------

        dateClick(info) {

            form.reset();

            eventId.value = '';

            modalTitle.textContent = 'Neuer Termin';

            deleteButton.style.display = 'none';

            eventStart.disabled = false;
            eventEnd.disabled = false;

            const start = new Date(info.date);
            const end = new Date(info.date);

            if (info.allDay) {
                start.setHours(9, 0, 0, 0);
                end.setHours(10, 0, 0, 0);
            } else {
                end.setHours(end.getHours() + 1);
            }

            eventStart.value = toInput(start);
            eventEnd.value = toInput(end);

            eventAllDay.checked = info.allDay;

            toggleAllDayMode();

            modal.show();
        },

        // --------------------------------------------------
        // Termin bearbeiten
        // --------------------------------------------------

        eventClick(info) {

            fetch(`/api/events/${info.event.id}`)
                .then((response) => response.json())
                .then((event) => {

                    modalTitle.textContent = 'Termin bearbeiten';

                    eventId.value = event.id;
                    eventTitle.value = event.title;
                    eventDescription.value = event.description ?? '';

                    eventStart.disabled = false;
                    eventEnd.disabled = false;

                    eventStart.value = fromApi(event.start);
                    eventEnd.value = fromApi(event.end);

                    eventAllDay.checked = Boolean(Number(event.all_day));

                    toggleAllDayMode();

                    deleteButton.style.display = '';

                    modal.show();
                })
                .catch((error) => {
                    console.error(error);
                    alert('Termin konnte nicht geladen werden.');
                });
        },

    });

    calendar.render();

    // ------------------------------------------------------
    // Speichern
    // ------------------------------------------------------

    form.addEventListener('submit', async (e) => {

        e.preventDefault();

        const url = eventId.value
            ? `/api/events/${eventId.value}`
            : '/api/events';

        const data = new FormData(form);

        // Deaktivierte Felder werden nicht übertragen
        if (eventStart.disabled) data.set('start', eventStart.value);
        if (eventEnd.disabled) data.set('end', eventEnd.value);

        try {

            const response = await fetch(url, {
                method: 'POST',
                body: data,
            });

            const json = await response.json();

            if (json.success) {

                modal.hide();

                calendar.refetchEvents();

            } else {

                alert('Termin konnte nicht gespeichert werden.');

            }

        } catch (error) {

            console.error(error);

            alert('Serverfehler beim Speichern.');

        }

    });

    // ------------------------------------------------------
    // Löschen
    // ------------------------------------------------------

    deleteButton.addEventListener('click', async () => {

        if (!eventId.value) return;

        if (!confirm('Termin wirklich löschen?')) return;

        try {

            const response = await fetch(`/api/events/${eventId.value}`, {
                method: 'DELETE',
            });

            const json = await response.json();

            if (json.success) {

                modal.hide();

                calendar.refetchEvents();

            } else {

                alert('Termin konnte nicht gelöscht werden.');

            }

        } catch (error) {

            console.error(error);

            alert('Serverfehler beim Löschen.');

        }

    });

}
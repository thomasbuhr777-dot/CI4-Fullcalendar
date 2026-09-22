<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm">

    <div class="card-body">

        <h3>Kalender</h3>

        <div id="calendar"></div>



    </div>

</div>
<div class="modal fade" id="eventModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form id="eventForm">

                <div class="modal-header">
                    <h5 class="modal-title">Neuer Termin</h5>

                    <button class="btn-close"
                            data-bs-dismiss="modal"
                            type="button"></button>
                </div>

                <div class="modal-body">

                    <input id="eventDate"
                           name="start"
                           type="hidden">
                        <input id="eventId"
       name="id"
       type="hidden">
                    <div class="mb-3">
                        <label class="form-label">Titel</label>

                        <input
                            class="form-control"
                            id="eventTitle"
                            name="title"
                            required
                            type="text">
                    </div>

                    <div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label">Beginn</label>

        <input
            class="form-control"
            id="eventStart"
            name="start"
            type="datetime-local"
            required>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Ende</label>

        <input
            class="form-control"
            id="eventEnd"
            name="end"
            type="datetime-local">
    </div>

</div>

<div class="form-check mb-3">

    <input
        class="form-check-input"
        id="eventAllDay"
        name="all_day"
        type="checkbox">

    <label class="form-check-label" for="eventAllDay">
        Ganztägiger Termin
    </label>

</div>

                    <div class="mb-3">
                        <label class="form-label">Beschreibung</label>

                        <textarea
                            class="form-control"
                            id="eventDescription"
                            name="description"
                            rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary"
                            data-bs-dismiss="modal"
                            type="button">
                        Abbrechen
                    </button>
                    <button
    class="btn btn-danger me-auto"
    id="deleteEvent"
    style="display:none"
    type="button">

    Löschen

</button>

                    <button class="btn btn-primary"
                            type="submit">
                        Speichern
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<?= vite('calendar') ?>

<?= $this->endSection() ?>
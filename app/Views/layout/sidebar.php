<?php



?>

<aside class="col-md-2 bg-light border-end min-vh-100 py-4 px-3">

    <nav class="nav flex-column mb-4">

        <a class="nav-link" href="<?= site_url('dashboard') ?>">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
        </a>

        <a class="nav-link active" href="<?= site_url('calendar') ?>">
            <i class="bi bi-calendar3 me-2"></i>
            Kalender
        </a>

    </nav>

<?php if (!empty($calendars)): ?>

    <hr>

    <div class="calendar-sidebar">
        <div class="sidebar-title">Meine Kalender</div>

     <?php foreach ($calendars as $calendar): ?>

    <div class="calendar-item">

        <label class="calendar-switch flex-grow-1">

            <input
                type="checkbox"
                class="calendar-filter"
                value="<?= $calendar['id'] ?>"
                checked>

            <span
                class="calendar-color"
                style="background: <?= esc($calendar['color']) ?>">
            </span>

            <span class="calendar-name">
                <?= esc($calendar['name']) ?>
            </span>

        </label>

        <button
            type="button"
            class="calendar-menu-btn"
            data-calendar-id="<?= $calendar['id'] ?>"
            data-calendar-name="<?= esc($calendar['name']) ?>"
            data-calendar-color="<?= esc($calendar['color']) ?>">

            <i class="bi bi-three-dots-vertical"></i>

        </button>

    </div>

<?php endforeach; ?>
        <button
    class="btn btn-link calendar-add p-0 mt-3"
    id="newCalendarButton">

    <i class="bi bi-plus-circle me-2"></i>
    Neuer Kalender

</button>

    </div>

<?php endif; ?>



</aside>
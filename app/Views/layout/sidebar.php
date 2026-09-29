<?php
$hasCalendarContext = isset($calendars);
$calendars = $calendars ?? [];
?>

<aside class="col-md-3 col-xl-2 bg-light border-end app-sidebar collapse d-md-block py-4 px-3" id="appSidebar">
    <nav class="nav flex-column mb-4">
        <a class="nav-link" href="<?= site_url('dashboard') ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a class="nav-link active" href="<?= site_url('calendar') ?>">
            <i class="bi bi-calendar3 me-2"></i>Kalender
        </a>
    </nav>

    <?php if ($hasCalendarContext): ?>
    <div class="calendar-sidebar">
        <div class="sidebar-title">Meine Kalender</div>

        <div id="calendarFilters">
            <?php foreach ($calendars as $calendar): ?>
                <div class="calendar-item" data-calendar-id="<?= (int) $calendar['id'] ?>">
                    <label class="calendar-switch flex-grow-1">
                        <input
                            type="checkbox"
                            class="calendar-filter form-check-input"
                            value="<?= (int) $calendar['id'] ?>">
                        <span class="calendar-color" style="background: <?= esc($calendar['color']) ?>"></span>
                        <span class="calendar-name"><?= esc($calendar['name']) ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="calendar-empty small text-secondary mb-0 <?= $calendars === [] ? '' : 'd-none' ?>" id="calendarEmpty">
            Noch kein Kalender angelegt.
        </p>

        <button class="btn btn-link calendar-add p-0 mt-3" id="newCalendarButton" type="button">
            <i class="bi bi-plus-circle me-2"></i>Neuer Kalender
        </button>
    </div>
    <?php endif; ?>
</aside>

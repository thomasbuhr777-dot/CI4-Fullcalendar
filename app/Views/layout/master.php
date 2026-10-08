<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2563EB">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <title><?= esc($title ?? 'Kalender') ?></title>

    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="icon" href="<?= base_url('icons/calendar.svg') ?>" type="image/svg+xml">

    <?= vite('app') ?>

</head>

<body>

<?= $this->include('layout/navbar') ?>

<div class="offline-banner d-none" id="offlineBanner" role="status">
    <i class="bi bi-wifi-off me-2"></i>
    Offline: Lesen kann eingeschränkt sein, Änderungen sind erst nach der Wiederverbindung möglich.
</div>

<div class="container-fluid">
        <div class="mobile-calendar-actions d-md-none py-2">
            <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#appSidebar" aria-expanded="false" aria-controls="appSidebar">
                <i class="bi bi-list me-2"></i>Navigation und Kalender
            </button>
        </div>
    <div class="row">

       <?= $this->include('layout/sidebar', ['calendars' => $calendars ?? []]) ?>

        <main class="col-md-9 col-xl-10 py-4 px-4 app-main">
            <?= $this->renderSection('content') ?>
        </main>

    </div>
</div>
<!-- Tommy Edition Floating Action Button -->
<?php if (isset($calendars)): ?>
<button id="newEventFab" class="btn btn-primary rounded-circle shadow-lg" type="button" title="Neuer Termin" aria-label="Neuer Termin">

    <i class="bi bi-plus-lg"></i>

</button>
<?php endif; ?>
<?= $this->renderSection('scripts') ?>

</body>
</html>

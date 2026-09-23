<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= esc($title ?? 'Kalender') ?></title>

    <?= vite('app') ?>

</head>

<body>

<?= $this->include('layout/navbar') ?>

<div class="container-fluid">
    <div class="row">

       <?= $this->include('layout/sidebar', ['calendars' => $calendars ?? []]) ?>

        <main class="col-md-10 py-4 px-4">
            <?= $this->renderSection('content') ?>
        </main>

    </div>
</div>
<!-- Tommy Edition Floating Action Button -->
<button
    id="newEventFab"
    class="btn btn-primary rounded-circle shadow-lg"
    type="button"
    title="Neuer Termin">

    <i class="bi bi-plus-lg"></i>

</button>
<?= $this->renderSection('scripts') ?>

</body>
</html>
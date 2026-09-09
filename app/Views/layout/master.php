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

        <?= $this->include('layout/sidebar') ?>

        <main class="col-md-10 py-4 px-4">
            <?= $this->renderSection('content') ?>
        </main>

    </div>
</div>

<?= $this->renderSection('scripts') ?>

</body>
</html>
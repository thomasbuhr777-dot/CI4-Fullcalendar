<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm p-4">

    <h2>Moin Thomas 👋</h2>

    <p>Dashboard läuft.</p>

    <a href="<?= site_url('calendar') ?>"
       class="btn btn-primary">
        Kalender öffnen
    </a>

</div>

<?= $this->endSection() ?>
<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm">

    <div class="card-body">

        <h3>Kalender</h3>

        <div id="calendar"></div>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<?= vite('calendar') ?>

<?= $this->endSection() ?>
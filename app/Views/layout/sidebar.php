<nav class="col-md-2 d-none d-md-block bg-white border-end vh-100 p-3">

    <h6 class="text-uppercase text-muted mb-3">Navigation</h6>

    <ul class="nav nav-pills flex-column gap-2">

        <li class="nav-item">
            <a class="nav-link" href="<?= site_url('dashboard') ?>">
                🏠 Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= site_url('calendar') ?>">
                📅 Kalender
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                🎨 Kategorien
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                👥 Benutzer
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                ⚙️ Einstellungen
            </a>
        </li>

    </ul>

    <hr>

    <small class="text-muted">
        Mandant:<br>
        <strong><?= esc(session('tenant_name') ?? '-') ?></strong>
    </small>

</nav>
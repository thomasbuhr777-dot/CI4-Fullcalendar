<nav class="navbar navbar-dark bg-primary px-3">

    <a class="navbar-brand" href="/dashboard">
        📅 Kalender
    </a>

    <div class="ms-auto text-white">

        <strong><?= esc(session('tenant_name') ?? 'Kein Mandant') ?></strong>

        |

        <?= esc(auth()->user()->username) ?>

        <a href="<?= url_to('logout') ?>" class="btn btn-sm btn-light ms-3">
            Logout
        </a>

    </div>

</nav>
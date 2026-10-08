<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<div class="card shadow-sm mx-auto settings-card">
<div class="card-body">
    <h1 class="h3">Einstellungen</h1>
    <p class="text-secondary">Für dein Benutzerkonto und den aktiven Mandanten.</p>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?></div><?php endif ?>
    <form method="post" action="<?= site_url('settings') ?>">
        <?= csrf_field() ?>
        <label class="form-label" for="defaultView">Standardansicht</label>
        <select class="form-select mb-3" id="defaultView" name="view">
        <?php foreach (\App\Models\CalendarPreferenceModel::VIEWS as $value => $name): ?>
            <option value="<?= esc($value) ?>" <?= $preferences['view'] === $value ? 'selected' : '' ?>><?= esc($name) ?></option>
        <?php endforeach ?>
        </select>
        <label class="form-label" for="holidayState">Bundesland für Feiertage</label>
        <select class="form-select mb-3" id="holidayState" name="state">
        <?php foreach (\App\Models\CalendarPreferenceModel::STATES as $value => $name): ?>
            <option value="<?= esc($value) ?>" <?= $preferences['state'] === $value ? 'selected' : '' ?>><?= esc($name) ?></option>
        <?php endforeach ?>
        </select>
        <fieldset class="mb-3"><legend class="fs-6">Feiertagsjahre</legend>
        <p class="small text-secondary">Ohne Auswahl werden keine Feiertage angezeigt. Künftige Jahre sind beim Anbieter eventuell noch nicht verfügbar.</p>
        <div class="row row-cols-2 row-cols-sm-3 g-2">
        <?php $years = array_unique(array_merge(range((int) date('Y') - 2, (int) date('Y') + 5), $preferences['years'])); sort($years); ?>
        <?php foreach ($years as $year): ?><div class="col"><label class="form-check">
            <input class="form-check-input" type="checkbox" name="years[]" value="<?= $year ?>" <?= in_array($year, $preferences['years'], true) ? 'checked' : '' ?>>
            <span class="form-check-label"><?= $year ?></span>
        </label></div><?php endforeach ?>
        </div></fieldset>
        <button class="btn btn-primary" type="submit">Speichern und zum Kalender</button>
    </form>
</div></div>
<?= $this->endSection() ?>

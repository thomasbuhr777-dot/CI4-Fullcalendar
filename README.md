# Tommy Kalender

Tommy Kalender ist eine eigenständig betreibbare, mandantenfähige Kalender-PWA auf Basis von CodeIgniter 4, Shield, FullCalendar, Bootstrap 5 und Vite. Sie ist für Desktop, Tablet und Handy ausgelegt und enthält keine Abhängigkeiten zu hv3.io.

## Voraussetzungen

- PHP 8.2 oder neuer mit `intl`, `mbstring`, `mysqli` und für Tests `sqlite3`
- Composer 2
- Node.js mit npm
- MySQL oder MariaDB
- Webserver mit Document Root auf `public/`
- HTTPS im Produktivbetrieb (Service Worker und Installation benötigen einen sicheren Kontext)

`localhost` und `127.0.0.1` gelten in aktuellen Browsern als sichere Testkontexte. Ein Aufruf über eine unverschlüsselte LAN-IP genügt dafür nicht.

## Installation

```bash
composer install
npm install
cp env .env
```

Mindestens diese Werte in `.env` setzen:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://kalender.example.de/'
database.default.hostname = localhost
database.default.database = kalender
database.default.username = kalender
database.default.password = 'sicheres-passwort'
database.default.DBDriver = MySQLi
```

Danach Schema und Frontend erzeugen:

```bash
php spark migrate --all
npm run build
```

Für lokale Beispieldaten kann optional `php spark db:seed DemoSeeder` verwendet werden. Der Seeder erwartet bereits mindestens einen Shield-Benutzer. Produktionszugänge niemals über den Demo-Seeder anlegen.

## Lokaler Start

```bash
php spark serve
npm run dev
```

Der Vite-Entwicklungsserver liefert nur Assets; die Anwendung selbst wird über CodeIgniter aufgerufen. Alternativ genügt nach `npm run build` allein der CodeIgniter-Server.

## Datenbank und Migrationen

Neue Installationen erhalten Mandanten, Zuordnungen, Kalender, Kategorien und Termine über die vorhandenen Migrationen. `events.calendar_id` ist bereits Teil der ursprünglichen Event-Migration und deshalb war für diese Version keine nachträgliche Schemaänderung nötig. Bestehende Migrationen wurden nicht verändert.

Vor einem Update:

```bash
php spark migrate:status
php spark migrate --all
```

Bestehende Termine behalten ihre Kalenderzuordnung. Fehlt bei einem älteren API-Client beim Erstellen `calendar_id`, wird aus Kompatibilitätsgründen der erste aktive Kalender des Mandanten verwendet. Beim Bearbeiten ist eine gültige Kalender-ID erforderlich.

## Bedienung

### Einstellungen und deutsche Feiertage (v1.1.0)

Über „Einstellungen“ in der Navigation (auf dem Handy über „Navigation und Kalender“) lassen sich Standardansicht, Bundesland und Feiertagsjahre speichern. Die neue Migration `2026-10-08-120000_CreateCalendarPreferences` muss mit `php spark migrate --all` angewendet werden; danach `npm run build` ausführen. Vorhandene Migrationen bleiben unverändert. Die PHP-Erweiterung `curl` und ausgehendes HTTPS zu `get.api-feiertage.de` werden benötigt.

Die Speicherung erfolgt pro Shield-Benutzer und aktivem Mandanten mit einem eindeutigen Datenbankschlüssel. Neue Benutzer erhalten Monat, Niedersachsen und dynamisch das aktuelle Kalenderjahr plus Folgejahr. Die Jahresauswahl bietet das aktuelle Jahr minus zwei bis plus fünf sowie bereits gespeicherte Jahre. Serverseitig sind höchstens 20 unterschiedliche Jahre zwischen 2000 und 2100 zulässig. Eine leere Auswahl schaltet Feiertage aus. Nach Speichern öffnet der Kalender mit der gewählten Ansicht; manuelle Ansichtswechsel bleiben möglich. Die bestehenden Kalenderfilter in `localStorage` bleiben davon unabhängig.

Die Datenquelle ist [api-feiertage.de](https://www.api-feiertage.de/). Nur der Server ruft über den CodeIgniter-HTTP-Client `https://get.api-feiertage.de/` mit `years` und `states` auf. Der geschützte Endpoint `GET /api/holidays?start=…&end=…` liefert ausschließlich ausgewählte Jahre, die den sichtbaren Zeitraum einschließlich Monats-/Wochenrändern betreffen; `end` ist exklusiv. HTTP-Status, JSON-Struktur, `status=success`, Datumswerte und Bundesland-Markierungen werden geprüft. Bundesweite und für das ausgewählte Land markierte Feiertage erscheinen als gelbe, gestrichelt umrandete Ganztagstermine in allen drei Ansichten. Sie öffnen keinen Editor, sind weder verschiebbar noch veränderbar und bleiben unabhängig von „Meine Kalender“ sichtbar.

Normalisierte Daten werden im gemeinsamen CodeIgniter-Servercache nach Bundesland/Jahr gespeichert: 24 Stunden frisch, bis zu 90 Tage als Rückfall bei Ausfällen. Veraltete Daten erhalten einen Hinweis im Kalender und in der Terminbeschriftung. Fehler und noch nicht verfügbare Jahre lösen eine 15-minütige Wiederholsperre aus; eine erfolgreiche leere Antwort wird **nicht** als vollständiger Feiertagsbestand gespeichert. Ein später veröffentlichter Jahrgang kann nach Ablauf der Sperre erneut geladen werden. Eigene Termine werden unabhängig geladen und bleiben bei Anbieterfehlern bedienbar.

Eine lokale Dateisperre verhindert parallele externe Aufrufe; ein rollierendes Stundenbudget erlaubt höchstens 90 Aufrufe je Installation und hält Abstand zum dokumentierten Anbieterlimit von 100 pro Stunde. `writable/cache` muss beschreibbar sein; der Cache darf nicht auf einen Dummy-Handler umgestellt werden. Mehrere Prozesse derselben Installation teilen Cache und Sperre. Bei mehreren Servern/Installationen hinter derselben öffentlichen IP ist eine gemeinsame Cache-/Sperrstrategie nötig; die lokale Begrenzung koordiniert diese nicht. Das Löschen des Caches löscht auch das Stundenbudget. Der Cache enthält nur öffentliche Feiertagsdaten, keine Benutzereinstellungen.

Der Anbieter garantiert keine unbegrenzt verfügbaren historischen oder künftigen Jahrgänge. Die Auswahl eines Jahres bedeutet nicht, dass bereits Daten vorhanden sind. Ortsabhängige Sonderfälle (etwa Augsburger Friedensfest oder Mariä Himmelfahrt in bestimmten Gemeinden) werden nicht nach Ort konfiguriert; angezeigt werden die vom Anbieter für das Bundesland markierten Daten. Die Angaben des Anbieters ersetzen keine Prüfung örtlicher Feiertagsregeln.

Die PWA bleibt online-first. Der Service Worker speichert ausschließlich statische Assets, keine geschützten HTML-Seiten oder API-/Einstellungsdaten. Kalender, Einstellungen und Feiertagsfeed senden zusätzlich `Cache-Control: private, no-store`.

### Ausgeführte Prüfungen für v1.1.0

- PHP-Suite inklusive gefälschtem Feiertags-HTTP-Client: **22 Tests, 102 Assertions erfolgreich**, `php -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --no-coverage` (SQLite ist lokal vorhanden, aber im Standard-CLI nicht aktiviert). Defaults, Validierung, Shield-Logout/Login, Benutzer-/Mandantentrennung, geschützte Routen, sofort wirksame Ansicht, Feiertagszuordnung, Jahreswechsel, Cache, Fehler, fehlende Jahre und Stundenbudget werden ohne Live-API-Aufrufe getestet.
- Regressionen für eigene Termine: Anlegen, Lesen, Bearbeiten, Verschieben/Verlängern, Filter und Löschen über die geschützten API-Routen; bestehende Isolationstests laufen mit.
- `npm run build` mit npm aus der lokalen Node-Installation erfolgreich. Der im PATH vorangestellte npm-Wrapper ist auf diesem Rechner defekt.
- Visuelle Prüfung in Edge/Chromium mit 390 × 844 und 820 × 1180 Pixeln: gerenderte authentifizierte Testseiten, echte Vite-Assets, gefälschte API-Antworten. Einstellungen sowie Monat/Woche/Tag geprüft, lange Feiertagsnamen umbrochen, kein horizontaler Überlauf und keine JavaScript-Fehler. Feiertagsklick öffnet keinen Editor; Kalendercheckboxen entfernen nur eigene Termine und laden die Feiertagsquelle nicht erneut.
- Eine echte Anbieterantwort wurde einmal separat zur Prüfung der Feldnamen gelesen; sie ist keine Testabhängigkeit. Tests verwenden ausschließlich Fakes. Installation/Touchgesten auf physischen Mobilgeräten und ein produktiver Mehrserverbetrieb wurden nicht geprüft.

- Kalender werden unter „Meine Kalender“ unmittelbar ein- und ausgeblendet.
- Die Auswahl wird mandantenspezifisch in `localStorage` gespeichert und bleibt nach Ansichtswechsel und Reload erhalten.
- Auch eine vollständig leere Auswahl ist zulässig.
- Neue Kalender erscheinen ohne Reload und sind zunächst sichtbar.
- Die Farbe eines Termins stammt immer aus seinem Kalender.
- Drag & Drop und Resize speichern neue Zeiten sofort; bei einem Fehler wird die Änderung zurückgenommen.

Auf kleinen Bildschirmen öffnet die Schaltfläche „Meine Kalender und Filter“ die eingeklappte Seitenleiste. Termin- und Kalendermodale nutzen auf Handys die volle Bildschirmfläche.

## PWA-Installation und Offline-Verhalten

1. Anwendung über HTTPS öffnen und anmelden.
2. In Chrome/Edge „App installieren“ beziehungsweise im Browsermenü „Zum Startbildschirm hinzufügen“ wählen.
3. Nach einem Deployment einmal neu laden, damit der aktualisierte Service Worker aktiv wird.

Die PWA arbeitet online-first:

- Geschützte HTML-Seiten und `/api/*` werden nie im Service Worker gecacht.
- Nur statische Assets wie JavaScript, CSS, Schriften, Manifest und Icon dürfen im Cache liegen.
- Ohne Verbindung erscheint ein Offline-Hinweis.
- Erstellen, Ändern, Verschieben und Löschen werden offline vor dem Request abgewiesen und niemals als erfolgreich dargestellt.
- Eine noch nicht geladene Seite zeigt offline eine neutrale Hinweisseite statt einer zuvor angemeldeten Oberfläche.

Damit bleibt nach einem Logout kein geschützter Seiten- oder Termininhalt aus dem Service-Worker-Cache sichtbar.

## Tests und Build

```bash
composer test -- --no-coverage
npm run build
```

Unter XAMPP muss `extension=sqlite3` in `php.ini` aktiv sein. Für einen einmaligen Lauf ohne Konfigurationsänderung:

```bash
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage
```

Die Datenbanktests prüfen insbesondere:

- Kalenderauflösung nur innerhalb des aktiven Mandanten,
- Entfernung fremder Kalender-IDs aus Filterabfragen,
- gemeinsame Mandanten-/Kalendergrenze im Termin-Feed,
- Kalenderfarbe aus der serverseitigen Zuordnung,
- leeren Feed bei vollständig abgewählten Kalendern.

Manuelle Abnahme vor einem Release:

1. Zwei Kalender mit verschiedenen Farben anlegen und Termine zuordnen.
2. Beide Filter einzeln und gemeinsam aus-/einschalten; Monat, Woche und Tag sowie Reload prüfen.
3. Einen Termin in einen anderen eigenen Kalender verschieben und erneut öffnen.
4. Fremde `calendar_id` bei Erstellung und Änderung per HTTP-Client senden; Status 422 erwarten.
5. Erstellen, Bearbeiten, Löschen, Drag & Drop und Resize prüfen.
6. Browserbreiten um 375 px und 768 px sowie Offline/Online-Wechsel testen.
7. Manifest und Service Worker in den Browser-Entwicklertools auf Installierbarkeit kontrollieren.

## Bekannte Grenzen

- Kalender eines Mandanten sind gemeinsam sichtbar; private Benutzerkalender und feinere Rechte sind nicht Bestandteil dieser Version.
- Offline werden keine Terminlisten bereitgestellt und keine Änderungen in eine Warteschlange gestellt.
- Ein Wechsel zwischen mehreren Mandanten ist noch nicht Teil der Oberfläche; verwendet wird der Shield-Standardmandant des Benutzers.
- Die spätere Integration in hv3.io ist ausdrücklich ein separates Projekt.
